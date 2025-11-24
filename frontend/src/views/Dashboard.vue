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
              <!-- Stat Cards -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Solde Disponible -->
                <div class="bg-white rounded-lg shadow-card p-6 border-l-4 border-primary">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-gray-500 text-sm mb-2">💰 Solde Disponible</p>
                      <p class="text-3xl font-bold text-primary">{{ availableBalance }}</p>
                    </div>
                  </div>
                </div>

                <!-- Portfolio Value -->
                <div class="bg-white rounded-lg shadow-card p-6 border-l-4 border-accent">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-gray-500 text-sm mb-2">📊 Valeur Portefeuille</p>
                      <p class="text-3xl font-bold text-accent">{{ portfolioValue }}</p>
                    </div>
                  </div>
                </div>

                <!-- Global Gain/Loss -->
                <div
                  :class="[
                    'bg-white rounded-lg shadow-card p-6 border-l-4',
                    globalGainPercentage >= 0 ? 'border-success' : 'border-danger'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-gray-500 text-sm mb-2">📈 Gain/Perte Global</p>
                      <p
                        :class="[
                          'text-3xl font-bold',
                          globalGainPercentage >= 0 ? 'text-success' : 'text-danger'
                        ]"
                      >
                        {{ globalGainPercentage >= 0 ? '+' : '' }}{{ globalGainPercentage }}%
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Cryptos Held -->
                <div class="bg-white rounded-lg shadow-card p-6 border-l-4 border-purple-500">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-gray-500 text-sm mb-2">💎 Cryptos Détenues</p>
                      <p class="text-3xl font-bold text-purple-600">{{ holdingsCount }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Market Chart Section -->
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-card p-6">
                  <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-secondary">Graphique Marché - Bitcoin</h3>
                    <div class="flex gap-2">
                      <button
                        v-for="period in ['24h', '7j', '30j']"
                        :key="period"
                        @click="selectedPeriod = period"
                        :class="[
                          'px-3 py-1 rounded-lg text-sm font-medium transition',
                          selectedPeriod === period
                            ? 'bg-primary text-white'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                        ]"
                      >
                        {{ period }}
                      </button>
                    </div>
                  </div>
                  <MarketChart :period="selectedPeriod" :data="chartData" :labels="chartLabels" :height="300" />
                </div>

                <!-- Market Stats -->
                <div class="bg-white rounded-lg shadow-card p-6">
                  <h3 class="text-lg font-bold text-secondary mb-4">Infos Marché</h3>
                  <div class="space-y-4">
                    <div>
                      <p class="text-gray-500 text-sm">Prix Actuel</p>
                      <p class="text-2xl font-bold text-primary">{{ currentPrice }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 text-sm">24h High</p>
                      <p class="text-lg font-semibold text-success">{{ high24h }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 text-sm">24h Low</p>
                      <p class="text-lg font-semibold text-danger">{{ low24h }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 text-sm">Volume 24h</p>
                      <p class="text-lg font-semibold text-gray-700">{{ volume24h }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Holdings Table -->
              <div class="bg-white rounded-lg shadow-card p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                  <h3 class="text-lg font-bold text-secondary">Positions Actuelles</h3>
                  <button
                    @click="$router.push('/market')"
                    class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition"
                  >
                    + Acheter des Cryptos
                  </button>
                </div>

                <!-- Holdings Table -->
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead>
                      <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Crypto</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Quantité</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Prix d'Achat</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Prix Actuel</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Valeur Totale</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Gain/Perte</th>
                        <th class="text-center py-3 px-4 font-bold text-gray-700">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="holding in holdings" :key="holding.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-4 px-4 font-semibold text-gray-800">{{ holding.crypto_name }}</td>
                        <td class="text-right py-4 px-4 text-gray-700">{{ holding.quantity }}</td>
                        <td class="text-right py-4 px-4 text-gray-700">{{ holding.purchase_price }} DT</td>
                        <td class="text-right py-4 px-4 text-gray-700">{{ holding.current_price }} DT</td>
                        <td class="text-right py-4 px-4 font-semibold text-gray-800">{{ holding.total_value }} DT</td>
                        <td
                          :class="[
                            'text-right py-4 px-4 font-bold',
                            parseFloat(holding.gain_loss) >= 0 ? 'text-success' : 'text-danger'
                          ]"
                        >
                          {{ holding.gain_loss }}%
                        </td>
                        <td class="text-center py-4 px-4">
                          <button
                            @click="manageHolding(holding)"
                            class="px-3 py-1 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition"
                          >
                            Gérer
                          </button>
                        </td>
                      </tr>
                      <tr v-if="holdings.length === 0" class="border-b border-gray-100">
                        <td colspan="7" class="py-8 text-center text-gray-500">
                          Aucune position. <router-link to="/market" class="text-primary font-bold hover:underline">Acheter maintenant</router-link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </main>
          </div>
  </div>
</template>

<script lang="ts">
        import { defineComponent, ref, onMounted } from 'vue'
        import ClientSidebar from '../components/ClientSidebar.vue'
        import MarketChart from '../components/MarketChart.vue'
        import api from '../services/api'
        import { useRouter } from 'vue-router'

        export default defineComponent({
          components: { ClientSidebar, MarketChart },
          setup() {
            const router = useRouter()
            const user = ref<any>(null)
            const userName = ref('Utilisateur')
            const availableBalance = ref('500,03 DT')
            const portfolioValue = ref('4 583,90 DT')
            const globalGainPercentage = ref(5.2)
            const holdingsCount = ref(0)
            const currentPrice = ref('90 000 DT')
            const high24h = ref('91 200 DT')
            const low24h = ref('88 500 DT')
            const volume24h = ref('120M DT')
            const selectedPeriod = ref('24h')
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
            
            const holdings = ref<any[]>([
              {
                id: 1,
                crypto_name: 'Bitcoin',
                quantity: '0.002',
                purchase_price: '88 225',
                current_price: '90 000',
                total_value: '180',
                gain_loss: '+2.01'
              },
              {
                id: 2,
                crypto_name: 'Ethereum',
                quantity: '0.5',
                purchase_price: '6 800',
                current_price: '7 000',
                total_value: '3 500',
                gain_loss: '+1.5'
              }
            ])

            const loadData = async () => {
              try {
                const profileRes = await api.get('/auth/profile')
                if (profileRes.data) {
                  user.value = profileRes.data
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
              
              holdingsCount.value = holdings.value.length
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

            const manageHolding = (holding: any) => {
              router.push(`/crypto/${holding.id}`)
            }

            onMounted(loadData)

            return {
              userName,
              availableBalance,
              portfolioValue,
              globalGainPercentage,
              holdingsCount,
              currentPrice,
              high24h,
              low24h,
              volume24h,
              selectedPeriod,
              chartData,
              chartLabels,
              holdings,
              logout,
              manageHolding
            }
          }
        })
</script>
