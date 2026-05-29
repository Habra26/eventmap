<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <nav class="bg-blue-700 px-6 py-4 flex items-center justify-between">
    <RouterLink to="/" class="text-white font-medium text-lg flex items-center gap-2">
      <i class="ti ti-map-pin"></i> EventMap
    </RouterLink>

    <div class="flex items-center gap-4">
      <template v-if="authStore.isAuthenticated()">
        <RouterLink
          to="/favorites"
          class="text-blue-200 text-sm hover:text-white flex items-center gap-1"
        >
          <i class="ti ti-heart"></i> Favoris
        </RouterLink>
        <span class="text-blue-200 text-sm">{{ authStore.user?.name }}</span>
        <button
          @click="handleLogout"
          class="bg-blue-800 text-blue-100 text-sm px-4 py-2 rounded-lg hover:bg-blue-900 transition-colors"
        >
          Se déconnecter
        </button>
      </template>
      <template v-else>
        <RouterLink to="/login" class="text-blue-200 text-sm hover:text-white"
          >Se connecter</RouterLink
        >
        <RouterLink
          to="/register"
          class="bg-white text-blue-700 text-sm px-4 py-2 rounded-lg hover:bg-blue-50"
          >S'inscrire</RouterLink
        >
      </template>
    </div>
  </nav>
</template>

<style scoped></style>
