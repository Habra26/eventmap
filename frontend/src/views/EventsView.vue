<script setup>
import { onMounted, ref, nextTick } from 'vue'
import { useEventsStore } from '@/stores/events'
import EventCard from '@/components/EventCard.vue'
import MapView from '@/components/MapView.vue'
import SearchBar from '@/components/SearchBar.vue'
import Loader from '@/components/Loader.vue'
import ErrorMessage from '@/components/ErrorMessage.vue'

const eventsStore = useEventsStore()
const activeTab = ref('list')

onMounted(() => {
  eventsStore.fetchEvents()
})

function switchToMap() {
  activeTab.value = 'map'
  nextTick(() => window.dispatchEvent(new Event('resize')))
}
</script>

<template>
  <div class="flex flex-col h-[calc(100vh-64px)]">
    <!-- Onglets mobile -->
    <div class="flex lg:hidden border-b border-gray-200">
      <button
        @click="activeTab = 'list'"
        :class="
          activeTab === 'list' ? 'border-b-2 border-brand-900 text-brand-900' : 'text-gray-500'
        "
        class="flex-1 py-3 text-sm font-medium flex items-center justify-center gap-2"
      >
        <i class="ti ti-list"></i> Liste
      </button>
      <button
        @click="switchToMap"
        :class="
          activeTab === 'map' ? 'border-b-2 border-brand-900 text-brand-900' : 'text-gray-500'
        "
        class="flex-1 py-3 text-sm font-medium flex items-center justify-center gap-2"
      >
        <i class="ti ti-map"></i> Carte
      </button>
    </div>

    <div class="flex flex-1 overflow-hidden">
      <!-- Liste -->
      <div
        :class="activeTab === 'list' ? 'flex' : 'hidden'"
        class="lg:flex w-full lg:w-1/2 flex-col overflow-y-auto px-6 py-6"
      >
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-brand-900">Évènements à venir</h1>
          <p class="text-sm text-gray-500 mt-1">Découvre ce qui se passe près de chez toi</p>
        </div>
        <SearchBar class="mb-6" />

        <Loader v-if="eventsStore.loading" message="Chargement des évènements..." />

        <ErrorMessage v-else-if="eventsStore.error" :message="eventsStore.error" />

        <div v-else-if="eventsStore.events.length === 0" class="text-center text-gray-500 py-12">
          <i class="ti ti-calendar-off text-3xl"></i>
          <p class="mt-2">Aucun évènement trouvé</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <EventCard v-for="event in eventsStore.events" :key="event.id" :event="event" />
        </div>
      </div>

      <!-- Carte -->
      <div
        :class="activeTab === 'map' ? 'flex' : 'hidden'"
        class="lg:flex w-full lg:w-1/2 h-full sticky top-0"
      >
        <MapView />
      </div>
    </div>
  </div>
</template>

<style scoped></style>
