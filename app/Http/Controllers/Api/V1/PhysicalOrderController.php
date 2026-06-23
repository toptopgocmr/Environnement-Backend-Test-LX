<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{
    ChatConversation, ChatMessage, PublicationPlan, AuthorPlan,
    AccountRequest, Book, Order, ShippingAddress, ReadingSession, Citation
    public function tracking(Order $order): JsonResponse
    {
        // Vérifier que la commande appartient à l'utilisateur connecté
        if ($order->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $order->load(['book:id,title,cover_image,cover_url', 'trackingEvents' => fn($q) => $q->orderBy('occurred_at', 'desc')]);

        return response()->json([
            'success' => true,
            'data'    => [
                'reference'               => $order->reference,
                'shipping_status'         => $order->shipping_status,
                'status_label'            => $order->shippingStatusLabel(),
                'status_icon'             => $order->shippingStatusIcon(),
                'tracking_number'         => $order->tracking_number,
                'carrier'                 => $order->carrier,
                'full_name'               => $order->full_name,
                'shipping_address'        => $order->shipping_address,
                'shipping_city'           => $order->shipping_city,
                'shipping_country'        => $order->shipping_country,
                'estimated_delivery_date' => $order->estimated_delivery_date?->format('Y-m-d'),
                'shipped_at'              => $order->shipped_at?->toISOString(),
                'delivered_at'            => $order->delivered_at?->toISOString(),
                'book'                    => $order->book,
                'events'                  => $order->trackingEvents->map(fn($e) => [
                    'id'          => $e->id,
                    'status'      => $e->status,
                    'status_label'=> \App\Models\OrderTrackingEvent::statusLabel($e->status),
                    'status_icon' => \App\Models\OrderTrackingEvent::statusIcon($e->status),
                    'location'    => $e->location,
                    'description' => $e->description,
                    'occurred_at' => $e->occurred_at->toISOString(),
                ]),
            ],
        ]);
    }

};
use App\Services\{ChatService, AiReviewService, PhysicalStockService};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Illuminate\Support\Str;

class PhysicalOrderController extends Controller
{
    public function __construct(private readonly PhysicalStockService $stockService) {}

    public function initiate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id'            => 'required|exists:books,id',
            'payment_method'     => 'required|in:mtn_momo,airtel_money,stripe',
            'phone'              => 'required_if:payment_method,mtn_momo,airtel_money|nullable|string',
            'shipping_address_id'=> 'nullable|exists:shipping_addresses,id',
            // ou nouvelle adresse
            'full_name'         => 'required_without:shipping_address_id|nullable|string',
            'phone_shipping'    => 'required_without:shipping_address_id|nullable|string',
            'address_line1'     => 'required_without:shipping_address_id|nullable|string',
            'city'              => 'required_without:shipping_address_id|nullable|string',
            'country'           => 'nullable|string|size:2',
        ]);

        $book = Book::findOrFail($data['book_id']);
        $user = Auth::user();

        if (!$this->stockService->isAvailable($book->id)) {
            return response()->json(['success' => false, 'message' => 'Stock épuisé pour ce livre.'], 400);
        }

        // Créer ou récupérer l'adresse
        $addressId = $data['shipping_address_id'] ?? null;
        if (!$addressId) {
            $address = ShippingAddress::create([
                'user_id'      => $user->id,
                'full_name'    => $data['full_name'],
                'phone'        => $data['phone_shipping'],
                'address_line1'=> $data['address_line1'],
                'city'         => $data['city'],
                'country'      => $data['country'] ?? 'CG',
            ]);
            $addressId = $address->id;
        }

        // Réserver le stock
        $reserved = $this->stockService->reserveStock($book->id);
        if (!$reserved) {
            return response()->json(['success' => false, 'message' => 'Stock insuffisant.'], 400);
        }

        $price = $book->physical_price ?? $book->print_price ?? $book->price;

        $order = Order::create([
            'reference'           => 'PHY' . strtoupper(Str::random(7)),
            'user_id'             => $user->id,
            'book_id'             => $book->id,
            'amount'              => $price,
            'currency'            => $book->currency,
            'type'                => 'print',
            'payment_method'      => $data['payment_method'],
            'payment_status'      => 'pending',
            'shipping_address_id' => $addressId,
            'shipping_status'     => 'none',
            'max_downloads'       => 0,
        ]);

        return response()->json(['success' => true, 'data' => $order->load('book:id,title,cover_image')], 201);
    }

    public function myPhysicalOrders(): JsonResponse
    {
        $orders = Order::where('user_id', Auth::id())
            ->where('type', 'print')
            ->with(['book:id,title,cover_image', 'shippingAddress'])
            ->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function shippingAddresses(): JsonResponse
    {
        $addresses = ShippingAddress::where('user_id', Auth::id())->get();
        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'    => 'required|string',
            'phone'        => 'required|string',
            'address_line1'=> 'required|string',
            'address_line2'=> 'nullable|string',
            'city'         => 'required|string',
            'state'        => 'nullable|string',
            'postal_code'  => 'nullable|string',
            'country'      => 'nullable|string|size:2',
            'is_default'   => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            ShippingAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address = ShippingAddress::create(array_merge($data, ['user_id' => Auth::id()]));
        return response()->json(['success' => true, 'data' => $address], 201);
    }
}
