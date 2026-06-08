<script setup>
import { onMounted } from 'vue'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import ToastContainer from '@/components/ToastContainer.vue'
import { useFavoritesStore } from '@/stores/favorites'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()

// Charge les favoris au montage de l'application si l'utilisateur est déjà connecté (session persistée)
// Cela permet à isFavorite() de fonctionner immédiatement sans attendre une action utilisateur
onMounted(() => {
  if (authStore.isAuthenticated()) {
    favoritesStore.fetchFavorites()
  }
})
</script>

<template>
  <!-- Structure de base : la navbar et le footer sont présents sur toutes les pages via RouterView -->
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <main class="flex-1">
      <!-- RouterView affiche le composant de la route active -->
      <RouterView />
    </main>
    <AppFooter />
    <!-- ToastContainer est global et positionné en fixed — indépendant du contenu de la page -->
    <ToastContainer />
  </div>
</template>

<style scoped></style>
