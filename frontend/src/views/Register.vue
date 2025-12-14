<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#E6F0FF] to-[#F3E8FF]">
    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
      <div class="flex items-center justify-center">
        <img src="/assets/signup.png" alt="signup" class="max-w-full h-64 object-contain" />
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Créer un compte</h2>
        <form @submit.prevent="submit">
          <div class="mb-3">
            <input v-model="name" placeholder="Nom complet" required class="w-full border px-3 py-2 rounded" />
            <div v-if="errors?.name" class="text-red-600 text-sm mt-1">{{ errors.name.join(' ') }}</div>
          </div>
          <div class="mb-3">
            <input v-model="email" type="email" placeholder="Email" required class="w-full border px-3 py-2 rounded" />
            <div v-if="errors?.email" class="text-red-600 text-sm mt-1">{{ errors.email.join(' ') }}</div>
          </div>
          <div class="mb-3">
            <input v-model="password" type="password" placeholder="Mot de passe" required class="w-full border px-3 py-2 rounded" />
            <div v-if="errors?.password" class="text-red-600 text-sm mt-1">{{ errors.password.join(' ') }}</div>
          </div>
          <div class="mb-3">
            <input v-model="password_confirmation" type="password" placeholder="Confirmer le mot de passe" required class="w-full border px-3 py-2 rounded" />
            <div v-if="errors?.password_confirmation" class="text-red-600 text-sm mt-1">{{ errors.password_confirmation.join(' ') }}</div>
          </div>
          <div v-if="error" class="text-red-600 mb-2">{{ error }}</div>
          <div v-if="success" class="text-green-600 mb-2">{{ success }}</div>
          <button :disabled="isLoading" :class="{ 'opacity-50 cursor-not-allowed': isLoading }" class="w-full btn-accent">
            {{ isLoading ? 'Création en cours...' : 'Créer un compte' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '../services/auth'
import { createRegistrationRequest } from '../services/registrationApi'
import { formatApiError, isValidationError, getFieldError } from '../services/errorHandler'

export default defineComponent({
  setup() {
    const router = useRouter()
    const name = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')
    const error = ref('')
    const success = ref('')
    const isLoading = ref(false)
    const errors = ref<Record<string, string[] | undefined> | null>(null)

    const submit = async () => {
      if (isLoading.value) return

      error.value = ''
      success.value = ''
      errors.value = null

      // Validate password match
      if (password.value !== password_confirmation.value) {
        error.value = 'Les mots de passe ne correspondent pas.'
        return
      }

      // Validate password length
      if (password.value.length < 6) {
        error.value = 'Le mot de passe doit contenir au moins 6 caractères.'
        return
      }

      isLoading.value = true

      try {
        // Registration request with automatic client role
        const response = await createRegistrationRequest({
          name: name.value,
          email: email.value,
          password: password.value,
          password_confirmation: password_confirmation.value,
          role: 'client' // Automatically set to client
        })

        success.value = 'Demande d\'inscription créée avec succès! En attente d\'approbation admin.'
        setTimeout(() => router.push({ name: 'Login' }), 2000)
      } catch (err: any) {
        const apiError = formatApiError(err)
        error.value = apiError.message || 'Erreur lors de l\'inscription.'
        if (isValidationError(err)) {
          errors.value = apiError.errors
        }
      } finally {
        isLoading.value = false
      }
    }

    return { name, email, password, password_confirmation, error, success, errors, isLoading, submit }
  }
})
</script>
