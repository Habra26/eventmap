<script setup>
import { onMounted } from 'vue'
import { useEventsStore } from '@/stores/events'
import EventCard from '@/components/EventCard.vue'

const eventsStore = useEventsStore()

onMounted(() => {
  eventsStore.fetchEvents()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-medium mb-6">Évènements en Belgique</h1>

    <div v-if="eventsStore.loading" class="text-center text-gray-500 py-12">
      <i class="ti ti-loader text-3xl animate-spin"></i>
      <p class="mt-2">Chargement des évènements...</p>
    </div>

    <div v-else-if="eventsStore.error" class="text-center text-red-500 py-12">
      <i class="ti ti-alert-circle text-3xl"></i>
      <p class="mt-2">{{ eventsStore.error }}</p>
    </div>

    <div v-else-if="eventsStore.events.length === 0" class="text-center text-gray-500 py-12">
      <i class="ti ti-calendar-off text-3xl"></i>
      <p class="mt-2">Aucun évènement trouvé</p>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <EventCard v-for="event in eventsStore.events" :key="event.id" :event="event" />
    </div>
  </div>
</template>

<style scoped></style>
