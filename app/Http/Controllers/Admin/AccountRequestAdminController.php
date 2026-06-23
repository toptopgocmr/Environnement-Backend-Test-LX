<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AiReview, AccountRequest, Book, Order, User, ChatConversation, PhysicalStockMovement};
use App\Services\{AiReviewService, ChatService, PhysicalStockService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ADMIN — DEMANDES D'ACTIVATION
class AccountRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $requests = AccountRequest::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->latest()->paginate(20);

        $counts = [
            'pending'  => AccountRequest::where('status', 'pending')->count(),
            'approved' => AccountRequest::where('status', 'approved')->count(),
            'rejected' => AccountRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.accounts.index', compact('requests', 'counts'));
    }

    public function show(AccountRequest $accountRequest)
    {
        $accountRequest->load('user');
        return view('admin.accounts.show', compact('accountRequest'));
    }

    public function approve(Request $request, AccountRequest $accountRequest)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($accountRequest, $request) {
            $accountRequest->update([
                'status'       => 'approved',
                'admin_note'   => $request->note,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
            ]);

            // Activer le compte + attribuer le bon rôle
            $user = $accountRequest->user;
            $user->update([
                'is_active'          => true,
                'role'               => $accountRequest->type === 'institution' ? 'author' : $accountRequest->type,
                'is_verified_author' => $accountRequest->type === 'author',
            ]);

            // Notifier
            $user->notify(new \App\Notifications\AccountApproved($accountRequest->type));
        });

        return back()->with('success', 'Compte activé avec succès.');
    }

    public function reject(Request $request, AccountRequest $accountRequest)
    {
        $request->validate(['note' => 'required|string|max:500']);

        $accountRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $accountRequest->user->notify(new \App\Notifications\AccountRejected($request->note));

        return back()->with('success', 'Demande rejetée.');
    }
}
