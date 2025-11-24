# 🎯 GUIDE IMPLÉMENTATION DES CONTRÔLEURS

## 1️⃣ AuthController (Priorité CRITIQUE)

**Fichier:** `app/Http/Controllers/Auth/AuthController.php`

### Méthodes à implémenter:

#### `login(LoginRequest $request)`
```php
// Vérifier credentials
// Retourner: { user, token, role }
// Gestion JWT + Sanctum compatible
// Si temp_password non null → warning
```

#### `logout(Request $request)`
```php
// Révoquer token Sanctum
// Retourner: { message: "Logged out" }
```

#### `profile(Request $request)`
```php
// Retourner: user + balance_eur + role
// Format: { id, name, email, balance_eur, role }
```

#### `updateProfile(UpdateProfileRequest $request)`
```php
// Mettre à jour: name, email, password
// NE PAS toucher à balance_eur
// Si nouveau password: hasher avec bcrypt
// Retourner user mise à jour
```

### ⚠️ À IMPLÉMENTER:
- [ ] Check temp_password lors du login
- [ ] Rediriger vers change-password si temp_password rempli
- [ ] Effacer temp_password après changement de mot de passe

---

## 2️⃣ WalletController (Priorité CRITIQUE ⚡)

**Fichier:** `app/Http/Controllers/WalletController.php`

### Méthodes:

#### `index(Request $request)` - GET /api/wallet
```php
// Retourner le wallet de l'utilisateur avec toutes les cryptos
// Format:
{
  "id": 1,
  "user_id": 1,
  "cryptocurrencies": [
    {
      "id": 1,
      "name": "Bitcoin",
      "symbol": "BTC",
      "quantity": 0.5,
      "avg_buy_price": 40000,
      "current_price": 42500,
      "current_value": 21250,
      "invested": 20000,
      "profit_loss": 1250,
      "profit_loss_percentage": 6.25
    }
  ],
  "total_value": 21250,
  "total_invested": 20000,
  "total_profit_loss": 1250
}
```

#### `buy(BuyRequest $request)` - POST /api/buy
```php
// LOGIQUE CRITIQUE:
$cryptocurrency = Cryptocurrency::find($request->cryptocurrency_id);
$wallet = auth()->user()->wallet;
$quantity = $request->quantity;
$currentPrice = $request->price;

// Vérifier balance suffisante
$totalCost = $quantity * $currentPrice;
if (auth()->user()->balance_eur < $totalCost) {
    return error(400, "Insufficient balance");
}

// Trouver ou créer WalletCrypto
$walletCrypto = WalletCrypto::firstOrCreate(
    ['wallet_id' => $wallet->id, 'cryptocurrency_id' => $cryptocurrency->id],
    ['quantity' => 0, 'avg_buy_price' => 0]
);

// CALCULER avg_buy_price (FORMULE CAHIER)
$totalInvestedBefore = $walletCrypto->quantity * $walletCrypto->avg_buy_price;
$newInvested = $quantity * $currentPrice;
$newTotalInvested = $totalInvestedBefore + $newInvested;
$newQuantity = $walletCrypto->quantity + $quantity;
$newAvgPrice = $newTotalInvested / $newQuantity;

// Update wallet_crypto
$walletCrypto->update([
    'quantity' => $newQuantity,
    'avg_buy_price' => $newAvgPrice
]);

// Débit balance
auth()->user()->decrement('balance_eur', $totalCost);

// Log transaction
Transaction::create([
    'user_id' => auth()->id(),
    'cryptocurrency_id' => $cryptocurrency->id,
    'type' => 'buy',
    'quantity' => $quantity,
    'price_at_transaction' => $currentPrice,
    'eur_amount' => $totalCost
]);

return success(["balance" => auth()->user()->balance_eur, "wallet" => $wallet]);
```

#### `sell(SellRequest $request)` - POST /api/sell
```php
// Vérifier que user possède la crypto
// Vérifier que quantity ≤ quantity possédée
// Réduire quantity (avg_buy_price inchangé)
// Créditer balance_eur = quantity × current_price
// Log transaction type='sell'
```

---

## 3️⃣ CryptoController

**Fichier:** `app/Http/Controllers/CryptoController.php`

### Méthodes:

#### `index()` - GET /api/cryptocurrencies
```php
// Retourner liste des 10 cryptos
// Format: [{ id, name, symbol, current_price }]
```

#### `show($id)` - GET /api/cryptocurrencies/{id}
```php
// Retourner crypto avec détails
// Format: { id, name, symbol, current_price }
```

#### `history($id)` - GET /api/cryptocurrencies/{id}/history
```php
// Retourner 31 jours de prix historiques
// Format: [
//   { date: "2025-11-14", price: 42500 },
//   { date: "2025-11-13", price: 41800 },
//   ...
// ]
// Ou format tableau simple: [42500, 41800, ...]
```

