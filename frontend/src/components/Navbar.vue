<template>
  <nav
    v-if="!hideOnClientArea"
    :class="[
      'w-full transition-all duration-300 shadow-sm',
      isAdmin ? 'bg-gray-900 text-white' : 'bg-white text-black'
    ]"
  >
    <div class="container">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3">
          <img src="/assets/bitchest_logo.png" alt="BitChest Logo" class="h-10 w-auto" />
        </router-link>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link
            v-for="link in filteredNavLinks"
            :key="link.to"
            :to="link.to"
            class="text-sm font-medium transition-colors"
            :class="isAdmin ? 'hover:text-gray-300' : 'hover:text-gray-700'"
            active-class="font-semibold underline"
          >
            {{ link.text }}
          </router-link>
        </div>

        <!-- Actions -->
        <div class="hidden md:flex items-center space-x-4">
          <!-- Notifications -->
          <router-link v-if="isLogged" to="/notifications" class="relative">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z"/>
            </svg>
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full px-1.5"
            >{{ unreadCount }}</span>
          </router-link>

          <!-- Login / Register -->
          <template v-if="!isLogged">
            <router-link to="/login" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition">
              Se connecter
            </router-link>
            <router-link to="/register" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition">
              S'inscrire
            </router-link>
          </template>

          <!-- Profile Dropdown -->
          <div v-else class="relative">
            <button @click="toggleProfileMenu" class="flex items-center space-x-2">
              <img
                v-if="currentUser?.avatar"
                :src="currentUser.avatar"
                alt="avatar"
                class="h-8 w-8 rounded-full"
              />
              <span>{{ currentUser?.name || 'Mon compte' }}</span>
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div
              v-if="showProfileMenu"
              class="absolute right-0 mt-2 w-48 bg-white text-black rounded-md shadow-lg overflow-hidden z-50"
            >
              <router-link v-if="isAdmin" to="/admin/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">
                Profil administrateur
              </router-link>
              <router-link v-if="isAdmin" to="/admin/audit" class="block px-4 py-2 text-sm hover:bg-gray-100">
                Journal d'audit
              </router-link>
              <router-link to="/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">
                Mon profil
              </router-link>
              <button
                @click="handleLogout"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
              >
                Déconnexion
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile menu toggle -->
        <button @click="isMenuOpen = !isMenuOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-200">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="!isMenuOpen"
              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              v-else
              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-show="isMenuOpen" class="md:hidden bg-white border-t">
      <div class="py-3">
        <router-link
          v-for="link in filteredNavLinks"
          :key="link.to"
          :to="link.to"
          class="block px-4 py-2 text-sm text-black hover:bg-gray-100"
        >
          {{ link.text }}
        </router-link>

        <div class="px-4 py-2">
          <router-link
            v-if="!isLogged"
            to="/login"
            class="block btn btn-primary w-full text-center"
          >Se connecter</router-link>
          <router-link
            v-if="!isLogged"
            to="/register"
            class="block btn btn-secondary w-full text-center mt-2"
          >S'inscrire</router-link>
          <button
            v-if="isLogged"
            @click="handleLogout"
            class="block btn btn-danger w-full mt-2"
          >Déconnexion</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { currentUser, isAuthenticated, logout } from '../services/auth'

export default defineComponent({
  name: 'Navbar',
  setup() {
    const router = useRouter()
    const route = useRoute()

    const isMenuOpen = ref(false)
    const showProfileMenu = ref(false)

    const isLogged = computed(() => isAuthenticated.value)

    const isAdmin = computed(() => currentUser.value?.role === 'admin')

    // Liste simplifiée des liens visibles
    const allNavLinks = [
      { to: '/', text: 'Accueil', requiresAuth: false },
      { to: '/market', text: 'Marché', requiresAuth: false },
      { to: '/alerts', text: 'Alertes', requiresAuth: true },
      { to: '/admin', text: 'Administration', requiresAuth: true, requiresAdmin: true }
    ]

    // Filtrage selon authentification / rôle
    const filteredNavLinks = computed(() =>
      allNavLinks.filter(link => {
        if (link.requiresAuth && !isLogged.value) return false
        if (link.requiresAdmin && !isAdmin.value) return false
        return true
      })
    )

    // Masquer la navbar sur les pages dashboard/admin
    const clientPaths = ['/dashboard', '/portfolio', '/wallet', '/transactions']
    const adminPaths = ['/admin']
    const hideOnClientArea = computed(() =>
      clientPaths.some(p => route.path.startsWith(p)) ||
      adminPaths.some(p => route.path.startsWith(p))
    )

    const handleLogout = async () => {
      try {
        await logout()
      } catch (e) {
        console.error('Logout error:', e)
      }
      isMenuOpen.value = false
      showProfileMenu.value = false
      router.push('/')
    }

    const toggleProfileMenu = () => {
      showProfileMenu.value = !showProfileMenu.value
    }

    const unreadCount = computed(() => {
      const val = localStorage.getItem('notifications_unread')
      return val ? parseInt(val, 10) : 0
    })

    return {
      isMenuOpen,
      showProfileMenu,
      isLogged,
      currentUser,
      isAdmin,
      filteredNavLinks,
      handleLogout,
      toggleProfileMenu,
      unreadCount,
      hideOnClientArea
    }
  }
})
</script>
