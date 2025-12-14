<template>
  <div class="flex">
    <ClientSidebar />
    <div class="flex-1">
      <!-- Header Navbar -->
      <header class="bg-white shadow-md p-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center text-white font-bold">BC</div>
          <h1 class="text-xl font-bold text-primary">BitChest</h1>
        </div>
        <div class="flex items-center gap-4">
          <button class="relative text-gray-600 hover:text-primary transition">
            🔔
            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          <span class="text-sm font-medium text-gray-700">{{ userName }}</span>
          <button
            @click="logout"
            class="px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition"
          >
            Déconnexion
          </button>
        </div>
      </header>

      <!-- Main Content -->
      <main class="p-8 bg-background min-h-screen">
        <button
          @click="$router.back()"
          class="mb-6 px-4 py-2 bg-gray-400 text-white rounded-lg font-medium hover:opacity-90 transition"
        >
          ← Retour
        </button>

        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-card p-8">
          <h2 class="text-3xl font-bold text-secondary mb-8">✕ Vendre une Cryptomonnaie</h2>

          <form @submit.prevent="submitTransaction" class="space-y-6">
            <!-- Crypto Selection -->
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Crypto</label>
              <select
                v-model="form.crypto"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium focus:border-primary focus:outline-none transition"
              >
                <option v-for="crypto in cryptoOptions" :key="crypto.id" :value="crypto.id.toString()">
                  {{ crypto.name }} ({{ crypto.symbol }})
                </option>
              </select>
            </div>

            <!-- Your Holdings -->
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Votre Quantité</label>
              <input
                type="text"
                readonly
                value="0.002 BTC (180 DT)"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium bg-gray-100 cursor-not-allowed"
              />
            </div>

            <!-- Quantity or Amount -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Quantité à Vendre</label>
                <input
                  v-model="form.quantity"
                  type="number"
                  placeholder="0.001"
                  step="0.001"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium focus:border-primary focus:outline-none transition"
                  @input="calculateTotal"
                />
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Montant Reçu</label>
                <input
                  v-model="form.amount"
                  type="number"
                  placeholder="100"
                  step="0.01"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium focus:border-primary focus:outline-none transition"
                  @input="calculateQuantity"
                />
              </div>
            </div>

            <!-- Current Price (Locked) -->
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Prix Actuel (DT)</label>
              <input
                v-model="form.currentPrice"
                type="text"
                readonly
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium bg-gray-100 cursor-not-allowed"
              />
            </div>

            <!-- Fees -->
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Frais (0.5%)</label>
              <input
                v-model="form.fees"
                type="text"
                readonly
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium bg-gray-100 cursor-not-allowed"
              />
            </div>

            <!-- Total -->
            <div class="border-t-2 border-gray-200 pt-4">
              <label class="block text-sm font-bold text-gray-700 mb-2">Montant Net Reçu (DT)</label>
              <input
                v-model="form.total"
                type="text"
                readonly
                class="w-full px-4 py-3 border-2 border-primary rounded-lg text-lg font-bold bg-blue-50 cursor-not-allowed text-primary"
              />
            </div>

            <!-- Submit -->
            <div class="flex gap-4 pt-4">
              <button
                type="submit"
                class="flex-1 px-6 py-3 bg-success text-white rounded-lg font-bold hover:opacity-90 transition"
              >
                ✓ Confirmer
              </button>
              <button
                type="button"
                @click="$router.back()"
                class="flex-1 px-6 py-3 bg-danger text-white rounded-lg font-bold hover:opacity-90 transition"
              >
                ✕ Annuler
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, watch } from 'vue'
import ClientSidebar from '../components/ClientSidebar.vue'
import api from '../services/api'
import { useRouter, useRoute } from 'vue-router'

export default defineComponent({
  components: { ClientSidebar },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const userName = ref('Utilisateur')
    const cryptoOptions = ref<any[]>([])

    const form = ref({
      crypto: route.query.crypto_id ? route.query.crypto_id.toString() : '',
      quantity: '',
      amount: '',
      currentPrice: '0',
      fees: '',
      total: ''
    })

    const loadData = async () => {
      try {
        const [profileRes, cryptosRes] = await Promise.all([
          api.get('/auth/profile'),
          api.get('/cryptocurrencies')
        ])
        if (profileRes.data?.data) {
          userName.value = profileRes.data.data.name || 'Utilisateur'
        }
        cryptoOptions.value = cryptosRes.data?.data || []
        
        // Set crypto from query parameter if provided
        if (route.query.crypto_id) {
          form.value.crypto = route.query.crypto_id.toString()
        } else if (cryptoOptions.value.length > 0) {
          form.value.crypto = cryptoOptions.value[0].id.toString()
        }
        
        // Set current price for selected crypto
        if (form.value.crypto) {
          const selectedCrypto = cryptoOptions.value.find(c => c.id.toString() === form.value.crypto)
          if (selectedCrypto) {
            form.value.currentPrice = selectedCrypto.current_price?.toString() || selectedCrypto.price?.toString() || '0'
          }
        }
      } catch (e) {
        console.error('Error loading profile:', e)
      }
    }

    const logout = async () => {
      try {
        await api.post('/auth/logout')
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        router.push('/login')
      } catch (e) {
        console.error('Logout error:', e)
        router.push('/login')
      }
    }

    const calculateTotal = () => {
      const quantity = parseFloat(form.value.quantity) || 0
      const price = parseFloat(form.value.currentPrice.replace(/\s/g, '')) || 0
      const subtotal = quantity * price
      const feesAmount = subtotal * 0.005 // 0.5%
      const total = subtotal - feesAmount

      form.value.amount = subtotal.toFixed(2)
      form.value.fees = feesAmount.toFixed(2)
      form.value.total = total.toFixed(2)
    }

    const calculateQuantity = () => {
      const amount = parseFloat(form.value.amount) || 0
      const price = parseFloat(form.value.currentPrice.replace(/\s/g, '')) || 0
      const quantity = amount / price
      const subtotal = amount
      const feesAmount = subtotal * 0.005
      const total = subtotal - feesAmount

      form.value.quantity = quantity.toFixed(6)
      form.value.fees = feesAmount.toFixed(2)
      form.value.total = total.toFixed(2)
    }

    const submitTransaction = async () => {
      try {
        if (!form.value.quantity || !form.value.amount) {
          alert('Veuillez remplir tous les champs')
          return
        }

        // Send to API
        const response = await api.post('/wallets/sell', {
          cryptocurrency_id: parseInt(form.value.crypto),
          quantity: parseFloat(form.value.quantity)
        })

        alert('Transaction confirmée! Email de confirmation envoyé.')
        // Redirect to history page instead of transactions
        router.push('/history')
      } catch (e) {
        console.error('Transaction error:', e)
        alert('Erreur lors de la transaction')
      }
    }

    watch(() => form.value.crypto, () => {
      const selectedCrypto = cryptoOptions.value.find(c => c.id.toString() === form.value.crypto)
      if (selectedCrypto) {
        form.value.currentPrice = selectedCrypto.current_price?.toString() || '0'
        form.value.quantity = ''
        form.value.amount = ''
        form.value.fees = ''
        form.value.total = ''
      }
    })

    onMounted(loadData)

    return {
      userName,
      form,
      cryptoOptions,
      logout,
      calculateTotal,
      calculateQuantity,
      submitTransaction
    }

    return {
      userName,
      form,
      logout,
      calculateTotal,
      calculateQuantity,
      submitTransaction
    }
  }
})
</script>
