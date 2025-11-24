# ⚠️ ERREURS À ÉVITER

## ❌ Erreurs courantes lors de l'implémentation des contrôleurs

### 1. avg_buy_price Calculation (CRITIQUE)

#### ❌ MAUVAIS
```php
// NE PAS faire ça:
$newAvgPrice = ($walletCrypto->avg_buy_price + $currentPrice) / 2;  // Moyenne naïve!

// NE PAS faire ça:
$newAvgPrice = $currentPrice;  // Ignorer l'historique!

// NE PAS faire ça:
$newAvgPrice = ($walletCrypto->quantity + $quantity) / 2;  // Quantités, pas prix!
```

#### ✅ CORRECT
```php
$totalInvestedBefore = $walletCrypto->quantity * $walletCrypto->avg_buy_price;
$newInvested = $quantity * $currentPrice;
$newTotalInvested = $totalInvestedBefore + $newInvested;
$newQuantity = $walletCrypto->quantity + $quantity;
$newAvgPrice = $newTotalInvested / $newQuantity;
```

### 2. balance_eur Manipulation

#### ❌ MAUVAIS
```php
// NE PAS permettre direct update du balance_eur:
$user->update(['balance_eur' => 1000]);  // ❌ GRAVE!

// NE PAS permettre dans le contrôleur:
$user->balance_eur = 500;
$user->save();  // ❌ GRAVE!

// NE PAS accepter le balance dans la requête:
$user->update($request->all());  // ❌ Si balance est dans $request!
```

#### ✅ CORRECT
```php
// Débiter lors d'un achat:
auth()->user()->decrement('balance_eur', $totalCost);

// Créditer lors d'une vente:
auth()->user()->increment('balance_eur', $totalEur);

// Jamais modifier directement
```

### 3. WalletCrypto Creation

#### ❌ MAUVAIS
```php
// NE PAS créer plusieurs WalletCrypto pour un même (wallet, crypto):
WalletCrypto::create([...]);  // Sans vérifier l'unicité!

// Relation mal utilisée:
$wallet->cryptos()->attach($cryptoId);  // ❌ Pas de relation attach!
```

#### ✅ CORRECT
```php
// Créer OU récupérer (unique key):
$walletCrypto = WalletCrypto::firstOrCreate(
    ['wallet_id' => $wallet->id, 'cryptocurrency_id' => $cryptoId],
    ['quantity' => 0, 'avg_buy_price' => 0]
);

// Ou vérifier l'existence:
$walletCrypto = WalletCrypto::where('wallet_id', $wallet->id)
    ->where('cryptocurrency_id', $cryptoId)
    ->first();
```

### 4. Wallet Selection

#### ❌ MAUVAIS
```php
// NE PAS faire pour chaque buy/sell:
$wallet = Wallet::where('user_id', auth()->id())->first();

// ❌ NE PAS créer plusieurs wallets par user!
Wallet::create(['user_id' => auth()->id()]);  // Sans vérifier l'unicité!
```

#### ✅ CORRECT
```php
// User a exactement UN wallet (créé par Observer):
$wallet = auth()->user()->wallet;

// OU avec relation:
$wallet = auth()->user()->wallet;  // Singular, pas wallets()
```

### 5. Quantity Validation

#### ❌ MAUVAIS
```php
// À la vente, NE PAS:
if ($quantity > $walletCrypto->quantity) {
    // Ne pas continuer!
}
$walletCrypto->quantity -= $quantity;  // ❌ Peut devenir négatif!
```

#### ✅ CORRECT
```php
// Vérifier AVANT
if ($sellQuantity > $walletCrypto->quantity) {
    return response()->json(['error' => 'Insufficient quantity'], 422);
}

// Puis réduire
$walletCrypto->update([
    'quantity' => $walletCrypto->quantity - $sellQuantity
]);

// Supprimer si quantity = 0
if ($walletCrypto->quantity == 0) {
    $walletCrypto->delete();
}
```

### 6. Transaction Logging

#### ❌ MAUVAIS
```php
// NE PAS oublier de logger la transaction:
// (code du buy/sell sans création de Transaction)

// NE PAS enregistrer les mauvaises données:
Transaction::create([
    'amount' => $quantity,           // ❌ Devrait être eur_amount
    'price' => $currentPrice,        // ❌ Devrait être price_at_transaction
    'total' => $quantity             // ❌ Wrong
]);
```

#### ✅ CORRECT
```php
Transaction::create([
    'user_id' => auth()->id(),
    'cryptocurrency_id' => $cryptoId,
    'type' => 'buy',  // ou 'sell'
    'quantity' => $quantity,
    'price_at_transaction' => $currentPrice,
    'eur_amount' => $quantity * $currentPrice
]);
```

### 7. Cryptocurrency vs Crypto

#### ❌ MAUVAIS
```php
// NE PAS utiliser Crypto model (ancien):
$crypto = Crypto::find($id);  // ❌ Ancien model!

// NE PAS mixer les deux:
$crypto->priceHistories;  // ❌ Crypto n'a pas cette relation!
```

#### ✅ CORRECT
```php
// Utiliser Cryptocurrency:
$cryptocurrency = Cryptocurrency::find($id);

// Accéder aux relations:
$cryptocurrency->priceHistories;
$cryptocurrency->walletCryptos;
$cryptocurrency->transactions;
```

