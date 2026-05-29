<script setup>
import { onMounted } from 'vue'
import NavBar from '@/components/NavBar.vue'
import { useFavoritesStore } from '@/stores/favorites'
import { useAuthStore } from '@/stores/auth'
import ToastContainer from './components/ToastContainer.vue'

const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()

onMounted(() => {
  if (authStore.isAuthenticated) {
    favoritesStore.fetchFavorites()
  }
})
</script>

<template>
  <NavBar />
  <RouterView />
  <ToastContainer />
</template>

<style scoped></style>
