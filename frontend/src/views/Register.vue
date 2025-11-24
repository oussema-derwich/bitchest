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
          <button class="w-full btn-accent">Créer un compte</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import api from '../services/api'
import router from '../router'

export default defineComponent({
  setup() {
    const name = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')
  const error = ref('')
    const success = ref('')
  const errors = ref<Record<string, string[] | undefined> | null>(null)

    const submit = async () => {
      error.value = ''
      success.value = ''
      if (password.value !== password_confirmation.value) {
        error.value = 'Les mots de passe ne correspondent pas.'
        return
      }
      try {
        // reset previous errors
        errors.value = null
        await api.post('/auth/register', { name: name.value, email: email.value, password: password.value, password_confirmation: password_confirmation.value })
        success.value = 'Compte créé avec succès !'
        setTimeout(() => router.push({ name: 'Login' }), 1200)
      } catch (e: any) {
        const resp = e?.response?.data
        if (resp) {
          if (resp.errors) {
            // keep structured errors for per-field display
            errors.value = resp.errors
            // also set a general message
            error.value = resp.message || 'Erreur de validation.'
          } else {
            error.value = resp.message || 'Erreur lors de la création du compte.'
          }
        } else {
          error.value = 'Erreur lors de la création du compte.'
        }
        console.error('Register error', e)
      }
    }

    return { name, email, password, password_confirmation, error, success, errors, submit }
  }
})
</script>
