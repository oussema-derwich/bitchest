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
        <h2 class="text-3xl font-bold text-secondary mb-8">Mon Portefeuille</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Donut Chart -->
          <div class="lg:col-span-2 bg-white rounded-lg shadow-card p-6">
            <h3 class="text-lg font-bold text-secondary mb-4">Répartition du Portefeuille</h3>
            <PortfolioDonutChart :data="chartData" />
          </div>

          <!-- Portfolio Stats -->
          <div class="bg-white rounded-lg shadow-card p-6">
            <h3 class="text-lg font-bold text-secondary mb-6">Résumé</h3>
            <div class="space-y-4">
              <div class="pb-4 border-b border-gray-200">
                <p class="text-gray-500 text-sm mb-1">Valeur Totale</p>
                <p class="text-3xl font-bold text-primary">4 583,90 DT</p>
              </div>
              <div class="pb-4 border-b border-gray-200">
                <p class="text-gray-500 text-sm mb-1">Gain/Perte Global</p>
                <p class="text-2xl font-bold text-success">+5.2%</p>
              </div>
              <div class="pb-4">
                <p class="text-gray-500 text-sm mb-1">Nombre d'Actifs</p>
                <p class="text-2xl font-bold text-accent">2</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="flex gap-2 mb-6">
          <button
            v-for="filter in ['Toutes', 'Gagnantes', 'Perdantes']"
            :key="filter"
            @click="selectedFilter = filter"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition',
              selectedFilter === filter
                ? 'bg-primary text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ filter }}
          </button>
        </div>

        <!-- Assets Table -->
        <div class="bg-white rounded-lg shadow-card p-6 mb-8">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-secondary">Actifs</h3>
            <div class="flex gap-2">
              <button
                @click="refreshData"
                class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition"
              >
                🔄 Actualiser
              </button>
              <button
                @click="$router.push('/market')"
                class="px-4 py-2 bg-accent text-white rounded-lg font-medium hover:opacity-90 transition"
              >
                + Acheter Plus
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b-2 border-gray-200">
                  <th class="text-left py-3 px-4 font-bold text-gray-700">Crypto</th>
                  <th class="text-right py-3 px-4 font-bold text-gray-700">Quantité</th>
                  <th class="text-right py-3 px-4 font-bold text-gray-700">Valeur Actuelle</th>
                  <th class="text-right py-3 px-4 font-bold text-gray-700">Variation</th>
                  <th class="text-center py-3 px-4 font-bold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="asset in filteredAssets" :key="asset.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                  <td class="py-4 px-4 font-semibold text-gray-800">{{ asset.name }}</td>
                  <td class="text-right py-4 px-4 text-gray-700">{{ asset.quantity }}</td>
                  <td class="text-right py-4 px-4 font-semibold text-gray-800">{{ asset.value }} DT</td>
                  <td
                    :class="[
                      'text-right py-4 px-4 font-bold',
                      parseFloat(asset.variation) >= 0 ? 'text-success' : 'text-danger'
                    ]"
                  >
                    {{ asset.variation }}%
                  </td>
                  <td class="text-center py-4 px-4 flex gap-2 justify-center">
                    <button
                      @click="viewDetails(asset)"
                      class="px-3 py-1 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition"
                    >
                      Détails
                    </button>
                    <button
                      @click="editAsset(asset)"
                      class="px-3 py-1 bg-gray-400 text-white rounded-lg text-sm font-medium hover:bg-gray-500 transition"
                    >
                      Gérer
                    </button>
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
import { defineComponent, ref, computed, onMounted } from 'vue'
import ClientSidebar from '../components/ClientSidebar.vue'
import PortfolioDonutChart from '../components/PortfolioDonutChart.vue'
import api from '../services/api'
import { useRouter } from 'vue-router'

interface Asset {
  id: number
  name: string
  quantity: string
  value: string
  variation: string
}

export default defineComponent({
  components: { ClientSidebar, PortfolioDonutChart },
  setup() {
    const router = useRouter()
    const userName = ref('Utilisateur')
    const selectedFilter = ref('Toutes')
    
    const chartData = ref([
      { name: 'Bitcoin', value: 180, color: '#FF6B35' },
      { name: 'Ethereum', value: 3500, color: '#004E89' }
    ])
    
    const assets = ref<Asset[]>([
      {
        id: 1,
        name: 'Bitcoin',
        quantity: '0.002',
        value: '180',
        variation: '+2.01'
      },
      {
        id: 2,
        name: 'Ethereum',
        quantity: '0.5',
        value: '3 500',
        variation: '+1.50'
      }
    ])

    const filteredAssets = computed(() => {
      return assets.value.filter((asset) => {
        if (selectedFilter.value === 'Gagnantes') {
          return parseFloat(asset.variation) >= 0
        } else if (selectedFilter.value === 'Perdantes') {
          return parseFloat(asset.variation) < 0
        }
        return true
      })
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

    const refreshData = () => {
      loadData()
    }

    const viewDetails = (asset: Asset) => {
      router.push(`/crypto-detail/${asset.id}`)
    }

    const editAsset = (asset: Asset) => {
      router.push(`/crypto-detail/${asset.id}`)
    }

    onMounted(loadData)

    return {
      userName,
      selectedFilter,
      assets,
      chartData,
      filteredAssets,
      logout,
      refreshData,
      viewDetails,
      editAsset
    }
  }
})
</script>
