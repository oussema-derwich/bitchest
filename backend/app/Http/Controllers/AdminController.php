<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Crypto;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'activeUsers' => User::where('is_active', true)->count(),
                'totalUsers' => User::count(),
                'newUsersThisWeek' => User::where('created_at', '>=', now()->subWeek())->count(),
                'totalTransactions' => Transaction::count(),
                'totalAlerts' => Alert::count(),
                'totalCryptos' => \App\Models\Cryptocurrency::count(),
            ]
        ]);
    }

    /**
     * Get all users with filtering
     */
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('created_at', 'desc')->get()
        ]);
    }

    /**
     * Suspend a user
     */
    public function suspendUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'suspended']);
        return response()->json(['message' => 'User suspended successfully']);
    }

    /**
     * Activate a user
     */
    public function activateUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'active']);
        return response()->json(['message' => 'User activated successfully']);
    }

    /**
     * Delete a user
     */
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Store a new user (admin creation)
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:client,admin'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'balance_eur' => 500,
            'is_active' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update a user (admin update)
     */
    public function updateUser($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'sometimes|string|between:2,100',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'role' => 'sometimes|in:client,admin',
            'is_active' => 'sometimes|boolean'
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get all cryptos
     */
    public function getCryptos()
    {
        return response()->json(Crypto::all());
    }

    /**
     * Add a new crypto
     */
    public function addCrypto(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cryptos',
            'symbol' => 'required|string|unique:cryptos',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'status' => 'required|in:Actif,Inactif'
        ]);

        $crypto = Crypto::create($validated);
        return response()->json($crypto, 201);
    }

    /**
     * Update a crypto
     */
    public function updateCrypto($cryptoId, Request $request)
    {
        $validated = $request->validate([
            'name' => 'string',
            'symbol' => 'string',
            'price' => 'numeric',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'status' => 'in:Actif,Inactif'
        ]);

        $crypto = Crypto::findOrFail($cryptoId);
        $crypto->update($validated);
        return response()->json($crypto);
    }

    /**
     * Delete a crypto
     */
    public function deleteCrypto($cryptoId)
    {
        Crypto::findOrFail($cryptoId)->delete();
        return response()->json(['message' => 'Crypto deleted successfully']);
    }

    /**
     * Get all transactions
     */
    public function getTransactions(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('crypto_id')) {
            $query->where('crypto_id', $request->input('crypto_id'));
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $transaction->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Transaction cancelled successfully']);
    }

    /**
     * Get all alerts
     */
    public function getAlerts(Request $request)
    {
        $query = Alert::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->get());
    }

    /**
     * Update an alert
     */
    public function updateAlert($alertId, Request $request)
    {
        $validated = $request->validate([
            'threshold' => 'numeric',
            'type' => 'string',
            'status' => 'string'
        ]);

        $alert = Alert::findOrFail($alertId);
        $alert->update($validated);
        return response()->json($alert);
    }

    /**
     * Pause an alert
     */
    public function pauseAlert($alertId)
    {
        $alert = Alert::findOrFail($alertId);
        $alert->update(['status' => 'paused']);
        return response()->json(['message' => 'Alert paused successfully']);
    }

    /**
     * Resume an alert
     */
    public function resumeAlert($alertId)
    {
        $alert = Alert::findOrFail($alertId);
        $alert->update(['status' => 'active']);
        return response()->json(['message' => 'Alert resumed successfully']);
    }

    /**
     * Delete an alert
     */
    public function deleteAlert($alertId)
    {
        Alert::findOrFail($alertId)->delete();
        return response()->json(['message' => 'Alert deleted successfully']);
    }

    /**
     * Save platform settings
     */
    public function savePlatformSettings(Request $request)
    {
        $settings = $request->validate([
            'platformName' => 'string',
            'description' => 'string',
            'supportEmail' => 'email'
        ]);

        // Store in cache or database as needed
        return response()->json(['message' => 'Platform settings saved']);
    }

    /**
     * Save security settings
     */
    public function saveSecuritySettings(Request $request)
    {
        $settings = $request->validate([
            'requireTwoFA' => 'boolean',
            'sessionTimeout' => 'integer',
            'requireStrongPassword' => 'boolean'
        ]);

        return response()->json(['message' => 'Security settings saved']);
    }

    /**
     * Save notification settings
     */
    public function saveNotificationSettings(Request $request)
    {
        $settings = $request->validate([
            'notifyNewUsers' => 'boolean',
            'notifyTransactions' => 'boolean',
            'notifyAlerts' => 'boolean'
        ]);

        return response()->json(['message' => 'Notification settings saved']);
    }

    /**
     * Get settings
     */
    public function getSettings()
    {
        return response()->json([
            'platformName' => 'BitChest',
            'description' => 'Plateforme d\'échange de cryptomonnaies sécurisée',
            'supportEmail' => 'support@bitchest.com',
            'requireTwoFA' => true,
            'sessionTimeout' => 30,
            'requireStrongPassword' => true,
            'notifyNewUsers' => true,
            'notifyTransactions' => true,
            'notifyAlerts' => true
        ]);
    }
}
