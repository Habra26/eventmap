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
    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all cursor-pointer relative"
    :class="eventsStore.selectedEventId === event.id ? 'ring-2 ring-brand-600' : ''"
  >
    <!-- Bouton favori -->
    <button
      @click.stop="toggleFavorite"
      class="absolute top-3 right-3 z-10 bg-white/90 backdrop-blur rounded-full p-2 shadow hover:scale-110 transition-transform"
    >
      <i v-if="isFav" class="ti ti-heart text-xl text-accent-500"></i>
      <i v-else class="ti ti-heart text-xl text-gray-400"></i>
    </button>

    <img
      v-if="event.image_url"
      :src="event.image_url"
      :alt="event.title"
      class="w-full h-44 object-cover"
    />
    <div v-else class="w-full h-44 bg-brand-50 flex items-center justify-center">
      <i class="ti ti-calendar-event text-4xl text-brand-300"></i>
    </div>

    <div class="p-4">
      <p class="font-semibold text-gray-900 truncate">{{ event.title }}</p>
      <p class="text-sm text-gray-500 mt-2 flex items-center gap-1.5">
        <i class="ti ti-map-pin text-brand-600"></i> {{ event.city ?? 'Ville inconnue' }}
      </p>
      <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
        <i class="ti ti-calendar text-brand-600"></i> {{ event.date ?? 'Date inconnue' }}
      </p>
      <p v-if="event.sub_events?.length > 1" class="text-xs text-gray-400 mt-2">
        {{ event.sub_events.length }} dates disponibles
      </p>
      <RouterLink
        :to="{ name: 'event', params: { id: event.id } }"
        class="mt-3 inline-block text-sm font-medium text-brand-700 hover:text-brand-900 transition-colors"
      >
        Voir le détail →
      </RouterLink>
    </div>
  </div>
</template>

<style scoped></style>