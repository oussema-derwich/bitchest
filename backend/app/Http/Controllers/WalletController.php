<?php

namespace App\Http\Controllers;

use App\Models\WalletCrypto;
use App\Models\Transaction;
use App\Models\Cryptocurrency;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Create a new WalletController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum,api');
    }

    /**
     * Display user's wallet with all holdings.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Portefeuille non trouvé'
            ], 404);
        }

        $holdings = $wallet->walletCryptos()->with('cryptocurrency')->get()->map(function ($holding) {
            return [
                'id' => $holding->id,
                'cryptocurrency_id' => $holding->cryptocurrency_id,
                'symbol' => $holding->cryptocurrency->symbol,
                'name' => $holding->cryptocurrency->name,
                'quantity' => $holding->quantity,
                'avg_buy_price' => $holding->avg_buy_price,
                'current_price' => $holding->cryptocurrency->current_price,
                'current_value' => $holding->getCurrentValue(),
                'profit_loss' => $holding->getProfitLoss(),
                'profit_loss_percentage' => $holding->getProfitLossPercentage()
            ];
        });

        $total_crypto_value = $holdings->sum('current_value');

        return response()->json([
            'status' => 'success',
            'data' => [
                'balance_eur' => (float)$user->balance_eur,
                'total_crypto_value' => (float)$total_crypto_value,
                'total_portfolio_value' => (float)$user->balance_eur + $total_crypto_value,
                'holdings' => $holdings
            ]
        ]);
    }

    /**
     * Buy cryptocurrency.
     */
    public function buy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cryptocurrency_id' => 'required|integer|exists:cryptos,id',
            'quantity' => 'required|numeric|min:0.00000001',
            'price' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $wallet = $user->wallet;
        $quantity = (float)$request->quantity;
        $price = (float)$request->price;
        $total_cost = $quantity * $price;

        // Vérifier le solde
        if ($user->balance_eur < $total_cost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Solde insuffisant pour cet achat'
            ], 400);
        }

        try {
            // Débiter le compte
            $user->balance_eur -= $total_cost;
            $user->save();

            // Trouver ou créer le holding
            $holding = WalletCrypto::firstOrCreate(
                ['wallet_id' => $wallet->id, 'cryptocurrency_id' => $request->cryptocurrency_id],
                ['quantity' => 0, 'avg_buy_price' => 0]
            );

            // Calculer le nouveau prix moyen
            $old_quantity = (float)$holding->quantity;
            $old_total_cost = $old_quantity * (float)$holding->avg_buy_price;
            $new_quantity = $old_quantity + $quantity;
            $new_avg_price = $new_quantity > 0 ? ($old_total_cost + $total_cost) / $new_quantity : $price;

            $holding->update([
                'quantity' => $new_quantity,
                'avg_buy_price' => $new_avg_price
            ]);

            // Créer la transaction
            Transaction::create([
                'user_id' => $user->id,
                'cryptocurrency_id' => $request->cryptocurrency_id,
                'type' => 'buy',
                'quantity' => $quantity,
                'price_at_transaction' => $price,
                'eur_amount' => $total_cost
            ]);

            // Créer notification d'achat
            $crypto = \App\Models\Cryptocurrency::find($request->cryptocurrency_id);
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'buy',
                'title' => "Achat réussi - {$crypto->symbol}",
                'message' => "Vous avez acheté {$quantity} {$crypto->symbol} à {$price}€ chacun (Total: {$total_cost}€)"
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Achat réussi',
                'data' => [
                    'transaction' => [
                        'type' => 'buy',
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $total_cost
                    ],
                    'new_balance' => (float)$user->balance_eur,
                    'holding' => [
                        'quantity' => $new_quantity,
                        'avg_buy_price' => $new_avg_price
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'achat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sell cryptocurrency.
     */
    public function sell(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cryptocurrency_id' => 'required|integer|exists:cryptos,id',
            'quantity' => 'required|numeric|min:0.00000001',
            'price' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $wallet = $user->wallet;
        $quantity = (float)$request->quantity;
        $price = (float)$request->price;
        $total_revenue = $quantity * $price;

        // Vérifier le holding
        $holding = WalletCrypto::where('wallet_id', $wallet->id)
            ->where('cryptocurrency_id', $request->cryptocurrency_id)
            ->first();

        if (!$holding || (float)$holding->quantity < $quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quantité insuffisante pour cette vente'
            ], 400);
        }

        try {
            // Créditer le compte
            $user->balance_eur += $total_revenue;
            $user->save();

            // Réduire le holding
            $new_quantity = (float)$holding->quantity - $quantity;

            if ($new_quantity <= 0) {
                $holding->delete();
            } else {
                $holding->update(['quantity' => $new_quantity]);
            }

            // Créer la transaction
            Transaction::create([
                'user_id' => $user->id,
                'cryptocurrency_id' => $request->cryptocurrency_id,
                'type' => 'sell',
                'quantity' => $quantity,
                'price_at_transaction' => $price,
                'eur_amount' => $total_revenue
            ]);

            // Créer notification de vente
            $crypto = \App\Models\Cryptocurrency::find($request->cryptocurrency_id);
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'sell',
                'title' => "Vente réussie - {$crypto->symbol}",
                'message' => "Vous avez vendu {$quantity} {$crypto->symbol} à {$price}€ chacun (Total: {$total_revenue}€)"
            ]);

            // Notifier si solde trop bas
            if ($user->balance_eur < 100) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'low_balance',
                    'title' => "Solde faible",
                    'message' => "Votre solde EUR est maintenant de {$user->balance_eur}€"
                ]);
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Vente réussie',
                'data' => [
                    'transaction' => [
                        'type' => 'sell',
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $total_revenue
                    ],
                    'new_balance' => (float)$user->balance_eur,
                    'remaining_quantity' => $new_quantity
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la vente: ' . $e->getMessage()
            ], 500);
        }
    }
}
