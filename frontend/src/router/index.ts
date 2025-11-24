import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Dashboard from '../views/Dashboard.vue'
import Market from '../views/Market.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import CryptoDetail from '../views/CryptoDetail.vue'
import Profile from '../views/Profile.vue'
import Wallet from '../views/Wallet.vue'
import Transactions from '../views/Transactions.vue'
import Alerts from '../views/Alerts.vue'

import PortfolioDetail from '../views/PortfolioDetail.vue'
import CryptoListPage from '../views/CryptoListPage.vue'
import TransactionsHistory from '../views/TransactionsHistory.vue'
import AlertsPage from '../views/AlertsPage.vue'
import ProfilePage from '../views/ProfilePage.vue'
import CryptoDetailPage from '../views/CryptoDetailPage.vue'
import BuyPage from '../views/BuyPage.vue'
import SellPage from '../views/SellPage.vue'
import Notifications from '../views/Notifications.vue'

// Admin Pages
import AdminLoginPage from '../views/admin/AdminLoginPage.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminUsersPage from '../views/admin/AdminUsersPage.vue'
import AdminCryptosPage from '../views/admin/AdminCryptosPage.vue'
import AdminTransactionsPage from '../views/admin/AdminTransactionsPage.vue'
import AdminAlertsPage from '../views/admin/AdminAlertsPage.vue'
import AdminSettingsPage from '../views/admin/AdminSettingsPage.vue'
const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/market', name: 'Market', component: Market },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/crypto/:id', name: 'CryptoDetail', component: CryptoDetail, meta: { requiresAuth: true } },
  { path: '/profile', name: 'Profile', component: Profile, meta: { requiresAuth: true } },
  { path: '/wallet', name: 'Wallet', component: Wallet, meta: { requiresAuth: true } },
  { path: '/transactions', name: 'Transactions', component: Transactions, meta: { requiresAuth: true } },
  { path: '/alerts', name: 'Alerts', component: Alerts, meta: { requiresAuth: true } },

  // New Client Pages
  { path: '/portfolio', name: 'Portfolio', component: PortfolioDetail, meta: { requiresAuth: true } },
  { path: '/cryptos', name: 'Cryptos', component: CryptoListPage, meta: { requiresAuth: true } },
  { path: '/history', name: 'History', component: TransactionsHistory, meta: { requiresAuth: true } },
  { path: '/alerts-page', name: 'AlertsPage', component: AlertsPage, meta: { requiresAuth: true } },
  { path: '/profile-page', name: 'ProfilePage', component: ProfilePage, meta: { requiresAuth: true } },
  { path: '/crypto-detail/:id', name: 'CryptoDetailPage', component: CryptoDetailPage, meta: { requiresAuth: true } },
  { path: '/buy', name: 'Buy', component: BuyPage, meta: { requiresAuth: true } },
  { path: '/sell', name: 'Sell', component: SellPage, meta: { requiresAuth: true } },
  { path: '/notifications', name: 'Notifications', component: Notifications, meta: { requiresAuth: true } },
  
  // Admin Routes
  { path: '/admin', redirect: '/admin/dashboard' },
  { path: '/admin/login', name: 'AdminLogin', component: AdminLoginPage },
  { 
    path: '/admin/dashboard', 
    name: 'AdminDashboard', 
    component: AdminDashboard, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { 
    path: '/admin/users', 
    name: 'AdminUsers', 
    component: AdminUsersPage, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { 
    path: '/admin/cryptos', 
    name: 'AdminCryptos', 
    component: AdminCryptosPage, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { 
    path: '/admin/transactions', 
    name: 'AdminTransactions', 
    component: AdminTransactionsPage, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { 
    path: '/admin/alerts', 
    name: 'AdminAlerts', 
    component: AdminAlertsPage, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { 
    path: '/admin/settings', 
    name: 'AdminSettings', 
    component: AdminSettingsPage, 
    meta: { requiresAuth: true, requiresAdmin: true } 
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to: any, from: any, next: any) => {
  const token = localStorage.getItem('token')
  const user = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')!) : null

  if (to.meta.requiresAuth && !token) {
    next({ name: 'Login' })
    return
  }

  if (to.meta.requiresAdmin) {
    const isAdmin = user && (user.isAdmin || user.role === 'admin' || user.role === 'Administrator' || user.role === 'ADMIN')
    if (!isAdmin) {
      next({ name: 'Dashboard' })
      return
    }
  }

  next()
})

export default router
