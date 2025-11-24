# ✅ Checklist de Test - BitChest

## Avant de Présenter au Jury

Cette checklist vous aide à vérifier que tous les points critiques fonctionnent.

---

## 🚀 DÉMARRAGE DE L'APPLICATION

### [ ] Backend en cours d'exécution
```bash
cd backend
php artisan serve
```
✅ Vérifier: http://localhost:8000 (page Laravel)

### [ ] Frontend en cours d'exécution
```bash
cd frontend
npm run dev
```
✅ Vérifier: http://localhost:5173 (app Vue)

### [ ] Ouvrir les DevTools
- Appuyer sur F12
- Aller dans Network tab
- Checker la Console pour les erreurs

---

## 📝 TEST 1: AUTHENTIFICATION

### [ ] Inscription (Signup)
1. Aller sur http://localhost:5173
2. Cliquer "S'inscrire" (ou Register)
3. Remplir le formulaire:
   - Nom: "Test User"
   - Email: "test@example.com"
   - Mot de passe: "password123"
   - Confirmé: "password123"
4. Cliquer "Créer un compte"

**✅ Points de vérification:**
- [ ] Pas d'erreurs dans la console
- [ ] Page redirige vers login après signup
- [ ] En DevTools → Network, voir la requête POST /api/auth/register
- [ ] Réponse: status 201 ou 200

### [ ] Login
1. Sur la page de login
2. Entrer:
   - Email: "test@example.com"
   - Password: "password123"
3. Cliquer "Connexion"

**✅ Points de vérification:**
- [ ] Pas d'erreurs
- [ ] Reçoit un token JWT dans la réponse
- [ ] Token stocké dans localStorage
- [ ] Redirige vers Dashboard
- [ ] En DevTools → Network, voir POST /api/auth/login

### [ ] Profile
1. Cliquer sur le compte/profile icon
2. Voir les infos utilisateur

**✅ Points de vérification:**
- [ ] Récupère les données de profil
- [ ] Voir le request en Network → Authorization: Bearer [token]

---

## 💰 TEST 2: MARKET & TRADING

### [ ] Consulter le Market
1. Aller dans "Market"
2. Voir la liste des cryptos

**✅ Points de vérification:**
- [ ] Au moins 3-4 cryptos affichées
- [ ] Chaque crypto a: nom, symbole, prix, changement %
- [ ] GET /api/cryptos dans Network
- [ ] Pas d'erreurs API

### [ ] Détails d'une Crypto
1. Cliquer sur une crypto
2. Voir la page de détail

**✅ Points de vérification:**
- [ ] Graphique du prix (derniers 30 jours)
- [ ] Détails (nom, symbole, prix courant)
- [ ] Bouton "Acheter"
- [ ] GET /api/cryptos/{id} dans Network

### [ ] Acheter une Crypto (BUY)
1. Sur une page de détail, cliquer "Acheter"
2. Entrer une quantité (exemple: 0.5)
3. Voir le prix total
4. Cliquer "Confirmer"

**✅ Points de vérification:**
- [ ] Calcul correct du prix total (quantité × prix)
- [ ] POST /api/wallet/buy dans Network
- [ ] Réponse: transaction créée
- [ ] Message "Achat confirmé"
- [ ] Pas d'erreurs

### [ ] Consulter le Wallet
1. Aller dans "Portefeuille" ou "Wallet"
2. Voir ses holdings

**✅ Points de vérification:**
- [ ] Voir la crypto achetée
- [ ] Quantité correcte
- [ ] Valeur calculée (quantité × prix)
- [ ] GET /api/wallet dans Network
- [ ] Graphique de distribution (Donut chart)

### [ ] Historique Transactions
1. Aller dans "Transactions"
2. Voir l'historique

**✅ Points de vérification:**
- [ ] Voir l'achat précédent
- [ ] Type: "Buy"
- [ ] Quantité et prix corrects
- [ ] Date/heure
- [ ] GET /api/transactions dans Network

### [ ] Vendre une Crypto (SELL)
1. Aller dans "Portefeuille"
2. Cliquer "Vendre" sur une crypto
3. Entrer une quantité (ex: 0.2)
4. Cliquer "Confirmer"

**✅ Points de vérification:**
- [ ] POST /api/wallet/sell dans Network
- [ ] Transaction enregistrée
- [ ] Quantité mise à jour dans le wallet
- [ ] Message de succès
- [ ] Pas d'erreur

