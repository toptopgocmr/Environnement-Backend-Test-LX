<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\{Book, Order, Royalty, WithdrawalRequest, BookTag};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, DB};
use Illuminate\Support\Str;

class EarningsController extends Controller
{
    public function index()
    {
        $author = Auth::user();

        $royalties = Royalty::where('author_id', $author->id)
            ->with('order.book')->latest()->paginate(15);

        $summary = [
            'total_earned'    => $author->royalties()->where('status', 'paid')->sum('net_amount'),
            'pending_balance' => $author->royalties()->where('status', 'pending')->sum('net_amount'),
            'total_withdrawn' => WithdrawalRequest::where('author_id', $author->id)
                                    ->where('status', 'completed')->sum('amount'),
        ];

        $withdrawals = WithdrawalRequest::where('author_id', $author->id)->latest()->paginate(10);

        return view('author.earnings.index', compact('royalties', 'summary', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request)
    {
        $author         = Auth::user();
        $pendingBalance = $author->royalties()->where('status', 'pending')->sum('net_amount');

        $data = $request->validate([
            'amount'         => "required|numeric|min:5000|max:{$pendingBalance}",
            'method'         => 'required|in:mtn_momo,airtel_money,bank',
            'account_number' => 'required|string|max:20',
            'account_name'   => 'required|string|max:100',
        ]);

        if (WithdrawalRequest::where('author_id', $author->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Vous avez déjà une demande de retrait en attente.');
        }

        WithdrawalRequest::create(array_merge($data, [
            'author_id'      => $author->id,
            'currency'       => 'XAF',
            'balance_before' => $pendingBalance,
        ]));

        return back()->with('success', 'Demande soumise. Traitement sous 48h.');
    }
}
