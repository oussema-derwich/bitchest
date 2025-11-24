# 🧪 GUIDE POUR TESTER LA STRUCTURE

## ✅ PRÊT À TESTER MAINTENANT

### 1. Migrer et Seeder la base de données

```bash
cd backend

# Nettoyer et re-migrer
php artisan migrate:fresh --seed

# Résultat attendu:
# ✓ Création des 7 tables
# ✓ Insertion de 2 utilisateurs (admin + client)
# ✓ Création de 10 cryptos
# ✓ Génération de 310 prix historiques
```

### 2. Vérifier les routes

```bash
php artisan route:list --path=api

# Résultat attendu:
# POST /api/login
# GET /api/me
# PUT /api/profile
# POST /api/logout
# GET /api/cryptocurrencies
# GET /api/cryptocurrencies/{id}
# GET /api/cryptocurrencies/{id}/history
# GET /api/wallet
# POST /api/buy
# POST /api/sell
# GET /api/transactions
# GET /api/admin/users
# POST /api/admin/users
# PUT /api/admin/users/{id}
# DELETE /api/admin/users/{id}
# GET /api/admin/stats
```

### 3. Tester dans Tinker

```bash
php artisan tinker

# Tester les modèles
>>> $users = User::all();
>>> $users->count()  // Devrait retourner: 2

>>> $cryptos = Cryptocurrency::all();
>>> $cryptos->count()  // Devrait retourner: 10

>>> $prices = PriceHistory::all();
>>> $prices->count()  // Devrait retourner: 310

>>> $client = User::where('email', 'user@bitchest.com')->first();
>>> $client->balance_eur  // Devrait retourner: 500

>>> $wallet = $client->wallet;  // Devrait retourner l'objet Wallet

>>> $wallet->walletCryptos;  // Devrait retourner une collection vide []

# Quitter tinker
>>> exit
```

### 4. Tester les relations

```bash
php artisan tinker

# Tester les relations bidirectionnelles
>>> $crypto = Cryptocurrency::first();
>>> $crypto->walletCryptos->count()  // 0 (pas encore d'achat)

>>> $crypto->priceHistories->count()  // 31 (31 jours)

>>> $user = User::where('email', 'admin@bitchest.com')->first();
>>> $user->isAdmin()  // true

>>> $user->transactions->count()  // 0 (pas encore de transaction)

# Quitter tinker
>>> exit
```

---

## ⏳ À FAIRE APRÈS TESTS STRUCTURE

### 1. Implémenter AuthController
```bash
# Tester:
POST /api/login
{
  "email": "user@bitchest.com",
  "password": "user123"
}
# Réponse attendue: { user, token, role }
```

### 2. Implémenter CryptoController
```bash
# Tester:
GET /api/cryptocurrencies
# Réponse: [10 cryptos]

GET /api/cryptocurrencies/1/history
# Réponse: [31 prix historiques]
```

### 3. Implémenter WalletController
```bash
# Tester (après login):
GET /api/wallet (avec token)
# Réponse: { id, user_id, cryptocurrencies: [] }

POST /api/buy (avec token)
{
  "cryptocurrency_id": 1,
  "quantity": 0.1,
  "price": 42500
}
# Réponse: { success, balance, wallet }
```

---

## 🎯 COMMANDES RAPIDES

```bash
# Migrer
php artisan migrate:fresh --seed

# Vérifier les erreurs
php artisan list

# Vérifier les tables
php artisan tinker
>>> Schema::getTables()

# Démarrer le serveur
php artisan serve

# Afficher les logs
php artisan pail
```

---

## 🔍 FICHIERS À VÉRIFIER

### ✅ Migrations
```
backend/database/migrations/
  ├── 0001_01_01_000000_create_users_table.php ✅
  ├── 0001_01_01_000001_create_cache_table.php ✅
  ├── 0001_01_01_000002_create_jobs_table.php ✅
  ├── 2025_10_30_093853_create_cryptos_table.php ✅
  ├── 2025_10_30_093853_create_wallets_table.php ✅
  ├── 2025_10_30_093854_create_transactions_table.php ✅
  ├── 2025_11_05_000001_create_alerts_table.php ✅
  ├── 2025_11_06_000001_add_two_factor_auth_fields.php ✅
  └── 2025_11_14_000001_create_price_histories_table.php ✅
```

### ✅ Modèles
```
backend/app/Models/
  ├── User.php ✅ (HasApiTokens, balance_eur, temp_password)
  ├── Wallet.php ✅ (hasOne User, hasMany WalletCrypto)
  ├── WalletCrypto.php ✅ (NEW - many-to-many)
  ├── Transaction.php ✅ (belongsTo User, Cryptocurrency)
  ├── Cryptocurrency.php ✅ (NEW - remplace Crypto)
  ├── PriceHistory.php ✅ (NEW - belongs Cryptocurrency)
  └── Alert.php ✅ (updated: belongsTo Cryptocurrency)
```

### ✅ Seeders
```
backend/database/seeders/
  ├── DatabaseSeeder.php ✅ (appelle CryptoSeeder)
  └── CryptoSeeder.php ✅ (10 cryptos + 310 prix)
```

### ✅ Configuration
```
backend/
  ├── composer.json ✅ (laravel/sanctum ^4.0)
  ├── .env ✅ (SANCTUM_STATEFUL_DOMAINS)
  └── config/
      ├── cors.php ✅ (supports_credentials=true)
      └── sanctum.php ✅ (NEW)
```

---

## 📊 RÉSULTAT ATTENDU APRÈS migrate:fresh --seed

```
Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table (20ms)

Migrating: 0001_01_01_000001_create_cache_table
Migrated:  0001_01_01_000001_create_cache_table (10ms)

Migrating: 2025_10_30_093853_create_cryptos_table
Migrated:  2025_10_30_093853_create_cryptos_table (15ms)

Migrating: 2025_10_30_093853_create_wallets_table
Migrated:  2025_10_30_093853_create_wallets_table (20ms)

Migrating: 2025_10_30_093854_create_transactions_table
Migrated:  2025_10_30_093854_create_transactions_table (18ms)

Migrating: 2025_11_14_000001_create_price_histories_table
Migrated:  2025_11_14_000001_create_price_histories_table (25ms)

Database seeding completed successfully.

Seeded: DatabaseSeeder
Seeded: CryptoSeeder

✓ 2 users créés
✓ 10 cryptos créés
✓ 310 price_histories créés
✓ 2 wallets créés
```

---

## 🚀 PROCHAINE ÉTAPE

Quand tout est prêt:

```bash
# Vérifier les migrations
php artisan migrate:fresh --seed

# Si OK → commencer développement contrôleurs
# Voir: GUIDE_IMPLEMENTATION_CONTROLEURS.md
```

**Vous pouvez maintenant copier ce projet et commencer à développer les contrôleurs !**

