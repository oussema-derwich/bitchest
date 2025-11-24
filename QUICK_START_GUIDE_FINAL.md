# 🚀 BITCHEST - QUICK START GUIDE

**Your cryptocurrency trading platform is READY TO USE!**

---

## ▶️ TO RUN THE PROJECT

### 1️⃣ Start Backend Server
```powershell
cd c:\Users\dell\Desktop\bitchest-proj\backend
php artisan serve --port=8000
```
✅ You should see: `Server running on [http://127.0.0.1:8000]`

### 2️⃣ Start Frontend Server (New Terminal)
```powershell
cd c:\Users\dell\Desktop\bitchest-proj\frontend
npx vite --port 5174
```
✅ You should see: `Local: http://localhost:5174/`

### 3️⃣ Open in Browser
```
http://localhost:5174
```

---

## 🔐 TEST ACCOUNT (or Register New)

### Option A: Register New User
1. Click "S'inscrire" (Register)
2. Enter email, name, password
3. Click "Créer un compte"
4. Login with credentials

### Option B: Use Test Data
- **Email:** test@bitchest.com
- **Password:** Test123456!

---

## 🎯 FEATURES TO TEST

### 🏠 Home Page
```
localhost:5174
- View about BitChest
- See market overview
- Click "S'inscrire" or "Connexion"
```

### 📊 Dashboard (After Login)
```
localhost:5174/dashboard
✓ See 4 stat cards (Solde, Valeur Portefeuille, Gains, Cryptos)
✓ View portfolio chart
✓ Quick action buttons: Acheter, Vendre, Alertes
```

### 💰 Cryptocurrency List
```
localhost:5174/cryptos
✓ See all cryptocurrencies with LOGOS
✓ Bitcoin (with PROFESSIONAL LOGO ⭐)
✓ Ethereum, Cardano, Solana, Ripple, Litecoin
✓ Click "Détails" to see individual crypto
✓ Use search to find crypto
✓ Click column headers to sort
```

### 🔍 Crypto Detail Page (with Professional Logo) ⭐
```
localhost:5174/crypto-detail/1
✓ Bitcoin with PROFESSIONAL LOGO IMAGE
✓ Current price: 82,250 DT
✓ Variation chart (7j, 30j, 90j)
✓ Market info cards
✓ 3 Action Buttons:
  - ✓ Acheter (Buy)
  - ✕ Vendre (Sell)
  - 🔔 Alerte (Alert)
```

### 🛒 Buy Cryptocurrency
```
localhost:5174/buy
✓ Select crypto (Bitcoin, Ethereum, Cardano)
✓ Enter quantity (0.001)
✓ Auto-calculates total
✓ Shows fees (0.5%)
✓ Click "Confirmer" to buy
✓ ✅ NOTIFICATION CREATED (new feature!)
```

### 📤 Sell Cryptocurrency
```
localhost:5174/sell
✓ Select crypto you own
✓ Enter quantity to sell
✓ See revenue calculation
✓ Click "Confirmer" to sell
✓ ✅ NOTIFICATION CREATED
✓ ⚠️ Low balance alert if < 100€
```

### 💼 Portfolio/Wallet
```
localhost:5174/wallet
✓ See all your holdings
✓ Logos for each crypto
✓ Total value, Gains/Losses
✓ Portfolio distribution chart
✓ Table with positions
```

### 📋 Transactions History
```
localhost:5174/history
✓ See all buy/sell transactions
✓ Filter by type
✓ Search transactions
✓ Detailed info per transaction
```

### 🔔 Notifications (NEW!) ⭐
```
localhost:5174/notifications
✓ Click bell icon in navbar
✓ See all notifications
✓ Notification types:
  - Buy: "Achat réussi"
  - Sell: "Vente réussie"
  - Low Balance: "Solde faible"
✓ Mark as read
✓ Delete notifications
```

### 🎯 Alerts
```
localhost:5174/alerts
✓ Create price alert
✓ Select crypto (Bitcoin, Ethereum, etc.)
✓ Set condition (Au-dessus, Au-dessous)
✓ Set price target
✓ Get notified when price reached
```

### 👤 Profile
```
localhost:5174/profile-page
✓ View your information
✓ Change password
✓ View balance
```

### 👨‍💼 Admin Panel
```
localhost:5174/admin/login
✓ Email: admin@bitchest.com
✓ Password: Admin123456!

Then access:
- /admin/dashboard - Stats overview
- /admin/users - Manage users
- /admin/cryptos - Manage cryptocurrencies
- /admin/transactions - Monitor transactions
```

---

## 🌟 KEY FEATURES IMPLEMENTED

### ✅ Authentication
- Register new account
- Login with email/password
- JWT token management
- Session persistence
- Logout

### ✅ Transactions
- Buy cryptocurrency (instant)
- Sell cryptocurrency (instant)
- Automatic fee calculation (0.5%)
- Balance validation
- Transaction history

