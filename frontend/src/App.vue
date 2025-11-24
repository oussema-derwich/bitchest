<template>
  <div class="min-h-screen flex flex-col bg-white">
    <Navbar />
    <main class="flex-1 w-full">
      <router-view />
    </main>
    <div class="w-full border-t border-gray-200 mt-auto">
      <Footer />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from 'vue'
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
import { loadUserFromStorage } from './services/auth'

export default defineComponent({
  components: { Navbar, Footer },
  setup() {
    onMounted(() => {
      loadUserFromStorage()
    })
  }
})
</script>
