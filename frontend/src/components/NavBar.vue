<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const menuOpen = ref(false) // Contrôle l'ouverture du menu burger sur mobile

async function handleLogout() {
  menuOpen.value = false // Ferme le menu mobile avant de déconnecter
  await authStore.logout()
  router.push('/login')
}

function closeMenu() {
  menuOpen.value = false
}
</script>

<template>
  <nav class="bg-white border-b border-gray-100 px-6 py-4 relative">
    <div class="flex items-center justify-between">
      <RouterLink
        to="/"
        @click="closeMenu"
        class="text-brand-900 font-semibold text-xl flex items-center gap-2"
      >
        <i class="ti ti-map-pin"></i> EventMap
      </RouterLink>

      <!-- Menu desktop : liens conditionnels selon l'état d'authentification -->
      <div class="hidden sm:flex items-center gap-6">
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
            <!-- Affiche le prénom de l'utilisateur connecté comme lien vers le profil -->
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

      <!-- Bouton burger : visible uniquement sur mobile -->
      <button
        @click="menuOpen = !menuOpen"
        class="sm:hidden text-gray-700 text-2xl"
        aria-label="Menu"
      >
        <!-- Icône change selon l'état du menu -->
        <i :class="menuOpen ? 'ti ti-x' : 'ti ti-menu-2'"></i>
      </button>
    </div>

    <!-- Menu mobile déroulant -->
    <div v-if="menuOpen" class="sm:hidden mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4">
      <template v-if="authStore.isAuthenticated()">
        <RouterLink
          to="/favorites"
          @click="closeMenu"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors flex items-center gap-2"
        >
          <i class="ti ti-heart"></i> Favoris
        </RouterLink>
        <RouterLink
          to="/profile"
          @click="closeMenu"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors flex items-center gap-2"
        >
          <i class="ti ti-user"></i> {{ authStore.user?.name }}
        </RouterLink>
        <button
          @click="handleLogout"
          class="text-left text-gray-600 text-sm hover:text-brand-900 transition-colors"
        >
          Se déconnecter
        </button>
      </template>
      <template v-else>
        <RouterLink
          to="/login"
          @click="closeMenu"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors"
        >
          Se connecter
        </RouterLink>
        <RouterLink
          to="/register"
          @click="closeMenu"
          class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-full hover:bg-brand-800 transition-colors text-center"
        >
          S'inscrire
        </RouterLink>
      </template>
    </div>
  </nav>
</template>

<style scoped></style>
