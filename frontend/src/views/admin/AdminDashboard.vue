<template>
  <AdminLayout pageTitle="Dashboard" pageDescription="Vue d'ensemble des statistiques de la plateforme">
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Active Users Card -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm mb-1">👤 Utilisateurs actifs</p>
            <p class="text-3xl font-bold text-blue-600">{{ stats.activeUsers }}</p>
            <p class="text-xs text-green-600 mt-2">↑ +{{ stats.newUsersThisWeek }} cette semaine</p>
          </div>
          <div class="text-4xl opacity-20">👥</div>
        </div>
      </div>

      <!-- Transaction Volume -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-emerald-500 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm mb-1">💰 Volume des transactions</p>
            <p class="text-3xl font-bold text-emerald-600">{{ formatCurrency(stats.transactionVolume) }}</p>
            <p class="text-xs text-green-600 mt-2">↑ {{ stats.volumeGrowth }}% vs semaine dernière</p>
          </div>
          <div class="text-4xl opacity-20">💳</div>
        </div>
      </div>

      <!-- Active Alerts -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm mb-1">⚠️ Alertes actives</p>
            <p class="text-3xl font-bold text-orange-600">{{ stats.activeAlerts }}</p>
            <p class="text-xs text-orange-600 mt-2">{{ stats.alertsTriggeredToday }} déclenchées aujourd'hui</p>
          </div>
          <div class="text-4xl opacity-20">🚨</div>
        </div>
      </div>

      <!-- Market Value -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm mb-1">💹 Valeur du marché global</p>
            <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(stats.totalMarketValue) }}</p>
            <p class="text-xs text-green-600 mt-2">↑ +2.8% dans les 24h</p>
          </div>
          <div class="text-4xl opacity-20">📈</div>
        </div>
      </div>
    </div>

    <!-- Charts and Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Transaction Volume Chart -->
      <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-bold text-gray-800">Évolution du volume des transactions</h3>
            <p class="text-sm text-gray-500">7 derniers jours</p>
          </div>
          <div class="flex gap-2">
            <button
              @click="chartPeriod = 7"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition',
                chartPeriod === 7 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              7j
            </button>
            <button
              @click="chartPeriod = 30"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition',
                chartPeriod === 30 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              30j
            </button>
          </div>
        </div>
        <div class="h-64 flex items-end justify-around gap-2">
          <div v-for="(value, index) in chartData" :key="index" class="flex-1 flex flex-col items-center">
            <div
              class="w-full rounded-t-lg transition hover:opacity-80"
              :style="{ height: (value / Math.max(...chartData)) * 100 + '%', backgroundColor: '#2563EB' }"
            ></div>
            <p class="text-xs text-gray-600 mt-2">{{ dayLabels[index] }}</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">Actions rapides</h3>
          <button class="w-full py-3 text-white rounded-lg font-medium transition mb-3 flex items-center justify-center gap-2" style="background-color: #2563EB" @mouseenter="$event.target.style.backgroundColor='#1e40af'" @mouseleave="$event.target.style.backgroundColor='#2563EB'">
            <span>🔄</span>
            <span>Actualiser statistiques</span>
          </button>
          <button class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition mb-3 flex items-center justify-center gap-2">
            <span>📄</span>
            <span>Exporter rapport</span>
          </button>
          <button class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition flex items-center justify-center gap-2">
            <span>⚙️</span>
            <span>Paramètres</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Activities Table -->
    <div class="bg-white rounded-xl shadow-md p-6">
      <h3 class="text-lg font-bold text-gray-800 mb-6">Dernières activités</h3>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b-2 border-gray-200">
              <th class="text-left py-3 px-4 text-gray-700 font-semibold">Date</th>
              <th class="text-left py-3 px-4 text-gray-700 font-semibold">Utilisateur</th>
              <th class="text-left py-3 px-4 text-gray-700 font-semibold">Action</th>
              <th class="text-left py-3 px-4 text-gray-700 font-semibold">Détail</th>
              <th class="text-left py-3 px-4 text-gray-700 font-semibold">Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="activity in recentActivities" :key="activity.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
              <td class="py-3 px-4 text-gray-600 text-sm">{{ activity.date }}</td>
              <td class="py-3 px-4">
                <span class="font-medium text-gray-800">{{ activity.user }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                  {{ activity.action }}
                </span>
              </td>
              <td class="py-3 px-4 text-gray-600 text-sm">{{ activity.detail }}</td>
              <td class="py-3 px-4">
                <span
                  :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-medium',
                    activity.status === 'Confirmé'
                      ? 'bg-green-100 text-green-800'
                      : activity.status === 'En attente'
                      ? 'bg-yellow-100 text-yellow-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ activity.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from './AdminLayout.vue'
import axios from 'axios'
import api from '@/services/api'

const chartPeriod = ref(7)
const dayLabels = ref(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'])
const chartData = ref([4500, 5200, 4800, 6100, 5900, 7200, 6800])

const stats = ref({
  activeUsers: 1247,
  newUsersThisWeek: 34,
  transactionVolume: 2450000,
  volumeGrowth: 15.3,
  activeAlerts: 856,
  alertsTriggeredToday: 12,
  totalMarketValue: 5800000
})

const recentActivities = ref([
  {
    id: 1,
    date: '12/11/2025',
    user: 'chedi01',
    action: 'Achat',
    detail: 'BTC - 0.002 à 88 000 DT',
    status: 'Confirmé'
  },
  {
    id: 2,
    date: '12/11/2025',
    user: 'Admin',
    action: 'Ajout',
    detail: 'Nouvelle crypto "ADA"',
    status: 'Confirmé'
  },
  {
    id: 3,
    date: '11/11/2025',
    user: 'mariem02',
    action: 'Vente',
    detail: 'ETH - 0.5 à 6 950 DT',
    status: 'Confirmé'
  },
  {
    id: 4,
    date: '11/11/2025',
    user: 'Admin',
    action: 'Modification',
    detail: 'Prix BTC ajusté à 88 000 DT',
    status: 'Confirmé'
  },
  {
    id: 5,
    date: '10/11/2025',
    user: 'system',
    action: 'Alerte',
    detail: 'Forte volatilité détectée',
    status: 'En attente'
  }
])

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'currency',
    currency: 'TND'
  }).format(value)
}

onMounted(async () => {
  try {
    // Charger les statistiques du backend
    const response = await api.get('/admin/stats')
    stats.value = response.data
  } catch (e) {
    console.error('Error loading stats:', e)
  }
})
</script>

<style scoped>
</style>
