# 🚀 GUIDE D'IMPLÉMENTATION - RECOMMANDATIONS PRIORITAIRES

**Pour atteindre 100/100 avant le jury**

---

## 1️⃣ SYSTÈME DE NOTIFICATIONS (CRITIQUE - 30 MIN)

### Étape 1: Créer la Migration

**Fichier:** `database/migrations/XXXX_XX_XX_XXXXXX_create_notifications_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['buy', 'sell', 'low_balance', 'alert'])->default('buy');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Index pour les requêtes rapides
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

---

### Étape 2: Créer le Model

**Fichier:** `app/Models/Notification.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    /**
     * Get the user that owns this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}
```

---

### Étape 3: Ajouter la Relation dans User Model

**Fichier:** `app/Models/User.php` (ajouter après `transactions()`)

```php
/**
 * Get the notifications for the user.
 */
public function notifications()
{
    return $this->hasMany(Notification::class);
}

/**
 * Get unread notifications count.
 */
public function getUnreadNotificationsCount()
{
    return $this->notifications()->where('is_read', false)->count();
}
```

---

### Étape 4: Créer NotificationController

**Fichier:** `app/Http/Controllers/NotificationController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum,api');
    }

    /**
     * Get all notifications for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $unread = $request->query('unread', false);

        $query = $user->notifications();

        if ($unread) {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $user->getUnreadNotificationsCount()
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Authorize the user
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted'
        ]);
    }
}
```

---

### Étape 5: Ajouter Routes

**Fichier:** `routes/api.php` (dans le groupe `middleware('auth:sanctum,api')`)

```php
// Notifications
Route::get('notifications', [NotificationController::class, 'index']);
Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
```

---

### Étape 6: Intégrer dans WalletController

**Fichier:** `app/Http/Controllers/WalletController.php`

**Dans `buy()` après création de la transaction (ligne 130):**

```php
// Créer notification d'achat
\App\Models\Notification::create([
    'user_id' => $user->id,
    'type' => 'buy',
    'title' => "Achat réussi - {$request->cryptocurrency->symbol}",
    'message' => "Vous avez acheté {$quantity} {$request->cryptocurrency->symbol} à {$price}€ chacun (Total: {$total_cost}€)"
]);
```

**Dans `sell()` après création de la transaction (ligne 210):**

```php
// Créer notification de vente
\App\Models\Notification::create([
    'user_id' => $user->id,
    'type' => 'sell',
    'title' => "Vente réussie - {$request->cryptocurrency->symbol}",
    'message' => "Vous avez vendu {$quantity} {$request->cryptocurrency->symbol} à {$price}€ chacun (Total: {$total_revenue}€)"
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
```

---

### Étape 7: Exécuter la Migration

```bash
php artisan migrate
```

✅ **Notifications intégrées !**

---

## 2️⃣ ADRESSES WALLET (IMPORTANT - 20 MIN)

### Étape 1: Créer une Migration

**Fichier:** `database/migrations/XXXX_XX_XX_XXXXXX_add_addresses_to_wallets.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('public_address')->nullable()->after('user_id');
            $table->string('private_address')->nullable()->after('public_address');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['public_address', 'private_address']);
        });
    }
};
```

---

### Étape 2: Mettre à jour UserObserver

**Fichier:** `app/Observers/UserObserver.php`

Modifier la méthode `created()`:

```php
public function created(User $user): void
{
    // Create wallet for new user
    $wallet = Wallet::create([
        'user_id' => $user->id,
        'public_address' => $this->generatePublicAddress(),
        'private_address' => $this->encryptPrivateAddress($this->generatePrivateAddress())
    ]);

    // Set balance to 500 EUR for new clients
    if ($user->role === 'client') {
        $user->update(['balance_eur' => 500]);
    }
}

/**
 * Generate a realistic public address (Ethereum-style).
 */
private function generatePublicAddress(): string
{
    return '0x' . strtolower(bin2hex(random_bytes(20)));
}

/**
 * Generate a private address.
 */
private function generatePrivateAddress(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Encrypt private address for security.
 */
private function encryptPrivateAddress(string $address): string
{
    return encrypt($address);
}
```

---

### Étape 3: Mettre à jour Wallet Model

**Fichier:** `app/Models/Wallet.php`

Ajouter la méthode pour décrypter:

```php
/**
 * Get the decrypted private address.
 */
public function getDecryptedPrivateAddress(): ?string
{
    if (!$this->private_address) {
        return null;
    }

    try {
        return decrypt($this->private_address);
    } catch (\Exception $e) {
        return null;
    }
}
```

---

### Étape 4: Mettre à jour WalletController

**Fichier:** `app/Http/Controllers/WalletController.php`

Dans `index()`, ajouter les adresses à la réponse:

```php
return response()->json([
    'status' => 'success',
    'data' => [
        'balance_eur' => (float)$user->balance_eur,
        'total_crypto_value' => (float)$total_crypto_value,
        'total_portfolio_value' => (float)$user->balance_eur + $total_crypto_value,
        'wallet' => [
            'public_address' => $wallet->public_address,
            'private_address' => $wallet->private_address ? '●●●●●●●●' : null
            // Ne JAMAIS retourner la clé privée décryptée à moins d'être absolument certain
        ],
        'holdings' => $holdings
    ]
]);
```

---

### Étape 5: Exécuter la Migration

```bash
php artisan migrate
```

✅ **Adresses générées automatiquement !**

---

## 3️⃣ MIDDLEWARE ADMIN (VÉRIFICATION - 5 MIN)

### Vérifier que AdminMiddleware existe

**Fichier:** `app/Http/Middleware/AdminMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized - Admin role required'
        ], 403);
    }
}
```

### Vérifier Kernel.php

**Fichier:** `app/Http/Kernel.php`

Ajouter dans `protected $routeMiddleware`:

```php
'admin' => \App\Http\Middleware\AdminMiddleware::class,
```

✅ **Middleware OK !**

---

## 4️⃣ RATE LIMITING (RECOMMANDÉ - 10 MIN)

### Configurer ThrottleRequests

**Fichier:** `routes/api.php`

```php
// Public routes avec rate limiting strict
Route::middleware('throttle:5,1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
});

