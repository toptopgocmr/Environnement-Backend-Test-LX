<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Book, Order, Royalty, Category, WithdrawalRequest};
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = WithdrawalRequest::with('author')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(WithdrawalRequest $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update(['status' => 'processing']);
            // En production : déclencher paiement MoMo/Airtel ici
            $withdrawal->update(['status' => 'completed']);
            $withdrawal->author->royalties()
                ->where('status', 'pending')
                ->update(['status' => 'paid', 'paid_at' => now()]);
        });
        return back()->with('success', 'Retrait approuvé et traité.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate(['reason' => 'required|string']);
        $withdrawal->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        return back()->with('success', 'Demande rejetée.');
    }
}
