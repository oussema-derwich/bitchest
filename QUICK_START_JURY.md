# 🚀 GUIDE DE DÉMARRAGE RAPIDE - BitChest

**Dernier mise à jour:** 21 Novembre 2025

---

## 📋 Prérequis

- PHP 8.1+
- Node.js 16+
- npm ou yarn
- Base de données MySQL 8.0+

---

## 🏃‍♂️ Démarrage Rapide (5 minutes)

### Terminal 1: Backend (Laravel)

```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve
```

**Attendu:**
```
INFO Server running on [http://127.0.0.1:8000]
```

### Terminal 2: Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

**Attendu:**
```
Local: http://localhost:5173/
```

---

## ✅ Vérification du Démarrage

### Backend
```bash
curl http://localhost:8000/api/cryptocurrencies
```
**Doit retourner:** JSON avec liste des cryptos

### Frontend
Ouvrir dans le navigateur:
```
http://localhost:5173/
```
**Doit afficher:** Page de login

---

## 🔐 Credentials de Test

### Utilisateur Régulier
```
Email: user@example.com
Mot de passe: password
```

### Admin
```
Email: admin@example.com
Mot de passe: admin123
```

---

## 🧪 Test Rapide des Fonctionnalités Critiques

### 1. Charts (Dashboard)
```
1. Se connecter
2. Aller à /dashboard
3. Vérifier que le graphique affiche une courbe
4. Les données doivent s'afficher correctement
```

### 2. Détails Crypto
```
1. Aller à /cryptos
2. Cliquer sur Litecoin → Détails
3. Vérifier que "Litecoin" s'affiche (pas Bitcoin)
```

### 3. Modifier Alerte
```
1. Aller à /alerts-page
2. Créer une alerte
3. Cliquer sur Modifier
4. Changer le seuil
5. Cliquer Sauvegarder
```

### 4. Export PDF/Excel
```
1. Aller à /history
2. Cliquer PDF → Fichier se télécharge
3. Cliquer Excel → Fichier se télécharge
```

### 5. 2FA
```
1. Aller au profil
2. Cliquer "Activer 2FA"
3. Scanner le QR code ou copier le secret
4. Entrer un code à 6 chiffres
5. Vérifier l'activation
```

---

## 🛠️ Commandes Utiles

### Backend

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear

# Queue (si utilisé)
php artisan queue:work

# Tests
php artisan test
```

### Frontend

```bash
# Build production
npm run build

# Linter
npm run lint

# Format code
npm run format
```

---

## 📂 Structure du Projet

```
bitchest-proj/
├── backend/              # Laravel API
│   ├── app/
│   ├── routes/
│   ├── database/
│   └── storage/
├── frontend/             # Vue.js + Vite
│   ├── src/
│   ├── components/
│   ├── views/
│   └── router/
└── documents/            # Documentation
```

---

## 🔗 URLs Importantes

| Page | URL | Auth |
|------|-----|------|
| Login | http://localhost:5173/login | ❌ |
| Dashboard | http://localhost:5173/dashboard | ✅ |
| Cryptos | http://localhost:5173/cryptos | ✅ |
| Portefeuille | http://localhost:5173/wallet | ✅ |
| Alertes | http://localhost:5173/alerts-page | ✅ |
| Historique | http://localhost:5173/history | ✅ |
| Profil | http://localhost:5173/profile-page | ✅ |
| Admin | http://localhost:5173/admin/dashboard | ✅ 👑 |

---

## 🐛 Dépannage

### Port 8000 déjà utilisé
```bash
php artisan serve --port=8001
```

### Port 5173 déjà utilisé
```bash
npm run dev -- --port 5174
```

### Erreurs de migration
```bash
php artisan migrate:fresh --seed
```

### Erreurs npm
```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Cache problématique
```bash
# Backend
php artisan cache:clear
php artisan config:clear

# Frontend
Navigateur: Ctrl+Shift+Delete
```

---

## 📞 Support Rapide

| Problème | Solution |
|----------|----------|
| 401 Unauthorized | Vérifier le token JWT dans localStorage |
| 500 Server Error | Vérifier les logs: `storage/logs/laravel.log` |
| Chart vide | Vérifier que l'API retourne les données |
| Route not found | Vérifier le nom de la route en router/index.ts |
| CORS error | Vérifier config/cors.php |

---

## ✨ Prêt pour le Jury?

Avant de présenter, vérifiez:

- [ ] Backend démarre sans erreur
- [ ] Frontend démarre sans erreur
- [ ] Dashboard affiche les charts
- [ ] Détails Crypto affichent la bonne crypto
- [ ] Alertes peuvent être modifiées
- [ ] Export PDF/Excel fonctionnent
- [ ] 2FA fonctionne
- [ ] Console du navigateur est propre (pas d'erreurs)

---

**Vous êtes prêt(e)! 🎉**

Bonne chance avec votre projet BitChest!
