<script setup>
import { onMounted } from 'vue'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import ToastContainer from '@/components/ToastContainer.vue'
import { useFavoritesStore } from '@/stores/favorites'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()

onMounted(() => {
  if (authStore.isAuthenticated()) {
    favoritesStore.fetchFavorites()
  }
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <main class="flex-1">
      <RouterView />
    </main>
    <AppFooter />
    <ToastContainer />
  </div>
</template>

<style scoped></style>