// Protected routes avec limit modéré
Route::middleware('auth:sanctum,api','throttle:60,1')->group(function() {
    Route::post('buy', [WalletController::class, 'buy']);
    Route::post('sell', [WalletController::class, 'sell']);
    Route::post('alerts', [AlertController::class, 'store']);
});

// Admin routes
Route::middleware('auth:sanctum,api','admin','throttle:30,1')->prefix('admin')->group(function() {
    Route::post('users', [AdminController::class, 'storeUser']);
    Route::put('users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
});
```

✅ **Rate limiting actif !**

---

## 5️⃣ TESTS UNITAIRES (BONUS - 30 MIN)

### Créer Test d'Achat

**Fichier:** `tests/Feature/WalletControllerTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cryptocurrency;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    /**
     * Test that a user can buy cryptocurrency.
     */
    public function test_user_can_buy_crypto()
    {
        // Setup
        $user = User::factory()->create(['balance_eur' => 1000]);
        $crypto = Cryptocurrency::first();
        
        // Act
        $response = $this->actingAs($user)->postJson('/api/buy', [
            'cryptocurrency_id' => $crypto->id,
            'quantity' => 1,
            'price' => 50000
        ]);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $this->assertEquals(950000, $user->fresh()->balance_eur);
    }

    /**
     * Test that a user cannot buy with insufficient balance.
     */
    public function test_user_cannot_buy_with_insufficient_balance()
    {
        $user = User::factory()->create(['balance_eur' => 100]);
        $crypto = Cryptocurrency::first();
        
        $response = $this->actingAs($user)->postJson('/api/buy', [
            'cryptocurrency_id' => $crypto->id,
            'quantity' => 1,
            'price' => 50000
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('status', 'error');
    }

    /**
     * Test that a user can sell cryptocurrency.
     */
    public function test_user_can_sell_crypto()
    {
        // Setup - Create user with holdings
        $user = User::factory()->create(['balance_eur' => 1000]);
        $wallet = $user->wallet;
        $crypto = Cryptocurrency::first();
        
        // Create a holding
        $wallet->walletCryptos()->create([
            'cryptocurrency_id' => $crypto->id,
            'quantity' => 2,
            'avg_buy_price' => 25000
        ]);

        // Act - Sell 1 unit
        $response = $this->actingAs($user)->postJson('/api/sell', [
            'cryptocurrency_id' => $crypto->id,
            'quantity' => 1,
            'price' => 30000
        ]);

        // Assert
        $response->assertStatus(201);
        $this->assertEquals(1030000, $user->fresh()->balance_eur);
    }
}
```

### Exécuter les Tests

```bash
php artisan test tests/Feature/WalletControllerTest.php
```

✅ **Tests en place !**

---

## 📋 CHECKLIST D'IMPLÉMENTATION

```
☐ Notifications
  ☐ Migration créée et exécutée
  ☐ Model Notification créé
  ☐ NotificationController créé
  ☐ Routes ajoutées
  ☐ Intégration WalletController buy()
  ☐ Intégration WalletController sell()
  ☐ Relation User::notifications() ajoutée
  
☐ Adresses Wallet
  ☐ Migration adresses créée et exécutée
  ☐ UserObserver mis à jour
  ☐ Wallet Model updated
  ☐ WalletController affiche adresses
  
☐ Middleware Admin
  ☐ AdminMiddleware exists
  ☐ Kernel.php updated
  ☐ Routes admin protégées
  
☐ Rate Limiting
  ☐ Throttle appliqué à routes publiques
  ☐ Throttle appliqué à routes métier
  ☐ Throttle appliqué à routes admin

☐ Tests
  ☐ Tests achat créés
  ☐ Tests vente créés
  ☐ Tests authentification créés
```

---

## 🎯 TEMPS ESTIMÉ

| Tâche | Temps | Priorité |
|-------|-------|----------|
| Notifications | 30 min | 🔴 CRITIQUE |
| Adresses Wallet | 20 min | 🟠 IMPORTANT |
| Middleware Admin | 5 min | 🟢 VÉRIF RAPIDE |
| Rate Limiting | 10 min | 🟡 RECOMMANDÉ |
| Tests | 30 min | 🟡 BONUS |
| **TOTAL** | **95 min** | |

**Pour 100/100 avant jury:** Notifications + Adresses = 50 min ✅

---

## 🚀 COMMANDES RAPIDES

```bash
# Créer tout d'un coup
php artisan make:migration create_notifications_table
php artisan make:model Notification
php artisan make:controller NotificationController
php artisan make:migration add_addresses_to_wallets

# Exécuter migrations
php artisan migrate

# Vérifier status
php artisan migrate:status

# Tester
php artisan test
```

---

**Bonne chance pour le jury ! 🎉**
