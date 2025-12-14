<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\RegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistrationRequestController extends Controller
{
    /**
     * Créer une demande d'inscription (tous les rôles, par défaut client)
     */
    public function createRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:client,trader,analyst,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Utiliser le rôle fourni ou 'client' par défaut
        $role = $request->role ?? 'client';

        // Créer l'utilisateur (compte désactivé par défaut)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'is_active' => false
        ]);

        // Créer la demande d'inscription
        RegistrationRequest::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'role' => $role,
            'is_approved' => false,
            'is_rejected' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Demande d\'inscription créée. En attente d\'approbation admin.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * Admin : Récupérer toutes les demandes d'inscription
     */
    public function getAllRequests(): JsonResponse
    {
        $this->authorizeAdmin();

        $requests = RegistrationRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Admin : Approuver une demande d'inscription
     */
    public function approveRequest($id): JsonResponse
    {
        $this->authorizeAdmin();

        $request = RegistrationRequest::findOrFail($id);
        $user = $request->user;

        // Activer l'utilisateur
        $user->is_active = true;
        $user->save();

        // Créer son wallet automatiquement
        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 500, // Solde initial
                'public_address' => 'public_' . uniqid(),
                'private_address' => 'private_' . uniqid(),
            ]
        );

        // Marquer la demande comme approuvée
        $request->is_approved = true;
        $request->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Demande d\'inscription approuvée',
            'user' => $user
        ]);
    }

    /**
     * Admin : Rejeter une demande d'inscription
     */
    public function rejectRequest(Request $request, $id): JsonResponse
    {
        $this->authorizeAdmin();

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $registrationRequest = RegistrationRequest::findOrFail($id);
        $user = $registrationRequest->user;

        // Marquer comme rejetée
        $registrationRequest->is_rejected = true;
        $registrationRequest->rejection_reason = $request->rejection_reason;
        $registrationRequest->save();

        // Supprimer l'utilisateur (optionnel)
        // $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Demande d\'inscription rejetée',
        ]);
    }

    /**
     * Admin : Vérifier l'état d'une demande d'inscription
     */
    public function getRequestStatus($id): JsonResponse
    {
        $registrationRequest = RegistrationRequest::with('user')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $registrationRequest
        ]);
    }

    /**
     * Vérifier que l'utilisateur est admin
     */
    private function authorizeAdmin(): void
    {
        $user = auth('sanctum')->user();
        
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès refusé. Vous devez être administrateur.');
        }
    }
}