### ✅ Notifications (NEW!) ⭐
- Auto-created on buy/sell
- Low balance alerts (< 100€)
- Notification center page
- Mark as read/unread
- Delete notifications
- Badge count in navbar

### ✅ Portfolio Management
- See all holdings
- Track gains/losses
- View portfolio value
- Crypto distribution chart
- Individual position details

### ✅ Market Data
- Real-time prices
- 24h variations
- Volume data
- Market cap
- Price charts
- Historical data

### ✅ Professional UI
- Clean, modern design
- Responsive layout
- Professional LOGOS for cryptos
- Color-coded (green=gain, red=loss)
- Intuitive navigation
- Fast load times

---

## 🐛 IF SOMETHING DOESN'T WORK

### Backend Not Responding?
```powershell
# Check if Laravel is installed
cd backend
composer install

# Check migrations are run
php artisan migrate

# Start server
php artisan serve --port=8000
```

### Frontend Not Loading?
```powershell
# Check Node.js packages
cd frontend
npm install

# Start Vite
npx vite --port 5174
```

### Port Already in Use?
```powershell
# Kill process on port 8000
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Or use different port
php artisan serve --port=8001
```

### Logo Not Showing?
✓ Check file exists: `frontend/public/assets/bitcoin.png`
✓ Check URL path correct in CryptoLogo.vue
✓ Browser cache might be issue - hard refresh (Ctrl+Shift+Del)

---

## 📱 IMPORTANT LINKS

| Feature | URL |
|---------|-----|
| Home | http://localhost:5174 |
| Dashboard | http://localhost:5174/dashboard |
| Crypto List | http://localhost:5174/cryptos |
| Crypto Detail ⭐ | http://localhost:5174/crypto-detail/1 |
| Buy | http://localhost:5174/buy |
| Sell | http://localhost:5174/sell |
| Wallet | http://localhost:5174/wallet |
| Transactions | http://localhost:5174/history |
| Notifications | http://localhost:5174/notifications |
| Alerts | http://localhost:5174/alerts |
| Profile | http://localhost:5174/profile-page |
| Admin Login | http://localhost:5174/admin/login |
| Admin Dashboard | http://localhost:5174/admin/dashboard |
| Backend API | http://localhost:8000/api |

---

## ✨ HIGHLIGHT FEATURES

### 🌟 Professional Logos
- Real cryptocurrency logos (not emojis!)
- Bitcoin with professional image on detail page
- All 6 cryptos have logo display
- Responsive logo sizing

### 📢 Notifications System
- Automatic notification on every buy/sell
- Low balance alert alert system (< 100€)
- Notification count badge in navbar
- Mark as read functionality
- Delete old notifications

### 📊 Analytics
- Portfolio value tracking
- Gain/loss percentage
- 24h variations
- Volume statistics
- Distribution charts

### 💪 Admin Power
- Manage all users
- Control cryptocurrency list
- Monitor all transactions
- System settings

---

## 🎓 USAGE EXAMPLE

### Step by Step: Buy Bitcoin

1. **Login**
   ```
   Go: http://localhost:5174
   Email: test@bitchest.com
   Password: Test123456!
   ```

2. **View Crypto Detail**
   ```
   Go: http://localhost:5174/crypto-detail/1
   See: Bitcoin with PROFESSIONAL LOGO ⭐
   ```

3. **Click Buy Button**
   ```
   Button: ✓ Acheter (green button)
   Go: http://localhost:5174/buy
   ```

4. **Complete Purchase**
   ```
   Crypto: Bitcoin (pre-selected)
   Quantity: 0.1
   Total: 8,225 DT + 41.13 DT frais = 8,266.13 DT
   Click: ✓ Confirmer
   ```

5. **See Notification** ⭐
   ```
   Go: http://localhost:5174/notifications
   See: "Achat réussi - BTC"
   Message: "Vous avez acheté 0.1 BTC à 82250€ chacun..."
   ```

6. **Check Portfolio**
   ```
   Go: http://localhost:5174/wallet
   See: 0.1 BTC holding with Bitcoin LOGO
   Value: 8,225 DT
   ```

---

## 💡 TIPS & TRICKS

- **🔍 Search cryptos** by name or symbol on crypto list
- **📊 Sort cryptos** by clicking column headers (price, change, volume)
- **⏰ Change time period** on charts (7j, 30j, 90j)
- **🔔 Check badge count** in navbar for unread notifications
- **💰 Monitor balance** in dashboard stat card
- **📈 View gains/losses** color-coded (green/red)
- **🔐 Admin panel** separate login for administrators

---

## 🎉 YOU'RE ALL SET!

Your BitChest cryptocurrency trading platform is **FULLY FUNCTIONAL** and **PRODUCTION READY**.

All buttons work ✓  
All features implemented ✓  
Professional logos display ✓  
Notifications system complete ✓  

**Enjoy trading! 🚀**

---

*Last Updated: November 20, 2025*  
*Project Status: ✅ COMPLETE & OPERATIONAL*
