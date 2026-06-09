<script setup>
import { onMounted } from 'vue'
import { useFavoritesStore } from '@/stores/favorites'
import EventCard from '@/components/EventCard.vue'
import Loader from '@/components/Loader.vue'

const favoritesStore = useFavoritesStore()

// Recharge les favoris à chaque visite de la page pour être sûr d'avoir les données à jour
onMounted(() => {
  favoritesStore.fetchFavorites()
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 py-8">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-brand-900">Mes favoris</h1>
      <p class="text-sm text-gray-500 mt-1">Retrouve les évènements que tu as sauvegardés</p>
    </div>

    <Loader v-if="favoritesStore.loading" message="Chargement..." />

    <!-- État vide : encourage l'utilisateur à explorer les événements -->
    <div
      v-else-if="favoritesStore.favorites.length === 0"
      class="text-center py-16 bg-brand-50 rounded-2xl"
    >
      <i class="ti ti-heart text-4xl text-brand-300"></i>
      <p class="mt-3 text-gray-600 font-medium">Aucun favori pour l'instant</p>
      <p class="text-sm text-gray-500 mt-1">Explore les évènements et ajoute tes préférés</p>
      <RouterLink
        to="/"
        class="inline-block mt-4 bg-brand-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors"
      >
        Découvrir les évènements
      </RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <EventCard
        v-for="event in favoritesStore.favorites"
        :key="event.id"
        :event="{ ...event, id: event.ticketmaster_id }"
      />
    </div>
  </div>
</template>

<style scoped></style>