---

## 🔔 TEST 3: ALERTES

### [ ] Créer une Alerte
1. Aller dans "Alertes"
2. Cliquer "Nouvelle alerte"
3. Sélectionner une crypto
4. Choisir le type: "Si le prix monte au-dessus de..."
5. Entrer un prix (ex: 50000)
6. Cliquer "Créer"

**✅ Points de vérification:**
- [ ] POST /api/alerts dans Network
- [ ] Alerte créée avec succès
- [ ] Alerte visible dans la liste
- [ ] Message de confirmation

### [ ] Voir les Alertes
1. Aller dans "Alertes"
2. Voir la liste

**✅ Points de vérification:**
- [ ] GET /api/alerts dans Network
- [ ] Alerte créée visible
- [ ] Status: active/inactive
- [ ] Options: modifier, supprimer

### [ ] Modifier une Alerte
1. Cliquer "Modifier" sur une alerte
2. Changer le prix (ex: 55000)
3. Cliquer "Sauvegarder"

**✅ Points de vérification:**
- [ ] PUT /api/alerts/{id} dans Network
- [ ] Alerte mise à jour
- [ ] Nouveau prix affiché

### [ ] Supprimer une Alerte
1. Cliquer "Supprimer" sur une alerte
2. Confirmer

**✅ Points de vérification:**
- [ ] DELETE /api/alerts/{id} dans Network
- [ ] Alerte supprimée
- [ ] Plus dans la liste

---

## 🔐 TEST 4: AUTHENTIFICATION 2FA

### [ ] Activer 2FA
1. Aller dans "Profile" ou "Settings"
2. Cliquer "Activer 2FA"
3. Voir le QR Code

**✅ Points de vérification:**
- [ ] QR Code affiché
- [ ] Code manuel visible si QR ne scanne pas
- [ ] POST /api/auth/2fa/enable dans Network

### [ ] Scanner le QR Code
1. Utiliser Google Authenticator ou Authy
2. Scanner le QR Code
3. Ajouter le compte

**✅ Points de vérification:**
- [ ] Compte ajouté dans l'app authenticator
- [ ] Code 6 chiffres généré
- [ ] Code change toutes les 30 secondes

### [ ] Confirmer 2FA
1. Dans BitChest, entrer le code 6 chiffres
2. Cliquer "Vérifier"

**✅ Points de vérification:**
- [ ] POST /api/auth/2fa/confirm dans Network
- [ ] Message "2FA activé avec succès"
- [ ] Page Profile montre "2FA: Activé"

### [ ] Logout et Test 2FA
1. Se déconnecter
2. Se reconnecter avec le même compte
3. Voir le popup "Entrer le code 2FA"

**✅ Points de vérification:**
- [ ] Formulaire 2FA s'affiche
- [ ] Code d'authenticator demandé
- [ ] POST /api/auth/2fa/verify dans Network

### [ ] Désactiver 2FA
1. Aller dans Profile
2. Cliquer "Désactiver 2FA"
3. Entrer le mot de passe
4. Confirmer

**✅ Points de vérification:**
- [ ] POST /api/auth/2fa/disable dans Network
- [ ] 2FA désactivé
- [ ] Prochain login ne demande pas le code

---

## 👨‍💼 TEST 5: ADMIN PANEL (Si Compte Admin)

### [ ] Créer un Compte Admin
```bash
cd backend
php artisan tinker

$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('admin123');
$user->role = 'admin';
$user->is_active = true;
$user->save();

# Quitter avec Ctrl+C ou exit()
```

### [ ] Accéder au Dashboard Admin
1. Logout du compte test
2. Login avec admin@example.com / admin123
3. Voir le Admin Dashboard

**✅ Points de vérification:**
- [ ] URL: http://localhost:5173/admin
- [ ] Statistiques affichées (total users, transactions, etc)
- [ ] Pas d'erreurs

### [ ] Gestion des Utilisateurs
1. Aller dans "Admin → Users"
2. Voir la liste des utilisateurs

**✅ Points de vérification:**
- [ ] GET /api/admin/users dans Network
- [ ] Tous les utilisateurs listés
- [ ] Actions disponibles (approve, suspend, delete)

### [ ] Gestion des Cryptos
1. Aller dans "Admin → Cryptos"
2. Voir la liste

**✅ Points de vérification:**
- [ ] GET /api/admin/cryptos dans Network
- [ ] Cryptos listées
- [ ] Boutons edit/delete

