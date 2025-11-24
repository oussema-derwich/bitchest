<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum,api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'balance_eur' => 500,
            'is_active' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        \Log::info('Tentative de connexion', ['email' => $request->email]);
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($user) {
                $user->increment('login_attempts');
                $user->last_login_attempt = Carbon::now();
                $user->save();

                if ($user->login_attempts >= 3) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Compte temporairement bloqué. Réessayez plus tard.'
                    ], 401);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants non valides'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Compte désactivé'
            ], 401);
        }

        // Réinitialiser les tentatives
        $user->login_attempts = 0;
        $user->save();

        // Créer token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): JsonResponse
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Get the authenticated User.
     */
    public function profile(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user' => auth('sanctum')->user()
        ]);
    }

    /**
     * Update the authenticated User profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|between:2,100',
            'email' => 'sometimes|string|email|max:100|unique:users,email,' . auth('sanctum')->id(),
            'password' => 'sometimes|string|min:8|confirmed',
            'old_password' => 'required_with:password|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();

        // Vérifier l'ancien mot de passe si un nouveau est fourni
        if ($request->has('password')) {
            if (!$request->filled('old_password')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ancien mot de passe requis'
                ], 422);
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ancien mot de passe incorrect'
                ], 401);
            }

            $user->password = Hash::make($request->password);
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }
}
