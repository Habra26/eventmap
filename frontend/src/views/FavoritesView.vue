<script setup>
import { onMounted } from 'vue'
import { useFavoritesStore } from '@/stores/favorites'
import EventCard from '@/components/EventCard.vue'
import Loader from '@/components/Loader.vue'

const favoritesStore = useFavoritesStore()

onMounted(() => {
  favoritesStore.fetchFavorites()
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-medium mb-6">Mes favoris</h1>

    <Loader v-if="favoritesStore.loading" message="Chargement..." />

    <div v-else-if="favoritesStore.favorites.length === 0" class="text-center text-gray-500 py-12">
      <i class="ti ti-heart text-3xl"></i>
      <p class="mt-2">Aucun favori pour l'instant</p>
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
