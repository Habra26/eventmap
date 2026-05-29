<script setup>
import { computed } from 'vue'
import { useEventsStore } from '@/stores/events'
import { useFavoritesStore } from '@/stores/favorites'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toasts'
import { useRouter } from 'vue-router'

const eventsStore = useEventsStore()
const favoritesStore = useFavoritesStore()
const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()

const props = defineProps({
  event: {
    type: Object,
    required: true,
  },
})

const isFav = computed(() => favoritesStore.isFavorite(props.event.id))

async function toggleFavorite() {
  if (!authStore.isAuthenticated()) {
    router.push({ name: 'login' })
    return
  }
  try {
    if (isFav.value) {
      await favoritesStore.removeFavorite(props.event.id)
      toastStore.success('Retiré des favoris')
    } else {
      await favoritesStore.addFavorite(props.event.id)
      toastStore.success('Ajouté aux favoris')
    }
  } catch (e) {
    toastStore.error('Une erreur est survenue')
  }
}
</script>

<template>
  <div
    @click="eventsStore.selectEvent(event.id)"
    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow cursor-pointer relative"
    :class="eventsStore.selectedEventId === event.id ? 'ring-2 ring-blue-500' : ''"
  >
    <!-- Bouton favori -->
    <button
      @click.stop="toggleFavorite"
      class="absolute top-2 right-2 z-10 bg-white rounded-full p-1.5 shadow hover:scale-110 transition-transform"
    >
      <i v-if="isFav" class="ti ti-heart text-xl text-red-500"></i>
      <i v-else class="ti ti-heart text-xl text-gray-400"></i>
    </button>

    <img
      v-if="event.image_url"
      :src="event.image_url"
      :alt="event.title"
      class="w-full h-40 object-cover"
    />
    <div v-else class="w-full h-40 bg-blue-50 flex items-center justify-center">
      <i class="ti ti-calendar-event text-4xl text-blue-300"></i>
    </div>

    <div class="p-4">
      <p class="font-medium text-gray-800 truncate">{{ event.title }}</p>
      <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <i class="ti ti-map-pin text-blue-600"></i> {{ event.city ?? 'Ville inconnue' }}
      </p>
      <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <i class="ti ti-calendar text-blue-600"></i> {{ event.date ?? 'Date inconnue' }}
      </p>
      <RouterLink
        :to="{ name: 'event', params: { id: event.id } }"
        class="mt-3 inline-block text-sm text-blue-700 hover:underline"
      >
        Voir le détail →
      </RouterLink>
    </div>
  </div>
</template>

<style scoped></style>