### [ ] Historique Transactions (Admin)
1. Aller dans "Admin → Transactions"
2. Voir toutes les transactions

**✅ Points de vérification:**
- [ ] GET /api/admin/transactions dans Network
- [ ] Toutes les transactions visibles
- [ ] User info pour chaque transaction

---

## 🐛 TEST 6: GESTION D'ERREURS

### [ ] Erreur: Email Déjà Enregistré
1. Essayer de s'inscrire avec un email existant

**✅ Points de vérification:**
- [ ] Message d'erreur: "Email already exists"
- [ ] Status 422 (validation error)
- [ ] Formulaire reste sur la page

### [ ] Erreur: Mot de Passe Incorrect
1. Essayer de login avec mauvais mot de passe

**✅ Points de vérification:**
- [ ] Message d'erreur: "Invalid credentials"
- [ ] Status 401
- [ ] Pas de redirection

### [ ] Erreur: Token Expiré
1. Attendre 1h (ou forcer dans DevTools)
2. Faire une requête API

**✅ Points de vérification:**
- [ ] Status 401 (Unauthorized)
- [ ] Message: "Token expired"
- [ ] Possibilité de refresh ou login

### [ ] Erreur: Solde Insuffisant
1. Essayer d'acheter plus que le solde

**✅ Points de vérification:**
- [ ] Message: "Insufficient balance"
- [ ] Transaction non créée
- [ ] Wallet inchangé

---

## 🎨 TEST 7: INTERFACE & RESPONSIVE

### [ ] Navigabilité
- [ ] Tous les liens fonctionnent
- [ ] Navigation fluide entre les pages
- [ ] Pas de 404 errors

### [ ] Responsive Design
1. Ouvrir DevTools → Toggle device toolbar (Ctrl+Shift+M)
2. Tester sur Mobile (375px), Tablet (768px), Desktop (1920px)

**✅ Points de vérification:**
- [ ] Layout s'adapte sur mobile
- [ ] Texte lisible sur tous les écrans
- [ ] Buttons cliquables sur mobile
- [ ] Pas de scroll horizontal non-intentionnel

### [ ] Graphiques
1. Sur Market ou Wallet, voir les graphiques

**✅ Points de vérification:**
- [ ] Graphiques s'affichent
- [ ] Pas d'erreurs dans console
- [ ] Chart.js chargé

---

## 🔄 TEST 8: PERFORMANCE

### [ ] Temps de Chargement
1. Aller sur Network tab
2. Rafraîchir la page (Ctrl+R)

**✅ Points de vérification:**
- [ ] Page charge en < 3 secondes
- [ ] Network requests: < 50
- [ ] Total size: < 2MB

### [ ] Caching
1. Charger une page une fois
2. Charger à nouveau

**✅ Points de vérification:**
- [ ] 2e fois plus rapide (cache)
- [ ] Statuts 304 Not Modified

---

## 📊 RÉSUMÉ DES TESTS

Cochez chaque section quand elle est complétée:

- [ ] Authentification
- [ ] Market & Trading
- [ ] Alertes
- [ ] 2FA
- [ ] Admin Panel
- [ ] Gestion d'erreurs
- [ ] Interface & Responsive
- [ ] Performance

---

## ✅ PRÊT POUR LA PRÉSENTATION?

Si tous les tests ci-dessus passent ✅, alors **VOUS ÊTES PRÊT!**

### Avant de Présenter:

1. [ ] Prendre des captures d'écran de chaque étape
2. [ ] Noter les URLs pour la démo
3. [ ] Vérifier que les 2 serveurs tournent
4. [ ] Tester une fois de plus le flow complet
5. [ ] Vérifier la connexion internet (si présentation en ligne)

---

## 📸 Captures à Prendre

Pour chaque point ci-dessous, prendre une capture d'écran:

- [ ] Page d'accueil (Home)
- [ ] Signup form
- [ ] Login form
- [ ] Dashboard (après login)
- [ ] Market (liste des cryptos)
- [ ] Détails Crypto (avec graphique)
- [ ] Buy Form
- [ ] Wallet/Holdings
- [ ] Transactions History
- [ ] Alertes
- [ ] Profile/2FA Setup
- [ ] Admin Dashboard (si admin)
- [ ] User Management (si admin)

---

**🎉 Bonne présentation! Vous êtes bien préparé(e)! 🚀**
