<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\PriceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function index(): JsonResponse
    {
        $cryptos = Cryptocurrency::all(['id', 'name', 'symbol', 'current_price'])->map(function ($crypto) {
            return [
                'id' => $crypto->id,
                'name' => $crypto->name,
                'symbol' => $crypto->symbol,
                'price' => (float)$crypto->current_price,
                'current_price' => (float)$crypto->current_price
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $cryptos
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $crypto = Cryptocurrency::find($id);

        if (!$crypto) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cryptomonnaie non trouvée'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $crypto->id,
                'name' => $crypto->name,
                'symbol' => $crypto->symbol,
                'price' => (float)$crypto->current_price,
                'current_price' => (float)$crypto->current_price
            ]
        ]);
    }

    public function history(int $id): JsonResponse
    {
        $crypto = Cryptocurrency::find($id);

        if (!$crypto) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cryptomonnaie non trouvée'
            ], 404);
        }

        $history = PriceHistory::where('cryptocurrency_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(310)
            ->get(['price', 'created_at'])
            ->reverse()
            ->map(function ($record) {
                return [
                    'price' => (float)$record->price,
                    'timestamp' => $record->created_at->getTimestamp(),
                    'date' => $record->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'cryptocurrency_id' => $id,
                'name' => $crypto->name,
                'symbol' => $crypto->symbol,
                'history' => $history
            ]
        ]);
    }
}
