# 🧪 GUIDE DE VÉRIFICATION - BitChest

**Date:** 21 Novembre 2025

---

## 🚀 Comment Vérifier que Tout Fonctionne

### ÉTAPE 1: Démarrer les Serveurs

**Terminal 1 - Backend:**
```bash
cd c:\Users\dell\Desktop\bitchest-proj\backend
php artisan serve
```
✅ Vous verrez: `INFO Server running on [http://127.0.0.1:8000]`

**Terminal 2 - Frontend:**
```bash
cd c:\Users\dell\Desktop\bitchest-proj\frontend
npm run dev
```
✅ Vous verrez: `Local: http://localhost:5173/`

---

## ✅ VÉRIFICATION PAR FONCTIONNALITÉ

### 1️⃣ CHARTS - Vérifier les Graphiques

**URL:** http://localhost:5173/dashboard

**Étapes:**
1. Se connecter avec:
   - Email: `user@example.com`
   - Mot de passe: `password`

2. Une fois connecté, vous verrez le dashboard

3. **CHERCHEZ:** "Graphique Marché - Bitcoin"

4. **VÉRIFIEZ:**
   - ✅ Une courbe doit s'afficher (pas juste un canvas vide)
   - ✅ La courbe doit avoir des hauts et des bas
   - ✅ Il y a 3 buttons: 24h, 7j, 30j
   - ✅ Cliquer sur les buttons change la courbe

**Si ça ne fonctionne pas:**
- Ouvrir F12 (DevTools)
- Chercher les erreurs rouges dans Console
- Vérifier que l'API retourne les données: 
  ```
  Aller à http://localhost:8000/api/cryptocurrencies/1/history
  Doit afficher JSON avec "data": { "history": [...] }
  ```

---

### 2️⃣ DÉTAILS CRYPTO - Vérifier que Litecoin != Bitcoin

**URL:** http://localhost:5173/cryptos

**Étapes:**
1. Vous êtes déjà connecté du test précédent
2. Allez à la page `/cryptos` (Liste des Cryptomonnaies)
3. Vous verrez une table avec Bitcoin, Ethereum, Litecoin, etc.
4. **CHERCHEZ:** La ligne "Litecoin" 
5. Cliquez sur le button "Détails" pour Litecoin

**VÉRIFIEZ:**
- ✅ La page affiche "Litecoin" (pas "Bitcoin")
- ✅ Le logo change (Litecoin au lieu de Bitcoin)
- ✅ Le symbole est "LTC" (pas "BTC")
- ✅ Les chiffres changent (prix, volume, etc.)

**Si ça affiche Bitcoin:**
- Le problème est la route
- Aller à http://localhost:5173/cryptos
- Cliquer sur une autre crypto
- Vérifier l'URL: Doit être `/crypto-detail/6` (pas `/crypto/1`)

---

### 3️⃣ MODIFIER ALERTES - Vérifier le Button Modifier

**URL:** http://localhost:5173/alerts-page

**Étapes:**
1. Allez à `/alerts-page`
2. Si pas d'alertes, créez une en cliquant "+ Nouvelle Alerte"
3. **CHERCHEZ:** Un button "Modifier" pour chaque alerte
4. Cliquez sur "Modifier"

**VÉRIFIEZ:**
- ✅ Un formulaire d'édition apparaît
- ✅ Vous pouvez changer la crypto
- ✅ Vous pouvez changer le seuil
- ✅ Cliquez "Sauvegarder"
- ✅ L'alerte dans la table se met à jour

**Si le formulaire n'apparaît pas:**
- F12 → Console
- Chercher l'erreur rouge
- Vérifier que `showEditAlertForm` est bien dans le template

---

### 4️⃣ EXPORT PDF/EXCEL - Vérifier les Buttons

**URL:** http://localhost:5173/history

**Étapes:**
1. Allez à `/history` (Historique des Transactions)
2. En haut à droite, cherchez deux buttons:
   - 📥 PDF
   - 📊 Excel

3. Cliquez sur "📥 PDF"
   - ✅ Un fichier CSV se télécharge
   - ✅ Son nom est: `transactions-YYYY-MM-DD.csv`
   - ✅ Ouvrir le fichier → vous verrez les transactions

4. Cliquez sur "📊 Excel"
   - ✅ Un fichier XLS se télécharge
   - ✅ Son nom est: `transactions-YYYY-MM-DD.xls`
   - ✅ Ouvrir le fichier → vous verrez les transactions dans Excel

**Si rien ne se télécharge:**
- F12 → Network
- Cliquer sur PDF
- Chercher une ligne "transactions-..." 
- Doit être en vert (200 OK)