### 8. Response Format

#### ❌ MAUVAIS
```php
// Format inconsistent:
return response()->json([
    'success' => true,
    'wallet' => $wallet
]);

// Pas de structure cohérente:
return response()->json($wallet);  // Pas clair si succès ou erreur
```

#### ✅ CORRECT
```php
// Format consistent:
return response()->json([
    'status' => 'success',
    'message' => 'Buy operation successful',
    'data' => [
        'balance_eur' => $user->balance_eur,
        'wallet' => $wallet,
        'transaction_id' => $transaction->id
    ]
], 200);

// Erreur:
return response()->json([
    'status' => 'error',
    'message' => 'Insufficient balance',
    'data' => []
], 422);
```

### 9. Admin User Modification

#### ❌ MAUVAIS
```php
// NE PAS permettre modifier le password d'un client via admin:
$user->update($request->all());  // ❌ Si 'password' est dans $request!

// NE PAS modifier la balance_eur via admin:
$user->update(['balance_eur' => 1000]);  // ❌ Administrateur ne doit pas!
```

#### ✅ CORRECT
```php
// Admin peut modifier: name, email, is_active
$user->update($request->only('name', 'email', 'is_active'));

// Admin NE PAS modifier: password, balance_eur, role
```

### 10. Temp Password

#### ❌ MAUVAIS
```php
// NE PAS afficher le temp_password plusieurs fois:
// Envoyer par email, pas en réponse JSON!

// NE PAS oublier de l'effacer:
// Après premier changement de mot de passe
```

#### ✅ CORRECT
```php
// Générer temp_password:
$tempPassword = Str::random(10);
$user->update(['temp_password' => $tempPassword]);

// Afficher UNE SEULE fois (dans la réponse de création):
return response()->json([
    'user' => $user,
    'temp_password' => $tempPassword  // Afficher ici SEULEMENT
], 201);

// Après login avec temp_password, forcer changement:
if ($user->temp_password) {
    return response()->json([
        'error' => 'Please change your password first',
        'redirect' => '/change-password'
    ], 403);
}

// Après changement de password:
$user->update(['temp_password' => null]);  // Effacer!
```

### 11. Rate Limiting

#### ❌ MAUVAIS
```php
// NE PAS oublier le rate limiting sur login:
Route::post('login', [AuthController::class, 'login']);  // ❌ Sans throttle!
```

#### ✅ CORRECT
```php
// Ajouter rate limiting:
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
// 10 tentatives par minute
```

### 12. Unique Key dans WalletCrypto

#### ❌ MAUVAIS
```php
// Migration sans UNIQUE KEY:
$table->foreignId('wallet_id')->constrained();
$table->foreignId('cryptocurrency_id')->constrained();
// ❌ Permet les doublons!

// Ou avec mauvaise syntaxe:
$table->unique(['wallet_id', 'crypto_id']);  // ❌ crypto_id n'existe pas!
```

#### ✅ CORRECT
```php
// Bonne syntaxe UNIQUE KEY:
$table->unique(['wallet_id', 'cryptocurrency_id']);

// OU
$table->unique(['wallet_id', 'cryptocurrency_id'], 'unique_wallet_crypto');
```

---

## 🔍 CHECKLIST AVANT DE TESTER

### WalletController
- [ ] avg_buy_price calcula EXACTEMENT selon la formule?
- [ ] balance_eur débité au buy?
- [ ] balance_eur crédité au sell?
- [ ] WalletCrypto créé/updaté correctement?
- [ ] Transaction loggée?
- [ ] Validation: solde suffisant?
- [ ] Validation: quantity suffisante à la vente?

### AuthController
- [ ] Login retourne JWT token?
- [ ] Logout révoque le token?
- [ ] Profile retourne user + balance?
- [ ] UpdateProfile ne touche pas balance?
- [ ] temp_password affichage unique?

### AdminController
- [ ] getUsers retourne clients UNIQUEMENT?
- [ ] storeUser crée temp_password?
- [ ] updateUser ne touche pas password/balance?
- [ ] deleteUser supprime wallet aussi?

---

## 🧪 TEST RAPIDES EN TINKER

```bash
php artisan tinker

# Test 1: avg_buy_price calculation
>>> $wc = new \App\Models\WalletCrypto();
>>> $wc->quantity = 1; $wc->avg_buy_price = 40000;
>>> $totalInvestedBefore = 1 * 40000;  // 40000
>>> $newInvested = 0.5 * 42500;        // 21250
>>> $newTotalInvested = 40000 + 21250; // 61250
>>> $newQuantity = 1 + 0.5;            // 1.5
>>> $newAvgPrice = 61250 / 1.5;        // 40833.33
// ✅ Correct!

# Test 2: Wallet unique par user
>>> \App\Models\User::first()->wallet;
// ✅ Devrait retourner 1 wallet

# Test 3: PriceHistories
>>> \App\Models\PriceHistory::count();
// ✅ Devrait retourner 310

# Test 4: Relations
>>> $c = \App\Models\Cryptocurrency::first();
>>> $c->priceHistories->count();  // ✅ 31
>>> $c->walletCryptos->count();   // ✅ 0 (pas encore d'achat)
```

---

**LISEZ CES POINTS AVANT DE COMMENCER !**

