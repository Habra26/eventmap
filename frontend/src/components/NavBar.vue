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
  <nav class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
    <RouterLink to="/" class="text-brand-900 font-semibold text-xl flex items-center gap-2">
      <i class="ti ti-map-pin"></i> EventMap
    </RouterLink>

    <div class="flex items-center gap-6">
      <template v-if="authStore.isAuthenticated()">
        <RouterLink
          to="/favorites"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors flex items-center gap-1"
        >
          <i class="ti ti-heart"></i> Favoris
        </RouterLink>
        <RouterLink
          to="/profile"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors flex items-center gap-1"
        >
          <i class="ti ti-user"></i> {{ authStore.user?.name }}
        </RouterLink>
        <button
          @click="handleLogout"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors"
        >
          Se déconnecter
        </button>
      </template>
      <template v-else>
        <RouterLink
          to="/login"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors"
        >
          Se connecter
        </RouterLink>
        <RouterLink
          to="/register"
          class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-full hover:bg-brand-800 transition-colors"
        >
          S'inscrire
        </RouterLink>
      </template>
    </div>
  </nav>
</template>

<style scoped></style>
