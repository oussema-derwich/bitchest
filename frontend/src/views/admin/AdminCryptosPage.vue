<template>
  <AdminLayout pageTitle="Gestion des Cryptomonnaies" pageDescription="Gérez le catalogue des cryptomonnaies disponibles">
    <!-- Action Buttons -->
    <div class="flex gap-4 mb-6">
      <button
        @click="showAddModal = true"
        class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2"
      >
        <span>➕</span>
        <span>Ajouter une crypto</span>
      </button>
      <button class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
        <span>🔄</span>
        <span>Actualiser les cours</span>
      </button>
    </div>

    <!-- Cryptos Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Logo</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Nom</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Symbole</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Prix actuel</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Variation</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Statut</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="crypto in cryptos" :key="crypto.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="py-4 px-6">
                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-lg">
                  <img :src="crypto.logo" :alt="crypto.name" class="w-8 h-8" />
                </div>
              </td>
              <td class="py-4 px-6 font-medium text-gray-800">{{ crypto.name }}</td>
              <td class="py-4 px-6 font-bold text-gray-700">{{ crypto.symbol }}</td>
              <td class="py-4 px-6 text-lg font-bold text-gray-800">{{ formatCurrency(crypto.price) }}</td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-flex items-center gap-1 font-bold',
                    crypto.variation >= 0 ? 'text-green-600' : 'text-red-600'
                  ]"
                >
                  <span>{{ crypto.variation >= 0 ? '📈' : '📉' }}</span>
                  {{ crypto.variation >= 0 ? '+' : '' }}{{ crypto.variation }}%
                </span>
              </td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    crypto.status === 'Actif'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ crypto.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <button
                    @click="editCrypto(crypto)"
                    class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium hover:bg-yellow-200 transition"
                  >
                    ✏️ Modifier
                  </button>
                  <button
                    @click="viewDetails(crypto)"
                    class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-200 transition"
                  >
                    📊 Détails
                  </button>
                  <button
                    @click="deleteCrypto(crypto.id)"
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

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">{{ editingCrypto ? 'Modifier une crypto' : 'Ajouter une crypto' }}</h3>

        <form @submit.prevent="saveCrypto" class="space-y-4">
          <!-- Nom -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom (ex: Bitcoin)</label>
            <input
              v-model="formData.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Symbole -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Symbole (ex: BTC)</label>
            <input
              v-model="formData.symbol"
              type="text"
              maxlength="10"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Prix -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Prix de référence</label>
            <input
              v-model.number="formData.price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="formData.description"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
            ></textarea>
          </div>

          <!-- Statut -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">État</label>
            <select v-model="formData.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="Actif">Actif</option>
              <option value="Inactif">Inactif</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex gap-4 mt-6">
            <button
              type="button"
              @click="showAddModal = false"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
            >
              {{ editingCrypto ? 'Modifier' : 'Ajouter' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from './AdminLayout.vue'
import axios from 'axios'

const showAddModal = ref(false)
const editingCrypto = ref<any>(null)

const formData = reactive({
  name: '',
  symbol: '',
  price: 0,
  description: '',
  status: 'Actif',
  logo: '/assets/bitcoin.png'
})

const cryptos = ref([
  {
    id: 1,
    name: 'Bitcoin',
    symbol: 'BTC',
    price: 88000,
    variation: 3.72,
    status: 'Actif',
    logo: '/assets/bitcoin.png'
  },
  {
    id: 2,
    name: 'Ethereum',
    symbol: 'ETH',
    price: 6950,
    variation: -1.12,
    status: 'Actif',
    logo: '/assets/ethereum.png'
  },
  {
    id: 3,
    name: 'Cardano',
    symbol: 'ADA',
    price: 1850,
    variation: 2.45,
    status: 'Actif',
    logo: '/assets/cardano.png'
  },
  {
    id: 4,
    name: 'Solana',
    symbol: 'SOL',
    price: 250,
    variation: 5.82,
    status: 'Actif',
    logo: '/assets/stellar.png'
  },
  {
    id: 5,
    name: 'Ripple',
    symbol: 'XRP',
    price: 3500,
    variation: -0.88,
    status: 'Actif',
    logo: '/assets/ripple.png'
  },
  {
    id: 6,
    name: 'Litecoin',
    symbol: 'LTC',
    price: 2150,
    variation: 1.34,
    status: 'Actif',
    logo: '/assets/litecoin.png'
  }
])

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'currency',
    currency: 'TND',
    minimumFractionDigits: 2
  }).format(value)
}

const saveCrypto = async () => {
  try {
    if (editingCrypto.value) {
      // PUT request pour modifier
      await axios.put(`/api/admin/cryptos/${editingCrypto.value.id}`, formData)
      const idx = cryptos.value.findIndex(c => c.id === editingCrypto.value.id)
      if (idx >= 0) {
        cryptos.value[idx] = { ...cryptos.value[idx], ...formData }
      }
    } else {
      // POST request pour ajouter
      const response = await axios.post('/api/admin/cryptos', formData)
      cryptos.value.push(response.data)
    }
    showAddModal.value = false
    resetForm()
  } catch (e) {
    console.error('Error saving crypto:', e)
  }
}

const editCrypto = (crypto: any) => {
  editingCrypto.value = crypto
  formData.name = crypto.name
  formData.symbol = crypto.symbol
  formData.price = crypto.price
  formData.description = crypto.description || ''
  formData.status = crypto.status
  formData.logo = crypto.logo
  showAddModal.value = true
}

const viewDetails = (crypto: any) => {
  console.log('View details:', crypto)
}

const deleteCrypto = async (cryptoId: number) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette crypto ?')) {
    try {
      await axios.delete(`/api/admin/cryptos/${cryptoId}`)
      cryptos.value = cryptos.value.filter(c => c.id !== cryptoId)
    } catch (e) {
      console.error('Error deleting crypto:', e)
    }
  }
}

const resetForm = () => {
  formData.name = ''
  formData.symbol = ''
  formData.price = 0
  formData.description = ''
  formData.status = 'Actif'
  formData.logo = '/assets/bitcoin.png'
  editingCrypto.value = null
}

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/cryptos')
    cryptos.value = response.data
  } catch (e) {
    console.error('Error loading cryptos:', e)
  }
})
</script>

<style scoped>
</style>
