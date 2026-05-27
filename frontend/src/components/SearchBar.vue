<script setup>
import { ref } from 'vue'
import { useEventsStore } from '@/stores/events'

const eventsStore = useEventsStore()
const keyword = ref('')

function handleSearch() {
  eventsStore.fetchEvents({ keyword: keyword.value })
}

function handleClear() {
  keyword.value = ''
  eventsStore.fetchEvents()
}
</script>

<template>
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
    <button
      @click="handleSearch"
      class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800 transition-colors"
    >
      Rechercher
    </button>
    <button
      v-if="keyword"
      @click="handleClear"
      class="bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors"
    >
      <i class="ti ti-x"></i>
    </button>
  </div>
</template>

<style scoped></style>
