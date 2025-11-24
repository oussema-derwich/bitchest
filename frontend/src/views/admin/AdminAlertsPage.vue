<template>
  <AdminLayout pageTitle="Gestion des Alertes" pageDescription="Supervision des alertes créées par les utilisateurs">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
        <p class="text-gray-500 text-sm mb-2">🚨 Alertes actives</p>
        <p class="text-3xl font-bold text-orange-600">{{ stats.activeAlerts }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <p class="text-gray-500 text-sm mb-2">✅ Déclenchées aujourd'hui</p>
        <p class="text-3xl font-bold text-green-600">{{ stats.triggeredToday }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm mb-2">⏸️ Mises en pause</p>
        <p class="text-3xl font-bold text-blue-600">{{ stats.paused }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateur</label>
          <input
            v-model="filterUser"
            type="text"
            placeholder="Rechercher..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Crypto</label>
          <select v-model="filterCrypto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Toutes</option>
            <option value="BTC">Bitcoin (BTC)</option>
            <option value="ETH">Ethereum (ETH)</option>
            <option value="ADA">Cardano (ADA)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">État</label>
          <select v-model="filterStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Tous</option>
            <option value="Active">Active</option>
            <option value="Pausée">Pausée</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Utilisateur</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Crypto</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Seuil</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Type</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Prix actuel</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">État</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Créée le</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="alert in filteredAlerts" :key="alert.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="py-4 px-6 font-medium text-gray-800">{{ alert.user }}</td>
              <td class="py-4 px-6 font-bold text-gray-800">{{ alert.crypto }}</td>
              <td class="py-4 px-6 font-bold text-lg text-blue-600">{{ formatCurrency(alert.threshold) }}</td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    alert.type === '↑ Au-dessus'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ alert.type }}
                </span>
              </td>
              <td class="py-4 px-6 text-gray-700">{{ formatCurrency(alert.currentPrice) }}</td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    alert.status === 'Active'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ alert.status }}
                </span>
              </td>
              <td class="py-4 px-6 text-gray-600 text-sm">{{ alert.createdAt }}</td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <button
                    @click="editAlert(alert)"
                    class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium hover:bg-yellow-200 transition"
                  >
                    ✏️ Modifier
                  </button>
                  <button
                    v-if="alert.status === 'Active'"
                    @click="pauseAlert(alert.id)"
                    class="px-3 py-1 bg-orange-100 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-200 transition"
                  >
                    ⏸ Pauser
                  </button>
                  <button
                    v-else
                    @click="resumeAlert(alert.id)"
                    class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-xs font-medium hover:bg-green-200 transition"
                  >
                    ▶️ Reprendre
                  </button>
                  <button
                    @click="deleteAlert(alert.id)"
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

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Modifier l'alerte</h3>

        <form @submit.prevent="updateAlert" class="space-y-4">
          <!-- Seuil -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Seuil (DT)</label>
            <input
              v-model.number="editForm.threshold"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type d'alerte</label>
            <select v-model="editForm.type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="↑ Au-dessus">Au-dessus du seuil</option>
              <option value="↓ Au-dessous">Au-dessous du seuil</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex gap-4 mt-6">
            <button
              type="button"
              @click="showEditModal = false"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
            >
              Modifier
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import AdminLayout from './AdminLayout.vue'
import axios from 'axios'

const filterUser = ref('')
const filterCrypto = ref('')
const filterStatus = ref('')
const showEditModal = ref(false)
const editingAlert = ref<any>(null)

const editForm = reactive({
  threshold: 0,
  type: '↑ Au-dessus'
})

const stats = ref({
  activeAlerts: 856,
  triggeredToday: 12,
  paused: 24
})

const alerts = ref([
  {
    id: 1,
    user: 'chedi01',
    crypto: 'BTC',
    threshold: 80000,
    type: '↑ Au-dessus',
    currentPrice: 88000,
    status: 'Active',
    createdAt: '10/11/2025'
  },
  {
    id: 2,
    user: 'mariem02',
    crypto: 'ETH',
    threshold: 7000,
    type: '↓ Au-dessous',
    currentPrice: 6950,
    status: 'Active',
    createdAt: '11/11/2025'
  },
  {
    id: 3,
    user: 'ahmed03',
    crypto: 'ADA',
    threshold: 2000,
    type: '↑ Au-dessus',
    currentPrice: 1850,
    status: 'Pausée',
    createdAt: '09/11/2025'
  },
  {
    id: 4,
    user: 'zaineb04',
    crypto: 'BTC',
    threshold: 90000,
    type: '↑ Au-dessus',
    currentPrice: 88000,
    status: 'Active',
    createdAt: '12/11/2025'
  }
])

const filteredAlerts = computed(() => {
  return alerts.value.filter(alert => {
    const matchesUser = alert.user.toLowerCase().includes(filterUser.value.toLowerCase())
    const matchesCrypto = !filterCrypto.value || alert.crypto === filterCrypto.value
    const matchesStatus = !filterStatus.value || alert.status === filterStatus.value
    return matchesUser && matchesCrypto && matchesStatus
  })
})

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'currency',
    currency: 'TND',
    minimumFractionDigits: 2
  }).format(value)
}

const editAlert = (alert: any) => {
  editingAlert.value = alert
  editForm.threshold = alert.threshold
  editForm.type = alert.type
  showEditModal.value = true
}

const updateAlert = async () => {
  try {
    await axios.put(`/api/admin/alerts/${editingAlert.value.id}`, editForm)
    const idx = alerts.value.findIndex(a => a.id === editingAlert.value.id)
    if (idx >= 0) {
      alerts.value[idx] = {
        ...alerts.value[idx],
        threshold: editForm.threshold,
        type: editForm.type
      }
    }
    showEditModal.value = false
  } catch (e) {
    console.error('Error updating alert:', e)
  }
}

const pauseAlert = async (alertId: number) => {
  try {
    await axios.post(`/api/admin/alerts/${alertId}/pause`)
    const alert = alerts.value.find(a => a.id === alertId)
    if (alert) alert.status = 'Pausée'
  } catch (e) {
    console.error('Error pausing alert:', e)
  }
}

const resumeAlert = async (alertId: number) => {
  try {
    await axios.post(`/api/admin/alerts/${alertId}/resume`)
    const alert = alerts.value.find(a => a.id === alertId)
    if (alert) alert.status = 'Active'
  } catch (e) {
    console.error('Error resuming alert:', e)
  }
}

const deleteAlert = async (alertId: number) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?')) {
    try {
      await axios.delete(`/api/admin/alerts/${alertId}`)
      alerts.value = alerts.value.filter(a => a.id !== alertId)
    } catch (e) {
      console.error('Error deleting alert:', e)
    }
  }
}

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/alerts')
    alerts.value = response.data
  } catch (e) {
    console.error('Error loading alerts:', e)
  }
})
</script>

<style scoped>
</style>
