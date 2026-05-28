<script setup>
import { ref } from 'vue'
import { useEventsStore } from '@/stores/events'

const eventsStore = useEventsStore()

const keyword = ref('')
const city = ref('')
const category = ref('')
const startDate = ref('')
const endDate = ref('')

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
</script>

<template>
  <div class="flex flex-col gap-3">
    <!-- Ligne 1 : keyword + ville -->
    <div class="flex gap-2">
      <div class="relative flex-1">
        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="keyword"
          type="text"
          placeholder="Rechercher un évènement..."
          class="w-full bg-white border border-gray-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-blue-500"
          @keyup.enter="handleSearch"
        />
      </div>
      <div class="relative flex-1">
        <i class="ti ti-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="city"
          type="text"
          placeholder="Ville..."
          class="w-full bg-white border border-gray-200 rounded-lg pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-blue-500"
          @keyup.enter="handleSearch"
        />
      </div>
    </div>

    <div class="flex gap-2 flex-wrap">
      <select
        v-model="category"
        class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
      >
        <option value="">Toutes catégories</option>
        <option value="Music">Musique</option>
        <option value="Sports">Sports</option>
        <option value="Arts & Theatre">Arts & Théâtre</option>
        <option value="Family">Famille</option>
        <option value="Film">Cinéma</option>
        <option value="Miscellaneous">Autre</option>
      </select>
      <input
        v-model="startDate"
        type="date"
        class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
      />
      <input
        v-model="endDate"
        type="date"
        class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
      />
      <button
        @click="handleSearch"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800 transition-colors"
      >
        Rechercher
      </button>
      <button
        v-if="keyword || city || category || startDate || endDate"
        @click="handleClear"
        class="bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors"
      >
        <i class="ti ti-x"></i>
      </button>
    </div>
  </div>
</template>

<style scoped></style>
