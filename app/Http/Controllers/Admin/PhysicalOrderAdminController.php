<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, OrderTrackingEvent};
use App\Services\PhysicalStockService;
use Illuminate\Http\Request;

class PhysicalOrderAdminController extends Controller
{
    public function __construct(private readonly PhysicalStockService $stockService) {}

    public function index(Request $request)
    {
        $orders = Order::where('type', 'print')
            ->with(['user', 'book', 'trackingEvents'])
            ->when($request->status, fn($q) => $q->where('shipping_status', $request->status))
            ->latest()->paginate(20);

        $summary = [
            'pending'          => Order::where('type','print')->where('shipping_status','none')->where('payment_status','paid')->count(),
            'processing'       => Order::where('type','print')->where('shipping_status','processing')->count(),
            'shipped'          => Order::where('type','print')->whereIn('shipping_status',['shipped','out_for_delivery'])->count(),
            'delivered'        => Order::where('type','print')->where('shipping_status','delivered')->count(),
        ];

        return view('admin.physical.orders', compact('orders', 'summary'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'book', 'trackingEvents.creator']);
        return view('admin.physical.order_detail', compact('order'));
    }

    public function updateShipping(Request $request, Order $order)
    {
        $data = $request->validate([
            'shipping_status'         => 'required|in:processing,shipped,out_for_delivery,delivered,failed,cancelled',
            'tracking_number'         => 'nullable|string|max:100',
            'carrier'                 => 'nullable|string|max:100',
            'shipping_note'           => 'nullable|string|max:500',
            'estimated_delivery_date' => 'nullable|date',
            // Tracking event
            'event_location'          => 'nullable|string|max:200',
            'event_description'       => 'required|string|max:500',
        ]);

        $update = [
            'shipping_status'         => $data['shipping_status'],
            'tracking_number'         => $data['tracking_number'] ?? $order->tracking_number,
            'carrier'                 => $data['carrier']         ?? $order->carrier,
            'shipping_note'           => $data['shipping_note']   ?? $order->shipping_note,
            'estimated_delivery_date' => $data['estimated_delivery_date'] ?? $order->estimated_delivery_date,
        ];

        if ($data['shipping_status'] === 'shipped' && !$order->shipped_at)    $update['shipped_at']   = now();
        if ($data['shipping_status'] === 'delivered' && !$order->delivered_at) $update['delivered_at'] = now();

        $order->update($update);

        // Enregistrer l'événement de suivi
        OrderTrackingEvent::create([
            'order_id'    => $order->id,
            'status'      => $data['shipping_status'],
            'location'    => $data['event_location'] ?? null,
            'description' => $data['event_description'],
            'occurred_at' => now(),
            'created_by'  => auth()->id(),
        ]);

        // Notifier le client
        try {
            $order->user->notify(new \App\Notifications\ShippingUpdated($order));
        } catch (\Throwable $e) {
            logger()->warning('ShippingUpdated notify failed: '.$e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Statut de livraison mis à jour. Client notifié.');
    }

    public function addEvent(Request $request, Order $order)
    {
        $data = $request->validate([
            'location'    => 'nullable|string|max:200',
            'description' => 'required|string|max:500',
        ]);

        OrderTrackingEvent::create([
            'order_id'    => $order->id,
            'status'      => $order->shipping_status,
            'location'    => $data['location'] ?? null,
            'description' => $data['description'],
            'occurred_at' => now(),
            'created_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Événement de suivi ajouté.');
    }

    public function stock(Request $request)
    {
        $books = Book::where('print_on_demand', true)
            ->orWhere('physical_price', '>', 0)
            ->with('author:id,name')
            ->withCount(['physicalOrders as sold' => fn($q) => $q->where('payment_status', 'paid')])
            ->paginate(20);

        return view('admin.physical.stock', compact('books'));
    }

    public function addStock(Request $request, Book $book)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string',
        ]);
        $this->stockService->addStock($book->id, $request->quantity, $request->reason ?? 'Ajout manuel', auth()->id());
        return back()->with('success', "Stock mis à jour pour « {$book->title} ».");
    }
}
