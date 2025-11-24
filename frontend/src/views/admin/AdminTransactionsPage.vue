<template>
  <AdminLayout pageTitle="Suivi des Transactions" pageDescription="Assurance et traçabilité de toutes les transactions">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
          <select v-model="filterType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Tous</option>
            <option value="Achat">Achat</option>
            <option value="Vente">Vente</option>
          </select>
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
        <div class="flex items-end">
          <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            Exporter PDF
          </button>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Date</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Utilisateur</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Type</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Crypto</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Quantité</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Prix unitaire</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Montant final</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Statut</th>
              <th class="text-left py-4 px-6 text-gray-700 font-semibold text-sm">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tx in filteredTransactions" :key="tx.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="py-4 px-6 text-gray-600 text-sm">{{ tx.date }}</td>
              <td class="py-4 px-6 font-medium text-gray-800">{{ tx.user }}</td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    tx.type === 'Achat'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ tx.type }}
                </span>
              </td>
              <td class="py-4 px-6 font-semibold text-gray-800">{{ tx.crypto }}</td>
              <td class="py-4 px-6 text-gray-700">{{ tx.quantity }}</td>
              <td class="py-4 px-6 text-gray-700">{{ formatCurrency(tx.pricePerUnit) }}</td>
              <td class="py-4 px-6 font-bold text-lg text-gray-800">{{ formatCurrency(tx.totalAmount) }}</td>
              <td class="py-4 px-6">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    tx.status === 'Confirmé'
                      ? 'bg-green-100 text-green-800'
                      : tx.status === 'En attente'
                      ? 'bg-yellow-100 text-yellow-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ tx.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <button
                    @click="viewTransactionDetails(tx)"
                    class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-200 transition"
                  >
                    🔍 Détails
                  </button>
                  <button
                    v-if="tx.status === 'En attente'"
                    @click="cancelTransaction(tx.id)"
                    class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition"
                  >
                    ⚠️ Annuler
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-gray-800">Détails de la transaction</h3>
          <button @click="showDetailsModal = false" class="text-2xl text-gray-500">✕</button>
        </div>

        <div v-if="selectedTransaction" class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Date</p>
            <p class="font-medium text-gray-800">{{ selectedTransaction.date }}</p>
          </div>

          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Utilisateur</p>
            <p class="font-medium text-gray-800">{{ selectedTransaction.user }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Type</p>
              <p class="font-medium text-gray-800">{{ selectedTransaction.type }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Crypto</p>
              <p class="font-medium text-gray-800">{{ selectedTransaction.crypto }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Quantité</p>
              <p class="font-bold text-lg text-blue-600">{{ selectedTransaction.quantity }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-xs text-gray-500 mb-1">Prix unit.</p>
              <p class="font-bold text-lg text-blue-600">{{ formatCurrency(selectedTransaction.pricePerUnit) }}</p>
            </div>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-xs text-blue-600 mb-1">Montant total</p>
            <p class="text-2xl font-bold text-blue-800">{{ formatCurrency(selectedTransaction.totalAmount) }}</p>
          </div>

          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Statut</p>
            <p class="font-medium text-gray-800">{{ selectedTransaction.status }}</p>
          </div>

          <button
            @click="showDetailsModal = false"
            class="w-full mt-6 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from './AdminLayout.vue'
import axios from 'axios'

const filterUser = ref('')
const filterType = ref('')
const filterCrypto = ref('')
const showDetailsModal = ref(false)
const selectedTransaction = ref<any>(null)

const transactions = ref([
  {
    id: 1,
    date: '11/11/2025',
    user: 'chedi01',
    type: 'Achat',
    crypto: 'BTC',
    quantity: 0.002,
    pricePerUnit: 88000,
    totalAmount: 176,
    status: 'Confirmé'
  },
  {
    id: 2,
    date: '12/11/2025',
    user: 'mariem02',
    type: 'Vente',
    crypto: 'ETH',
    quantity: 0.1,
    pricePerUnit: 6950,
    totalAmount: 695,
    status: 'En attente'
  },
  {
    id: 3,
    date: '10/11/2025',
    user: 'ahmed03',
    type: 'Achat',
    crypto: 'ADA',
    quantity: 10,
    pricePerUnit: 1850,
    totalAmount: 18500,
    status: 'Confirmé'
  },
  {
    id: 4,
    date: '09/11/2025',
    user: 'zaineb04',
    type: 'Vente',
    crypto: 'BTC',
    quantity: 0.001,
    pricePerUnit: 88000,
    totalAmount: 88,
    status: 'Confirmé'
  }
])

const filteredTransactions = computed(() => {
  return transactions.value.filter(tx => {
    const matchesUser = tx.user.toLowerCase().includes(filterUser.value.toLowerCase())
    const matchesType = !filterType.value || tx.type === filterType.value
    const matchesCrypto = !filterCrypto.value || tx.crypto === filterCrypto.value
    return matchesUser && matchesType && matchesCrypto
  })
})

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'currency',
    currency: 'TND',
    minimumFractionDigits: 2
  }).format(value)
}

const viewTransactionDetails = (tx: any) => {
  selectedTransaction.value = tx
  showDetailsModal.value = true
}

const cancelTransaction = async (txId: number) => {
  if (confirm('Êtes-vous sûr de vouloir annuler cette transaction ?')) {
    try {
      await axios.post(`/api/admin/transactions/${txId}/cancel`)
      const tx = transactions.value.find(t => t.id === txId)
      if (tx) tx.status = 'Annulée'
    } catch (e) {
      console.error('Error cancelling transaction:', e)
    }
  }
}

onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/transactions')
    transactions.value = response.data
  } catch (e) {
    console.error('Error loading transactions:', e)
  }
})
</script>

<style scoped>
</style>
