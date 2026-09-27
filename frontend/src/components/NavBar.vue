<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const menuOpen = ref(false)
const userMenuOpen = ref(false)

async function handleLogout() {
  menuOpen.value = false
  userMenuOpen.value = false
  await authStore.logout()
  router.push('/login')
}

function closeMenu() {
  menuOpen.value = false
  userMenuOpen.value = false
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

      <!-- Menu desktop -->
      <div class="hidden sm:flex items-center gap-6">
        <template v-if="authStore.isAuthenticated()">
          <RouterLink
            to="/events/create"
            @click="closeMenu"
            class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-full hover:bg-brand-800 transition-colors flex items-center gap-1"
          >
            <i class="ti ti-plus"></i> Créer un évènement
          </RouterLink>

          <!-- Menu utilisateur déroulant -->
          <div class="relative">
            <button
              @click="userMenuOpen = !userMenuOpen"
              class="text-gray-700 text-sm hover:text-brand-900 transition-colors flex items-center gap-1"
              :aria-expanded="userMenuOpen"
              aria-haspopup="true"
            >
              <i class="ti ti-user-circle text-lg"></i>
              {{ authStore.user?.name }}
              <i
                :class="userMenuOpen ? 'ti ti-chevron-up' : 'ti ti-chevron-down'"
                class="text-xs"
              ></i>
            </button>

            <!-- Zone invisible qui ferme le menu au clic à l'extérieur -->
            <div
              v-if="userMenuOpen"
              class="fixed inset-0 z-[1050]"
              @click="userMenuOpen = false"
            ></div>

            <div
              v-if="userMenuOpen"
              class="absolute right-0 top-full mt-2 w-52 bg-white border border-gray-100 rounded-xl shadow-lg py-2 z-[1100]"
            >
              <RouterLink
                to="/profile"
                @click="closeMenu"
                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-900 transition-colors"
              >
                <i class="ti ti-user"></i> Mon profil
              </RouterLink>
              <RouterLink
                to="/my-events"
                @click="closeMenu"
                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-900 transition-colors"
              >
                <i class="ti ti-calendar-event"></i> Mes évènements
              </RouterLink>
              <RouterLink
                to="/favorites"
                @click="closeMenu"
                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-900 transition-colors"
              >
                <i class="ti ti-heart"></i> Mes favoris
              </RouterLink>
              <div class="border-t border-gray-100 my-2"></div>
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-accent-600 transition-colors"
              >
                <i class="ti ti-logout"></i> Se déconnecter
              </button>
            </div>
          </div>
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

      <!-- Bouton burger mobile -->
      <button
        @click="menuOpen = !menuOpen"
        class="sm:hidden text-gray-700 text-2xl"
        aria-label="Menu"
      >
        <i :class="menuOpen ? 'ti ti-x' : 'ti ti-menu-2'"></i>
      </button>
    </div>

    <!-- Menu mobile déroulant -->
    <div v-if="menuOpen" class="sm:hidden mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4">
      <template v-if="authStore.isAuthenticated()">
        <RouterLink
          to="/events/create"
          @click="closeMenu"
          class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-full hover:bg-brand-800 transition-colors flex items-center justify-center gap-2"
        >
          <i class="ti ti-plus"></i> Créer un évènement
        </RouterLink>
        <RouterLink
          to="/my-events"
          @click="closeMenu"
          class="text-gray-600 text-sm hover:text-brand-900 transition-colors flex items-center gap-2"
        >
          <i class="ti ti-calendar-event"></i> Mes évènements
        </RouterLink>
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
