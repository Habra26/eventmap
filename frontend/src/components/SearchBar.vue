<script setup>
import { ref } from 'vue'
import { useEventsStore } from '@/stores/events'

const eventsStore = useEventsStore()

const keyword = ref('')
const city = ref('')
const category = ref('')
const startDate = ref('')
const endDate = ref('')
const showFilters = ref(false)

function buildParams() {
  const params = {}
  if (keyword.value) params.keyword = keyword.value
  if (city.value) params.city = city.value
  if (category.value) params.category = category.value
  if (startDate.value) params.startDate = startDate.value
  if (endDate.value) params.endDate = endDate.value
  return params
}

function handleSearch() {
  eventsStore.fetchEvents(buildParams())
}

function handleClear() {
  keyword.value = ''
  city.value = ''
  category.value = ''
  startDate.value = ''
  endDate.value = ''
  eventsStore.fetchEvents()
}

const hasFilters = () =>
  keyword.value || city.value || category.value || startDate.value || endDate.value
</script>

<template>
  <div class="flex flex-col gap-3">
    <!-- Barre principale -->
    <div class="flex gap-2">
      <div class="relative flex-1">
        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="keyword"
          type="text"
          placeholder="Rechercher un évènement..."
          class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
          @keyup.enter="handleSearch"
        />
      </div>
      <button
        @click="handleSearch"
        class="bg-brand-900 text-white px-5 py-2.5 rounded-xl text-sm hover:bg-brand-800 transition-colors whitespace-nowrap"
      >
        Rechercher
      </button>
    </div>

    <!-- Toggle filtres -->
    <div class="flex items-center justify-between">
      <button
        @click="showFilters = !showFilters"
        class="text-sm text-gray-500 hover:text-brand-900 transition-colors flex items-center gap-1"
      >
        <i class="ti ti-adjustments-horizontal"></i> Filtres
        <i :class="showFilters ? 'ti ti-chevron-up' : 'ti ti-chevron-down'"></i>
      </button>
      <button
        v-if="hasFilters()"
        @click="handleClear"
        class="text-sm text-gray-400 hover:text-accent-600 transition-colors flex items-center gap-1"
      >
        <i class="ti ti-x"></i> Réinitialiser
      </button>
    </div>

    <!-- Filtres dépliables -->
    <div v-if="showFilters" class="flex flex-col gap-2 pt-1">
      <div class="relative">
        <i class="ti ti-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="city"
          type="text"
          placeholder="Ville..."
          class="w-full bg-white border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
          @keyup.enter="handleSearch"
        />
      </div>
      <select
        v-model="category"
        class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      >
        <option value="">Toutes catégories</option>
        <option value="Music">Musique</option>
        <option value="Sports">Sports</option>
        <option value="Arts & Theatre">Arts & Théâtre</option>
        <option value="Family">Famille</option>
        <option value="Film">Cinéma</option>
        <option value="Miscellaneous">Autre</option>
      </select>
      <div class="flex gap-2">
        <input
          v-model="startDate"
          type="date"
          class="flex-1 bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
        />
        <input
          v-model="endDate"
          type="date"
          class="flex-1 bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
        />
      </div>
    </div>
  </div>
</template>

<style scoped></style>
