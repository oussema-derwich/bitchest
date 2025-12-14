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
import { useRouter } from 'vue-router'
import { login } from '../services/auth'
import { formatApiError, isValidationError } from '../services/errorHandler'

export default defineComponent({
  setup() {
    const router = useRouter()
    const email = ref('')
    const password = ref('')
    const error = ref('')
    const isLoading = ref(false)

    const submit = async () => {
      if (isLoading.value) return

      error.value = ''
      isLoading.value = true

      try {
        // Use new auth service
        const response = await login(email.value, password.value)

        // Ensure we have a valid user object
        if (!response.user) {
          throw new Error('Données utilisateur invalides')
        }

        // Redirect based on role
        const userRole = response.user.role || 'user'
        if (userRole === 'admin') {
          router.push({ name: 'AdminDashboard' })
        } else {
          router.push({ name: 'Dashboard' })
        }
      } catch (err: any) {
        // Format and display error
        const apiError = formatApiError(err)
        error.value = apiError.message || 'Erreur lors de la connexion'

        // Log validation errors if present
        if (isValidationError(err)) {
          console.log('Validation errors:', apiError.errors)
        }
      } finally {
        isLoading.value = false
      }
    }

    return { email, password, error, isLoading, submit }
  }
})
</script>
