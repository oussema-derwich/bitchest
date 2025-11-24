<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::with(['crypto'])
            ->where('user_id', Auth::id())
            ->get();
            
        return response()->json($alerts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'crypto_id' => 'required|exists:cryptos,id',
            'price_threshold' => 'required|numeric|min:0',
            'type' => 'required|in:above,below',
        ]);

        $alert = Alert::create([
            'user_id' => Auth::id(),
            'crypto_id' => $validated['crypto_id'],
            'price_threshold' => $validated['price_threshold'],
            'type' => $validated['type'],
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Alerte créée avec succès',
            'alert' => $alert
        ]);
    }

    public function update(Request $request, Alert $alert)
    {
        // Vérifier que l'alerte appartient à l'utilisateur
        if ($alert->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'price_threshold' => 'sometimes|numeric|min:0',
            'type' => 'sometimes|in:above,below',
            'is_active' => 'sometimes|boolean'
        ]);

        $alert->update($validated);

        return response()->json([
            'message' => 'Alerte mise à jour avec succès',
            'alert' => $alert
        ]);
    }

    public function destroy(Alert $alert)
    {
        if ($alert->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $alert->delete();

        return response()->json(['message' => 'Alerte supprimée avec succès']);
    }
}