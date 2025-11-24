# 📝 PENSE-BÊTE DÉVELOPPEUR

Garde ce fichier sous les yeux pendant que tu codes les contrôleurs!

---

## ⭐ TOP 3 POINTS CRITIQUES

### 1. avg_buy_price Formula
```php
$newAvgPrice = ($oldQty * $oldPrice + $newQty * $newPrice) / ($oldQty + $newQty);
// OU avec les variables du code:
$newAvgPrice = ($walletCrypto->quantity * $walletCrypto->avg_buy_price + $quantity * $currentPrice) / ($walletCrypto->quantity + $quantity);
```

### 2. balance_eur Rules
```php
// BUY:
auth()->user()->decrement('balance_eur', $totalCost);

// SELL:
auth()->user()->increment('balance_eur', $totalEur);

// JAMAIS:
$user->balance_eur = 1000;  // ❌
$user->update(['balance_eur' => 1000]);  // ❌
```

### 3. Wallet & WalletCrypto
```php
// Get wallet (singular - unique par user)
$wallet = auth()->user()->wallet;

// Get or create WalletCrypto (unique key!)
$walletCrypto = WalletCrypto::firstOrCreate(
    ['wallet_id' => $wallet->id, 'cryptocurrency_id' => $cryptoId],
    ['quantity' => 0, 'avg_buy_price' => 0]
);
```

---

## 🔄 WORKFLOW BUY

```
1. Valider input (BuyRequest)
2. Fetch crypto
3. Calculer total cost = quantity × price
4. Check balance_eur >= total cost
5. Fetch wallet = auth()->user()->wallet
6. FirstOrCreate WalletCrypto
7. Update avg_buy_price (FORMULE!)
8. Decrement balance_eur
9. Log Transaction
10. Return success + new balance
```

## 🔄 WORKFLOW SELL

```
1. Valider input (SellRequest)
2. Fetch crypto
3. Fetch wallet = auth()->user()->wallet
4. Fetch WalletCrypto
5. Check quantity >= sell quantity
6. Calculer eur = quantity × price
7. Update WalletCrypto.quantity (réduire)
8. Delete WalletCrypto si quantity == 0
9. Increment balance_eur
10. Log Transaction
11. Return success + eur received
```

---

## 🚨 ERREURS COURANTES

| Erreur | Conséquence | Prévention |
|--------|-----------|-----------|
| avg_buy_price moyenne naïve | Calcul faux | Utiliser formule fournie |
| balance_eur modifiable | Grave faille! | Utiliser decrement/increment |
| Plusieurs WalletCrypto | Doublons | Utiliser firstOrCreate + UNIQUE KEY |
| Oublier Transaction | Pas de log | Toujours créer après buy/sell |
| Quantity négative | Bug! | Vérifier avant sell |
| Pas vérifier balance | Vente sans solde | Return erreur 422 |
| Effectuer vente, puis créditer | Inconsistant | Créditer APRÈS update DB |

---

## 📌 CHECKLIST AVANT PUSH

### AuthController
- [ ] login() retourne { user, token, role }
- [ ] logout() révoque token
- [ ] profile() retourne balance_eur
- [ ] updateProfile() ne touche pas balance_eur
- [ ] Gestion temp_password

### WalletController
- [ ] buy() crée WalletCrypto avec firstOrCreate
- [ ] buy() calcule avg_buy_price EXACTEMENT selon formule
- [ ] buy() débite balance_eur APRÈS update DB
- [ ] buy() logue Transaction avec type='buy'
- [ ] sell() crédite balance_eur
- [ ] sell() réduit quantity
- [ ] sell() delete WalletCrypto si qty=0
- [ ] sell() logue Transaction avec type='sell'
- [ ] Validations: balance/quantity

### CryptoController
- [ ] index() retourne 10 cryptos
- [ ] show() retourne crypto complète
- [ ] history() retourne 31 jours (310 prix)

### AdminController
- [ ] getUsers() retourne clients UNIQUEMENT
- [ ] storeUser() génère temp_password
- [ ] updateUser() ne touche pas password/balance
- [ ] deleteUser() supprime aussi wallet

---

## 🧪 TEST RAPIDE TINKER

```bash
php artisan tinker

# Test buy logic
>>> $user = User::where('email', 'user@bitchest.com')->first();
>>> $initialBalance = $user->balance_eur;  // 500
>>> $crypto = Cryptocurrency::first();
>>> $quantity = 0.1;
>>> $price = $crypto->current_price;
>>> $totalCost = $quantity * $price;
>>> $newBalance = $initialBalance - $totalCost;
>>> // Verify newBalance > 0
```

---

## 🎯 RELATIONS À UTILISER

```php
// User → Wallet (singular)
$wallet = auth()->user()->wallet;

// Wallet → WalletCryptos
$walletCryptos = $wallet->walletCryptos;

// WalletCrypto → Cryptocurrency
$crypto = $walletCrypto->cryptocurrency;

// Cryptocurrency → current_price
$currentPrice = $crypto->current_price;

// Cryptocurrency → priceHistories (31 entries)
$prices = $crypto->priceHistories;
```

---

## 🔑 VARIABLES DE SCHÉMA

```php
// Users
id, name, email, password, temp_password (nullable), role, balance_eur, is_active

// Cryptos
id, name, symbol, current_price

// Wallets
id, user_id (unique), created_at

// WalletCryptos (UNIQUE KEY!)
id, wallet_id, cryptocurrency_id, quantity, avg_buy_price

// Transactions
id, user_id, cryptocurrency_id, type (buy/sell), quantity, price_at_transaction, eur_amount

// PriceHistories
id, cryptocurrency_id, price, created_at
```

---

## 💾 SAVE CHECKLIST

### Avant de commiter:
- [ ] Tests auth OK?
- [ ] avg_buy_price calculé?
- [ ] balance_eur débité/crédité?
- [ ] Transactions loggées?
- [ ] WalletCryptos créés?
- [ ] Validations OK?
- [ ] Erreurs retournées?
- [ ] Format responses cohérent?

---

## 🐛 DEBUG TIPS

```php
// Débugger la formule:
dd($totalInvestedBefore, $newInvested, $newTotalInvested, $newQuantity, $newAvgPrice);

// Vérifier la balance:
dump(auth()->user()->fresh()->balance_eur);

// Voir les relations:
dd($wallet->walletCryptos->toArray());

// Vérifier les transactions:
dd(\App\Models\Transaction::latest()->first()->toArray());
```

---

## ✅ QUAND APPUYER SUR GREEN

```
POST /api/buy → balance réduit ✅
POST /api/sell → balance augmenté ✅
avg_buy_price calculé ✅
WalletCrypto créé/update ✅
Transaction loggée ✅
Erreurs retournées (400/422) ✅
Format responses OK ✅
Relations OK ✅
```

**ALORS vous êtes prêt!**

---

**À garder à portée de main! 📌**

