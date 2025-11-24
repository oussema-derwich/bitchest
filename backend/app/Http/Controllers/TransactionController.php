<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum,api');
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->with('cryptocurrency')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'cryptocurrency' => $transaction->cryptocurrency->symbol,
                    'quantity' => (float)$transaction->quantity,
                    'price' => (float)$transaction->price_at_transaction,
                    'total' => (float)$transaction->eur_amount,
                    'date' => $transaction->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }
}