---

### 5️⃣ 2FA - Vérifier l'Authentification à Deux Facteurs

**URL:** http://localhost:5173/profile-page

**Étapes:**
1. Allez à `/profile-page`
2. Scroller jusqu'à "Double authentification (2FA)"
3. Cliquez "Activer 2FA"

**VÉRIFIEZ Étape 1:**
- ✅ Un modal apparaît
- ✅ Un QR code s'affiche
- ✅ Un code secret s'affiche en dessous

**VÉRIFIEZ Étape 2 (Si vous avez Google Authenticator):**
- ✅ Ouvrir Google Authenticator
- ✅ Cliquer "+"
- ✅ Scanner le QR code
- ✅ Un code 6 chiffres apparaît
- ✅ Copier ce code
- ✅ Revenir à BitChest
- ✅ Coller le code dans le champ "Code de vérification"
- ✅ Cliquer "Vérifier"
- ✅ Message "Success!" ou similaire
- ✅ Le 2FA est maintenant activé

**VÉRIFIEZ Étape 3 (Désactivation):**
- ✅ Un button "Désactiver 2FA" apparaît
- ✅ Cliquer dessus
- ✅ Le 2FA se désactive

**Si le QR code ne s'affiche pas:**
- F12 → Network
- Chercher `/auth/2fa/enable`
- Vérifier la réponse: Doit contenir `"qr_code"` et `"secret"`

---

## 🔍 Checklist Rapide

Copiez-collez et cochez:

```
✅ CHARTS
  [ ] Dashboard chart affiche une courbe
  [ ] Buttons 24h/7j/30j changent les données
  [ ] Pas d'erreurs dans la console

✅ CRYPTOS
  [ ] Cliquer Litecoin → Affiche Litecoin (pas Bitcoin)
  [ ] Le logo change
  [ ] Le prix change

✅ ALERTES
  [ ] Button Modifier existe
  [ ] Cliquer Modifier → Formulaire s'affiche
  [ ] Pouvoir changer la crypto
  [ ] Pouvoir changer le seuil
  [ ] Sauvegarder → L'alerte se met à jour

✅ EXPORTS
  [ ] Button PDF télécharge un fichier
  [ ] Button Excel télécharge un fichier
  [ ] Les fichiers contiennent les bonnes données

✅ 2FA
  [ ] Button "Activer 2FA" fonctionne
  [ ] QR code s'affiche
  [ ] Code secret s'affiche
  [ ] Pouvoir entrer un code 6 chiffres
  [ ] Vérification fonctionne
  [ ] Désactivation fonctionne
```

---

## 🛠️ Dépannage Rapide

| Symptôme | Solution |
|----------|----------|
| Chart vide | Vérifier http://localhost:8000/api/cryptocurrencies/1/history |
| Mauvaise crypto | Vérifier l'URL: doit être `/crypto-detail/[ID]` |
| Button Modifier absent | F12 → Console → Chercher erreur |
| Pas de téléchargement | F12 → Network → Vérifier la requête |
| 2FA ne marche pas | Vérifier http://localhost:8000/api/auth/2fa/enable |

---

## 📊 Résultats Attendus

Vous devriez voir:

| Fonctionnalité | Avant | Après |
|---|---|---|
| Charts | ❌ Vide | ✅ Courbe visible |
| Litecoin | ❌ Affiche Bitcoin | ✅ Affiche Litecoin |
| Modifier | ❌ Ne fait rien | ✅ Ouvre formulaire |
| Export | ❌ Alert "À implémenter" | ✅ Télécharge fichier |
| 2FA | ✅ Fonctionnait | ✅ Toujours OK |

---

## 🎯 Résultat Final

Si tous les ✅ passent au vert → **VOUS ÊTES PRÊT POUR LE JURY! 🚀**

---

## 📞 Questions Fréquentes

**Q: Le chart s'affiche mais il n'y a pas de courbe?**
A: L'API peut ne pas avoir de données. Vérifier: http://localhost:8000/api/cryptocurrencies/1/history

**Q: Litecoin affiche toujours Bitcoin?**
A: Vérifier l'URL. Doit être `/crypto-detail/6` pas `/crypto/1`

**Q: Le button Modifier n'apparaît pas?**
A: Rafraîchir la page (Ctrl+F5). S'il y a une erreur, F12 → Console.

**Q: L'export PDF ne se télécharge pas?**
A: Vérifier les paramètres de téléchargement du navigateur. Ou F12 → Network.

**Q: 2FA ne marche pas du tout?**
A: Vérifier que le backend retourne un QR code: F12 → Network → /auth/2fa/enable

---

**Bonne vérification! ✅**
