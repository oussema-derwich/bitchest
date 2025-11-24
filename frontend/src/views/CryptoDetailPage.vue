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
        <!-- Back Button -->
        <button
          @click="$router.back()"
          class="mb-6 px-4 py-2 bg-gray-400 text-white rounded-lg font-medium hover:opacity-90 transition"
        >
          ← Retour
        </button>

        <!-- Crypto Header -->
        <div class="bg-white rounded-lg shadow-card p-6 mb-6">
          <div class="flex justify-between items-start mb-6">
            <div>
              <div class="flex items-center gap-3 mb-4">
                <CryptoLogo :name="cryptoSymbol" size="lg" />
                <div>
                  <h2 class="text-3xl font-bold text-secondary">{{ cryptoName }}</h2>
                  <p class="text-gray-500">{{ cryptoSymbol }}</p>
                </div>
              </div>
            </div>
            <div class="text-right">
              <p class="text-4xl font-bold text-primary mb-2">{{ currentPrice }} DT</p>
              <p
                :class="[
                  'text-lg font-bold',
                  priceChange >= 0 ? 'text-success' : 'text-danger'
                ]"
              >
                {{ priceChange >= 0 ? '+' : '' }}{{ priceChange }}%
              </p>
            </div>
          </div>

          <!-- Timeframe Buttons -->
          <div class="flex gap-2 mb-6">
            <button
              v-for="period in ['7j', '30j', '90j']"
              :key="period"
              @click="selectedPeriod = period"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition',
                selectedPeriod === period
                  ? 'bg-primary text-white'
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              {{ period }}
            </button>
          </div>

          <!-- Chart -->
          <MarketChart :period="selectedPeriod" :data="chartData" :labels="chartLabels" :height="300" />
        </div>

        <!-- Market Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-card p-6">
            <p class="text-gray-500 text-sm mb-2">Volume 24h</p>
            <p class="text-2xl font-bold text-primary">{{ volume24h }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-card p-6">
            <p class="text-gray-500 text-sm mb-2">Market Cap</p>
            <p class="text-2xl font-bold text-accent">{{ marketCap }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-card p-6">
            <p class="text-gray-500 text-sm mb-2">Offre en Circulation</p>
            <p class="text-2xl font-bold text-purple-600">{{ circulatingSupply }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-card p-6">
          <div class="flex gap-4">
            <button
              @click="buyCrypto"
              class="flex-1 px-6 py-3 bg-success text-white rounded-lg font-bold hover:opacity-90 transition"
            >
              ✓ Acheter
            </button>
            <button
              @click="sellCrypto"
              class="flex-1 px-6 py-3 bg-danger text-white rounded-lg font-bold hover:opacity-90 transition"
            >
              ✕ Vendre
            </button>
            <button
              @click="createAlert"
              class="flex-1 px-6 py-3 bg-accent text-white rounded-lg font-bold hover:opacity-90 transition"
            >
              🔔 Alerte
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue'
import ClientSidebar from '../components/ClientSidebar.vue'
import CryptoLogo from '../components/CryptoLogo.vue'
import MarketChart from '../components/MarketChart.vue'
import api from '../services/api'
import { useRouter } from 'vue-router'

export default defineComponent({
  components: { ClientSidebar, CryptoLogo, MarketChart },
  setup() {
    const router = useRouter()
    const userName = ref('Utilisateur')
    const selectedPeriod = ref('7j')
    const chartData = ref<any[]>([])
    const chartLabels = ref<string[]>([])

    const calculateOHLC = (data: any[]) => {
      const grouped: any = {}
      data.forEach((item: any) => {
        const date = new Date(item.timestamp * 1000)
        const hour = Math.floor(date.getTime() / (60 * 60 * 1000)) * (60 * 60 * 1000)
        const key = new Date(hour).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
        if (!grouped[key]) {
          grouped[key] = { prices: [] }
        }
        grouped[key].prices.push(parseFloat(item.price) || 0)
      })
      return Object.entries(grouped).map(([label, group]: any) => {
        const prices = group.prices.sort((a: number, b: number) => a - b)
        return {
          x: label,
          o: prices[0],
          h: Math.max(...prices),
          l: Math.min(...prices),
          c: prices[prices.length - 1]
        }
      })
    }

    // Mock crypto data
    const cryptoName = ref('Bitcoin')
    const cryptoSymbol = ref('BTC')
    const currentPrice = ref('82 250')
    const priceChange = ref(3.72)
    const volume24h = ref('120M DT')
    const marketCap = ref('1,620B DT')
    const circulatingSupply = ref('21M BTC')

    const loadData = async () => {
      try {
        const profileRes = await api.get('/auth/profile')
        if (profileRes.data) {
          userName.value = profileRes.data.name || 'Utilisateur'
        }
      } catch (e) {
        console.error('Error loading profile:', e)
      }

      // Load Bitcoin history
      try {
        const historyRes = await api.get('/cryptocurrencies/1/history')
        if (historyRes.data?.data?.history) {
          const historyObj = historyRes.data.data.history
          const historyArray = Object.values(historyObj).sort((a: any, b: any) => a.timestamp - b.timestamp)
          const ohlcData = calculateOHLC(historyArray)
          
          chartData.value = ohlcData
          chartLabels.value = ohlcData.map((d: any) => d.x)
        }
      } catch (e) {
        console.error('Error loading chart data:', e)
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

    const buyCrypto = () => {
      router.push('/buy')
    }

    const sellCrypto = () => {
      router.push('/sell')
    }

    const createAlert = () => {
      alert('Créer une alerte pour ' + cryptoName.value)
      router.push('/alerts')
    }

    onMounted(loadData)

    return {
      userName,
      selectedPeriod,
      cryptoName,
      cryptoSymbol,
      currentPrice,
      priceChange,
      volume24h,
      marketCap,
      circulatingSupply,
      chartData,
      chartLabels,
      logout,
      buyCrypto,
      sellCrypto,
      createAlert
    }
  }
})
</script>