---

## 4️⃣ TransactionController

**Fichier:** `app/Http/Controllers/TransactionController.php`

### Méthodes:

#### `index(Request $request)` - GET /api/transactions
```php
// Retourner les transactions de l'utilisateur
// Format: [
//   {
//     id,
//     cryptocurrency: { id, name, symbol },
//     type: "buy|sell",
//     quantity,
//     price_at_transaction,
//     eur_amount,
//     created_at
//   }
// ]
```

---

## 5️⃣ AdminController

**Fichier:** `app/Http/Controllers/AdminController.php`

### Méthodes:

#### `getUsers()` - GET /api/admin/users
```php
// Retourner liste des clients UNIQUEMENT (role='client')
// Format: [
//   { id, name, email, role, balance_eur, is_active, created_at }
// ]
```

#### `storeUser(Request $request)` - POST /api/admin/users
```php
// Créer nouvel utilisateur avec temp_password
// Retourner: { user, temp_password }
// Afficher temp_password UNE SEULE FOIS
```

#### `updateUser($id, Request $request)` - PUT /api/admin/users/{id}
```php
// Mettre à jour user (sauf password)
// NE PAS toucher à password ni balance_eur
```

#### `deleteUser($id)` - DELETE /api/admin/users/{id}
```php
// Supprimer user et son wallet associé
```

#### `getStats()` - GET /api/admin/stats
```php
// Retourner stats dashboard:
{
  "total_users": 42,
  "total_clients": 40,
  "total_balance": 20000,
  "total_transactions": 150,
  "total_cryptos": 10,
  "total_price_histories": 310
}
```

---

## 🔑 POINTS CRITIQUES

### ✅ Formule avg_buy_price (COPIER-COLLER)
```php
$totalInvestedBefore = $walletCrypto->quantity * $walletCrypto->avg_buy_price;
$newInvested = $quantity * $currentPrice;
$newTotalInvested = $totalInvestedBefore + $newInvested;
$newQuantity = $walletCrypto->quantity + $quantity;
$newAvgPrice = $newTotalInvested / $newQuantity;
```

### ✅ Relations à utiliser
```php
auth()->user()->wallet                    // Wallet unique
auth()->user()->wallet->walletCryptos     // Tous les cryptos du wallet
$walletCrypto->cryptocurrency            // La crypto
$walletCrypto->cryptocurrency->currentPrice  // Prix actuel
```

### ✅ Valeurs à PROTÉGER
- `balance_eur` - jamais modifiable directement (via buy/sell seulement)
- `quantity` dans WalletCrypto - jamais négatif
- `temp_password` - affichage unique, puis null

### ✅ Erreurs à gérer
```php
// Solde insuffisant
if (auth()->user()->balance_eur < $totalCost) {
    return response()->json([
        'error' => 'Insufficient balance'
    ], 422);
}

// Crypto insuffisante à la vente
if ($walletCrypto->quantity < $sellQuantity) {
    return response()->json([
        'error' => 'Insufficient quantity'
    ], 422);
}

// Crypto non possédée
if (!$wallet->walletCryptos->where('cryptocurrency_id', $id)->first()) {
    return response()->json([
        'error' => 'You do not own this cryptocurrency'
    ], 404);
}
```

---

## 🚀 STRUCTURE RECOMMANDÉE

```php
// BaseController avec méthodes utilitaires
class Controller extends BaseController
{
    protected function success($data = [], $message = 'Success', $code = 200) {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($message = 'Error', $code = 400, $data = []) {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
```

---

## ✅ TESTING CHECKLIST

### AuthController
- [ ] Login avec credentials valides → token reçu
- [ ] Login avec mauvais password → erreur 401
- [ ] Logout → token révoqué
- [ ] GET /api/me retourne user
- [ ] PUT /api/profile met à jour user

### WalletController
- [ ] GET /api/wallet retourne structure complète
- [ ] POST /api/buy crée WalletCrypto + log transaction
- [ ] avg_buy_price calculé correctement
- [ ] balance_eur débité correctement
- [ ] POST /api/sell inverse l'opération
- [ ] Erreur si solde insuffisant
- [ ] Erreur si quantity insuffisante à la vente

### CryptoController
- [ ] GET /api/cryptocurrencies retourne 10 cryptos
- [ ] GET /api/cryptocurrencies/{id}/history retourne 31 jours

### AdminController
- [ ] GET /api/admin/users retourne liste clients
- [ ] POST /api/admin/users crée user avec temp_password
- [ ] PUT /api/admin/users/{id} ne touche pas password
- [ ] DELETE /api/admin/users/{id} supprime user

---

**Temps estimé: 3-4 heures**
**Difficulté: MOYENNE**
**Criticalité: HAUTE**

