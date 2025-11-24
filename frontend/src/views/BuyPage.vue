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
          <h2 class="text-3xl font-bold text-secondary mb-8">✓ Acheter une Cryptomonnaie</h2>

          <form @submit.prevent="submitTransaction" class="space-y-6">
            <!-- Crypto Selection -->
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Crypto</label>
              <select
                v-model="form.crypto"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base font-medium focus:border-primary focus:outline-none transition"
              >
                <option value="Bitcoin">Bitcoin</option>
                <option value="Ethereum">Ethereum</option>
                <option value="Cardano">Cardano</option>
              </select>
            </div>

            <!-- Quantity or Amount -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Quantité</label>
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
                <label class="block text-sm font-bold text-gray-700 mb-2">Montant</label>
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
              <label class="block text-sm font-bold text-gray-700 mb-2">Total (DT)</label>
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
import { defineComponent, ref, onMounted } from 'vue'
import ClientSidebar from '../components/ClientSidebar.vue'
import api from '../services/api'
import { useRouter } from 'vue-router'

export default defineComponent({
  components: { ClientSidebar },
  setup() {
    const router = useRouter()
    const userName = ref('Utilisateur')

    const form = ref({
      crypto: 'Bitcoin',
      quantity: '',
      amount: '',
      currentPrice: '82 250',
      fees: '',
      total: ''
    })

    const loadData = async () => {
      try {
        const profileRes = await api.get('/auth/profile')
        if (profileRes.data) {
          userName.value = profileRes.data.name || 'Utilisateur'
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
      const total = subtotal + feesAmount

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
      const total = subtotal + feesAmount

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
        const response = await api.post('/wallets/buy', {
          crypto: form.value.crypto,
          quantity: parseFloat(form.value.quantity),
          amount: parseFloat(form.value.amount),
          fees: parseFloat(form.value.fees)
        })

        alert('Transaction confirmée! Email de confirmation envoyé.')
        router.push('/transactions')
      } catch (e) {
        console.error('Transaction error:', e)
        alert('Erreur lors de la transaction')
      }
    }

    onMounted(loadData)

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
