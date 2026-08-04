<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/axios'
import { useFavoritesStore } from '@/stores/favorites'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toasts'
import Loader from '@/components/Loader.vue'
import ErrorMessage from '@/components/ErrorMessage.vue'

const route = useRoute()
const router = useRouter()
const favoritesStore = useFavoritesStore()
const authStore = useAuthStore()
const toastStore = useToastStore()

const event = ref(null)
const loading = ref(true)
const error = ref(null)

const isFav = computed(() => (event.value ? favoritesStore.isFavorite(event.value.id) : false))

onMounted(async () => {
  try {
    const response = await api.get(`/events/${route.params.id}`)
    event.value = response.data
  } catch (e) {
    error.value = 'Évènement introuvable'
  } finally {
    loading.value = false
  }
})

async function toggleFavorite() {
  if (!authStore.isAuthenticated()) {
    router.push({ name: 'login' })
    return
  }
  try {
    if (isFav.value) {
      await favoritesStore.removeFavorite(event.value.id)
      toastStore.success('Retiré des favoris')
    } else {
      await favoritesStore.addFavorite(event.value.id)
      toastStore.success('Ajouté aux favoris')
    }
  } catch (e) {
    toastStore.error('Une erreur est survenue')
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 py-8">
    <Loader v-if="loading" message="Chargement..." />

    <ErrorMessage v-else-if="error" :message="error" />

    <div v-else-if="event">
      <RouterLink
        to="/"
        class="text-sm text-brand-700 hover:text-brand-900 transition-colors flex items-center gap-1 mb-6"
      >
        <i class="ti ti-arrow-left"></i> Retour aux évènements
      </RouterLink>

      <div class="relative">
        <img
          v-if="event.image_url"
          :src="event.image_url"
          :alt="event.title"
          class="w-full h-72 object-cover rounded-2xl mb-6"
        />
        <div v-else class="w-full h-72 bg-brand-50 rounded-2xl mb-6 flex items-center justify-center">
          <i class="ti ti-calendar-event text-5xl text-brand-300"></i>
        </div>

        <button
          @click="toggleFavorite"
          class="absolute top-4 right-4 bg-white/90 backdrop-blur rounded-full p-3 shadow hover:scale-110 transition-transform"
        >
          <i v-if="isFav" class="ti ti-heart text-2xl text-accent-500"></i>
          <i v-else class="ti ti-heart text-2xl text-gray-400"></i>
        </button>
      </div>

      <span
        v-if="event.category"
        class="inline-block bg-brand-100 text-brand-800 text-xs font-medium px-3 py-1 rounded-full mb-3"
      >
        {{ event.category }}
      </span>

      <h1 class="text-3xl font-bold text-brand-900 mb-4">{{ event.title }}</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-map-pin text-brand-600"></i>
          <span>{{ event.city ?? 'Ville inconnue' }}</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-building text-brand-600"></i>
          <span>{{ event.venue ?? 'Lieu inconnu' }}</span>
        </div>
      </div>

      <p class="text-gray-600 leading-relaxed mb-6">
        {{ event.description ?? 'Aucune description disponible' }}
      </p>

      <div v-if="event.sub_events?.length" class="space-y-2 mb-6">
        <h2 class="text-lg font-semibold text-brand-900 mb-2">Tickets disponibles</h2>
        <a
          v-for="sub in event.sub_events"
          :key="sub.id"
          :href="sub.ticket_url"
          target="_blank"
          class="flex items-center justify-between bg-brand-50 hover:bg-brand-100 transition-colors rounded-xl px-4 py-3"
        >
          <div>
            <p class="font-medium text-gray-900">{{ sub.name }}</p>
            <p class="text-sm text-gray-500">
              {{ sub.date ?? 'Date inconnue' }}<span v-if="sub.time"> à {{ sub.time }}</span>
            </p>
          </div>
          <i class="ti ti-ticket text-brand-700 text-xl"></i>
        </a>
      </div>

      <p class="text-xs text-gray-400 mt-6">
        Données fournies par Ticketmaster
      </p>
    </div>
  </div>
</template>

<style scoped></style>