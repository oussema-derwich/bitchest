# 🚀 Guide de Démarrage Rapide - BitChest

## Prérequis
- PHP 8.2+
- Node.js 16+
- npm ou yarn

## ⚡ Démarrage en 3 Commandes

### Terminal 1 - Backend (Laravel)
```bash
cd backend
php artisan serve
```
✅ Le serveur s'exécute sur `http://localhost:8000`

### Terminal 2 - Frontend (Vue 3 + Vite)
```bash
cd frontend
npm run dev
```
✅ L'app s'exécute sur `http://localhost:5173`

### Terminal 3 (Optionnel) - Queue Worker
```bash
cd backend
php artisan queue:listen
```
✅ Pour traiter les jobs en arrière-plan

---

## 🔐 Authentification

### Créer un Compte
1. Aller sur http://localhost:5173
2. Cliquer sur "S'inscrire"
3. Remplir les champs (email, mot de passe x2)
4. Cliquer sur "Créer un compte"

### Identifiants de Test (À Créer)
```
Email: test@example.com
Password: password123
```

---

## 🔑 Admin Account (Créer avec Artisan Tinker)

```bash
cd backend
php artisan tinker

# Dans le terminal tinker:
$user = new App\Models\User();
$user->name = 'Admin User';
$user->email = 'admin@example.com';
$user->password = bcrypt('admin123');
$user->role = 'admin';
$user->is_active = true;
$user->save();

# Quitter tinker (Ctrl+C ou exit)
```

---

## 📊 Points Clés à Tester

### ✅ Authentification
- [ ] Inscription (Register)
- [ ] Connexion (Login)
- [ ] Profil utilisateur

### ✅ 2FA (Two-Factor Auth)
- [ ] Activer 2FA dans les paramètres
- [ ] Scanner le QR Code avec Google Authenticator
- [ ] Vérifier le code 6 chiffres

### ✅ Trading
- [ ] Consulter le Market (liste des cryptos)
- [ ] Voir les détails d'une crypto
- [ ] Acheter une crypto
- [ ] Vendre une crypto
- [ ] Voir l'historique des transactions

### ✅ Portefeuille
- [ ] Voir son portefeuille
- [ ] Graphique de valeur du portefeuille
- [ ] Distribution des actifs

### ✅ Alertes
- [ ] Créer une alerte de prix
- [ ] Modifier une alerte
- [ ] Supprimer une alerte

### ✅ Admin Panel (Si admin)
- [ ] Dashboard (statistiques)
- [ ] Gestion des utilisateurs
- [ ] Gestion des cryptos
- [ ] Historique des transactions
- [ ] Alertes

---

## 🛠️ Commandes Utiles

### Backend (Laravel)

```bash
# Base de données
php artisan migrate              # Exécuter les migrations
php artisan migrate:refresh      # Reset la DB
php artisan migrate:reset        # Annuler les migrations
php artisan seed                 # Seeder les données

# Artisan
php artisan tinker              # Shell interactif
php artisan test                # Exécuter les tests
php artisan list                # Lister les commandes

# Cache
php artisan cache:clear         # Vider le cache
php artisan config:clear        # Vider la config
```

### Frontend (Vue)

```bash
# Installation
npm install                     # Installer les dépendances
npm update                      # Mettre à jour les dépendances

# Build
npm run dev                      # Dev server
npm run build                    # Build production
npm run preview                  # Preview du build
```

---

## 🔗 Endpoints API Principaux

### Public
```
POST   /api/auth/register        - Inscription
POST   /api/auth/login           - Connexion
GET    /api/cryptos              - Liste des cryptos
GET    /api/cryptos/:id          - Détail crypto
```

### Protected (Authentification Required)
```
POST   /api/auth/logout          - Déconnexion
GET    /api/auth/profile         - Profil utilisateur
POST   /api/auth/refresh         - Refresh token

POST   /api/auth/2fa/enable      - Activer 2FA
POST   /api/auth/2fa/confirm     - Confirmer 2FA
POST   /api/auth/2fa/verify      - Vérifier 2FA
POST   /api/auth/2fa/disable     - Désactiver 2FA

GET    /api/wallet               - Portefeuille
POST   /api/wallet/buy           - Acheter
POST   /api/wallet/sell          - Vendre
GET    /api/transactions         - Transactions
GET    /api/alerts               - Alertes
POST   /api/alerts               - Créer alerte
```

### Admin (Admin Role Required)
```
GET    /api/admin/dashboard      - Dashboard
GET    /api/admin/users          - Utilisateurs
GET    /api/admin/cryptos        - Cryptos
GET    /api/admin/transactions   - Transactions
GET    /api/admin/alerts         - Alertes
```

---

## 🐛 Troubleshooting

### Backend ne démarre pas
```bash
# Générer la clé APP
php artisan key:generate

# Réinitialiser les migrations
php artisan migrate:reset
php artisan migrate
```

### Frontend ne démarre pas
```bash
# Supprimer node_modules et réinstaller
rm -r node_modules
npm install

# Supprimer le cache Vite
rm -r .vite
npm run dev
```

### Erreur de connexion API
- Vérifier que le backend s'exécute sur http://localhost:8000
- Vérifier la configuration CORS dans `config/cors.php`
- Vérifier que le `.env` a `APP_URL=http://localhost:8000`

### Erreur 401 (Unauthorized)
- Vérifier le token JWT dans le localStorage
- Se reconnecter
- Vérifier que le token n'est pas expiré

---

## 📝 Fichiers de Configuration Importants

| Fichier | Localisation | Description |
|---------|-------------|-------------|
| `.env` | `backend/` | Configuration Laravel |
| `package.json` | `frontend/` | Dépendances npm |
| `composer.json` | `backend/` | Dépendances PHP |
| `vite.config.ts` | `frontend/` | Configuration Vite |
| `routes/api.php` | `backend/` | Définition des routes API |
| `app/Http/Controllers` | `backend/` | Contrôleurs |
| `src/router/index.ts` | `frontend/` | Configuration routage |
| `src/services/api.ts` | `frontend/` | Client HTTP Axios |

---

## 🎯 Pour la Présentation au Jury

### Scénario Recommandé (15-20 min)
1. **Accueil** - Montrer la page d'accueil
2. **Inscription** - Créer un compte de test
3. **Connexion** - Se connecter
4. **Market** - Montrer les cryptos disponibles
5. **Achat** - Acheter une crypto (Buy transaction)
6. **Portefeuille** - Montrer le portefeuille avec les holdings
7. **Vente** - Vendre une portion (Sell transaction)
8. **2FA** - Configurer l'authentification 2FA
9. **Admin** (si applicable) - Montrer le dashboard admin
10. **Questions** - Répondre aux questions du jury

### Points Forts à Mettre en Avant
✅ Authentification sécurisée avec JWT  
✅ 2FA avec TOTP (Google Authenticator)  
✅ Interface responsive et moderne  
✅ Real-time features (WebSocket)  
✅ Admin panel complet  
✅ Gestion des transactions  
✅ Architecture bien structurée  

---

## 💡 Tips Supplémentaires

- Utiliser les DevTools du navigateur (F12) pour voir les appels API
- Consulter les logs Laravel: `storage/logs/laravel.log`
- Pour déboguer, ajouter `dd()` dans le code PHP
- Utiliser Postman pour tester directement les endpoints API

---

**Bonne présentation! 🎉**
