<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F3F4F6]">
    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
      <img src="/assets/bitchest_logo.png" alt="logo" class="h-12 mx-auto mb-4" />
      <h2 class="text-xl font-semibold mb-4 text-center">Se connecter</h2>
      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="block text-sm">Email</label>
          <input v-model="email" type="email" required class="w-full border px-3 py-2 rounded" />
        </div>
        <div class="mb-3">
          <label class="block text-sm">Mot de passe</label>
          <input v-model="password" type="password" required class="w-full border px-3 py-2 rounded" />
        </div>
        <div v-if="error" class="text-red-600 mb-3">{{ error }}</div>
        <button :disabled="isLoading" :class="{ 'opacity-50 cursor-not-allowed': isLoading }" class="w-full btn-primary">
          {{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import api from '../services/api'
import router from '../router'
import { setUser } from '../services/auth'

export default defineComponent({
  setup() {
    const email = ref('')
    const password = ref('')
    const error = ref('')
    const isLoading = ref(false)

    const submit = async () => {
      if (isLoading.value) return // Empêcher les clics multiples
      
      error.value = ''
      isLoading.value = true
      try {
        console.log('Tentative de connexion avec:', { email: email.value, password: password.value })
        const res = await api.post('/auth/login', { email: email.value, password: password.value })
        console.log('Réponse du serveur:', res.data)
        const token = res.data.access_token || res.data.token
        const user = res.data.user

        if (!token || !user) {
          throw new Error('Informations de connexion invalides')
        }

        setUser(user, token)

        // Redirection selon le rôle
        if (user.role === 'admin' || user.role === 'Administrator' || user.isAdmin) {
          router.push({ name: 'AdminDashboard' })
        } else {
          router.push({ name: 'Dashboard' })
        }
      } catch (err: any) {
        // Show server-provided message when available to help debug (404, validation, etc.)
        console.error('Erreur lors de la connexion:', err)
        const serverMessage = err?.response?.data?.message || err?.response?.data || err?.message
        error.value = serverMessage || 'Email ou mot de passe incorrect'
        isLoading.value = false
      }
    }

    return { email, password, error, isLoading, submit }
  }
})
</script>
