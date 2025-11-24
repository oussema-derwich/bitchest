<template>
  <AdminLayout pageTitle="Gestion des Utilisateurs" pageDescription="Gérez les comptes utilisateurs de la plateforme">
    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nom, email ou rôle..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="selectedStatus"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="Actif">Actif</option>
            <option value="Suspendu">Suspendu</option>
            <option value="En attente">En attente</option>
          </select>
        </div>
        <div class="flex items-end">
          <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
            <span>🔍</span>
            <span>Filtrer</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Nom</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Email</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Date d'inscription</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Rôle</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Statut</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-medium text-gray-800">{{ user.name }}</span>
                </div>
              </td>
              <td class="py-4 px-6 text-gray-600">{{ user.email }}</td>
              <td class="py-4 px-6 text-gray-600 text-sm">{{ formatDate(user.created_at) }}</td>
              <td class="py-4 px-6">
                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                  {{ user.role === 'admin' ? 'Admin' : 'Client' }}
                </span>
              </td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    user.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ user.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <button
                    @click="selectUser(user); showDetailsPanel = true"
                    class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-200 transition"
                  >
                    👁 Voir
                  </button>
                  <button
                    @click="editUser(user)"
                    class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium hover:bg-yellow-200 transition"
                  >
                    ✏️ Modifier
                  </button>
                  <button
                    v-if="user.is_active"
                    @click="suspendUser(user.id)"
                    class="px-3 py-1 bg-orange-100 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-200 transition"
                  >
                    ⛔ Suspendre
                  </button>
                  <button
                    v-else
                    @click="activateUser(user.id)"
                    class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-xs font-medium hover:bg-green-200 transition"
                  >
                    ✅ Réactiver
                  </button>
                  <button
                    @click="deleteUser(user.id)"
                    class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition"
                  >
                    🗑 Supprimer
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Details Panel (Sidebar) -->
    <div
      v-if="showDetailsPanel"
      class="fixed right-0 top-0 h-screen w-96 bg-white shadow-xl z-50 overflow-y-auto"
    >
      <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Détails Utilisateur</h3>
          <button
            @click="showDetailsPanel = false"
            class="text-gray-500 hover:text-gray-700 text-2xl"
          >
            ✕
          </button>
        </div>
      </div>

      <div v-if="selectedUser" class="p-6 space-y-6">
        <!-- User Avatar and Name -->
        <div class="text-center">
          <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
            {{ selectedUser.name.charAt(0).toUpperCase() }}
          </div>
          <h4 class="text-xl font-bold text-gray-800">{{ selectedUser.name }}</h4>
          <p class="text-gray-500 text-sm">{{ selectedUser.email }}</p>
        </div>

        <!-- User Info -->
        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Date d'inscription</p>
            <p class="font-medium text-gray-800">{{ selectedUser.createdAt }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Solde</p>
              <p class="font-bold text-lg text-blue-600">{{ formatCurrency(selectedUser.balance) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Transactions</p>
              <p class="font-bold text-lg text-emerald-600">{{ selectedUser.transactions }}</p>
            </div>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-xs text-blue-600 font-medium mb-2">Rôle: {{ selectedUser.role }}</p>
            <p class="text-xs text-blue-600">Statut: {{ selectedUser.status }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
          <button
            @click="viewHistory(selectedUser.id)"
            class="w-full py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2"
          >
            <span>📜</span>
            <span>Voir l'historique</span>
          </button>
          <button
            @click="editUser(selectedUser)"
            class="w-full py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition"
          >
            Modifier les informations
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Overlay -->
    <div
      v-if="showDetailsPanel"
      @click="showDetailsPanel = false"
      class="fixed inset-0 bg-black bg-opacity-50 z-40"
    ></div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from './AdminLayout.vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

interface User {
  id: number
  name: string
  email: string
  created_at: string
  role: string
  is_active: boolean
  balance_eur: number
}

const router = useRouter()
const searchQuery = ref('')
const selectedStatus = ref('')
const showDetailsPanel = ref(false)
const selectedUser = ref<User | null>(null)
const isLoading = ref(false)

const users = ref<User[]>([])

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch = 
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.role.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = !selectedStatus.value || 
      (selectedStatus.value === 'Actif' && user.is_active) ||
      (selectedStatus.value === 'Inactif' && !user.is_active)
    
    return matchesSearch && matchesStatus
  })
})

const selectUser = (user: User) => {
  selectedUser.value = user
}

const editUser = (user: User) => {
  console.log('Edit user:', user)
  // Open edit modal here
}

const suspendUser = async (userId: number) => {
  try {
    await api.put(`/admin/users/${userId}`, { is_active: false })
    const user = users.value.find(u => u.id === userId)
    if (user) user.is_active = false
  } catch (e) {
    console.error('Error suspending user:', e)
  }
}

const activateUser = async (userId: number) => {
  try {
    await api.put(`/admin/users/${userId}`, { is_active: true })
    const user = users.value.find(u => u.id === userId)
    if (user) user.is_active = true
  } catch (e) {
    console.error('Error activating user:', e)
  }
}

const deleteUser = async (userId: number) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
    try {
      await api.delete(`/admin/users/${userId}`)
      users.value = users.value.filter(u => u.id !== userId)
    } catch (e) {
      console.error('Error deleting user:', e)
    }
  }
}

const viewHistory = (userId: number) => {
  router.push(`/admin/transactions?userId=${userId}`)
  showDetailsPanel.value = false
}

const formatDate = (dateString: string): string => {
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR')
  } catch {
    return dateString
  }
}

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'currency',
    currency: 'TND'
  }).format(value)
}

const loadUsers = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/admin/users')
    if (response.data?.data) {
      users.value = response.data.data
    } else if (Array.isArray(response.data)) {
      users.value = response.data
    }
  } catch (e) {
    console.error('Error loading users:', e)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
</style>
