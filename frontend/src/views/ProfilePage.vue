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
        <h2 class="text-3xl font-bold text-secondary mb-8">Mon Profil</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Profile Picture -->
          <div class="bg-white rounded-lg shadow-card p-6">
            <h3 class="text-lg font-bold text-secondary mb-4">Photo de Profil</h3>
            <div class="mb-4">
              <div class="w-full aspect-square bg-gray-200 rounded-lg flex items-center justify-center text-4xl mb-4">
                👤
              </div>
              <button
                @click="() => (fileInput as any)?.click()"
                class="w-full px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition mb-2"
              >
                📤 Uploader
              </button>
              <button
                @click="deletePhoto"
                class="w-full px-4 py-2 bg-danger text-white rounded-lg font-medium hover:opacity-90 transition"
              >
                🗑️ Supprimer
              </button>
              <input
                ref="fileInput"
                type="file"
                accept="image/png,image/jpeg"
                @change="uploadPhoto"
                class="hidden"
              />
            </div>
          </div>

          <!-- Edit Info -->
          <div class="lg:col-span-2 bg-white rounded-lg shadow-card p-6">
            <h3 class="text-lg font-bold text-secondary mb-6">Informations Personnelles</h3>

            <!-- Name -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom Complet</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg font-medium focus:border-primary focus:outline-none transition"
              />
            </div>

            <!-- Email -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg font-medium focus:border-primary focus:outline-none transition"
              />
            </div>

            <!-- Change Password Section -->
            <div class="border-t-2 border-gray-200 pt-6 mb-6">
              <h4 class="text-md font-bold text-secondary mb-4">Changer le Mot de Passe</h4>

              <!-- Old Password -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ancien Mot de Passe</label>
                <input
                  v-model="form.oldPassword"
                  type="password"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg font-medium focus:border-primary focus:outline-none transition"
                />
              </div>

              <!-- New Password -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau Mot de Passe</label>
                <input
                  v-model="form.newPassword"
                  type="password"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg font-medium focus:border-primary focus:outline-none transition"
                />
              </div>

              <!-- Confirm Password -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer Mot de Passe</label>
                <input
                  v-model="form.confirmPassword"
                  type="password"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg font-medium focus:border-primary focus:outline-none transition"
                />
              </div>
            </div>

            <!-- 2FA Section -->
            <div class="border-t-2 border-gray-200 pt-6 mb-6">
              <h4 class="text-md font-bold text-secondary mb-4">Authentification à Deux Facteurs (2FA)</h4>
              <p class="text-gray-600 text-sm mb-4">
                Sécurisez votre compte avec Google Authenticator
              </p>
              <button
                @click="setupTwoFactor"
                class="px-4 py-2 bg-accent text-white rounded-lg font-medium hover:opacity-90 transition"
              >
                🔐 Configurer 2FA
              </button>
            </div>

            <!-- Save Button -->
            <div class="flex gap-4">
              <button
                @click="saveProfile"
                class="px-6 py-3 bg-success text-white rounded-lg font-bold hover:opacity-90 transition"
              >
                ✓ Enregistrer
              </button>
              <button
                @click="resetForm"
                class="px-6 py-3 bg-gray-400 text-white rounded-lg font-bold hover:opacity-90 transition"
              >
                ↻ Réinitialiser
              </button>
            </div>
          </div>
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
import { currentUser, fetchUserProfile } from '../services/auth'

export default defineComponent({
  components: { ClientSidebar },
  setup() {
    const router = useRouter()
    const userName = ref(currentUser.value?.name || 'Utilisateur')
    const fileInput = ref<HTMLInputElement | null>(null)
    const form = ref({
      name: currentUser.value?.name || '',
      email: currentUser.value?.email || '',
      oldPassword: '',
      newPassword: '',
      confirmPassword: ''
    })

    const loadData = async () => {
      try {
        const user = await fetchUserProfile()
        if (user) {
          userName.value = user.name || 'Utilisateur'
          form.value.name = user.name
          form.value.email = user.email
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

    const uploadPhoto = (event: Event) => {
      const file = (event.target as HTMLInputElement).files?.[0]
      if (file) {
        console.log('Photo uploaded:', file.name)
        alert('Photo uploadée: ' + file.name)
      }
    }

    const uploadPhotoClick = () => {
      (fileInput.value as HTMLInputElement | null)?.click()
    }

    const deletePhoto = () => {
      console.log('Photo deleted')
      alert('Photo supprimée')
    }

    const saveProfile = async () => {
      try {
        // Validate form
        if (!form.value.name || !form.value.email) {
          alert('Veuillez remplir tous les champs requis')
          return
        }

        // If password change requested
        if (form.value.newPassword) {
          if (form.value.newPassword !== form.value.confirmPassword) {
            alert('Les mots de passe ne correspondent pas')
            return
          }
          if (!form.value.oldPassword) {
            alert('Veuillez entrer votre ancien mot de passe')
            return
          }
        }

        // Save to API
        const response = await api.put('/auth/profile', {
          name: form.value.name,
          email: form.value.email,
          password: form.value.newPassword || undefined
        })

        alert('Profil mis à jour avec succès')
        userName.value = form.value.name
        form.value.oldPassword = ''
        form.value.newPassword = ''
        form.value.confirmPassword = ''
      } catch (e) {
        console.error('Error saving profile:', e)
        alert('Erreur lors de la sauvegarde')
      }
    }

    const resetForm = () => {
      form.value.oldPassword = ''
      form.value.newPassword = ''
      form.value.confirmPassword = ''
      loadData()
    }

    const setupTwoFactor = () => {
      alert('Fonctionnalité 2FA à implémenter')
      // Would open a modal with QR code for Google Authenticator
    }

    onMounted(loadData)

    return {
      userName,
      fileInput,
      form,
      logout,
      uploadPhoto,
      deletePhoto,
      saveProfile,
      resetForm,
      setupTwoFactor
    }
  }
})
</script>
