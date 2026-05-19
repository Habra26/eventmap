<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/axios'

const route = useRoute()
const event = ref(null)
const loading = ref(true)
const error = ref(null)

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
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 py-8">
    <div v-if="loading" class="text-center text-gray-500 py-12">
      <i class="ti ti-loader text-3xl animate-spin"></i>
      <p class="mt-2">Chargement...</p>
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-12">
      <i class="ti ti-alert-circle text-3xl"></i>
      <p class="mt-2">{{ error }}</p>
    </div>

    <div v-else-if="event">
      <RouterLink to="/" class="text-sm text-blue-700 hover:underline flex items-center gap-1 mb-6">
        <i class="ti ti-arrow-left"></i> Retour aux évènements
      </RouterLink>

      <img
        v-if="event.image_url"
        :src="event.image_url"
        :alt="event.title"
        class="w-full h-64 object-cover rounded-xl mb-6"
      />

      <h1 class="text-2xl font-medium mb-4">{{ event.title }}</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-calendar text-blue-600"></i>
          <span>{{ event.date ?? 'Date inconnue' }}</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-clock text-blue-600"></i>
          <span>{{ event.time ?? 'Heure inconnue' }}</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-map-pin text-blue-600"></i>
          <span>{{ event.city ?? 'Ville inconnue' }}</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
          <i class="ti ti-building text-blue-600"></i>
          <span>{{ event.venue ?? 'Lieu inconnu' }}</span>
        </div>
      </div>
      <p class="text-gray-600 mb-6">{{ event.description ?? 'Aucune description disponible' }}</p>

      <a
        v-if="event.ticket_url"
        :href="event.ticket_url"
        target="_blank"
        class="inline-flex items-center gap-2 bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition-colors"
      >
        <i class="ti ti-ticket"></i> Acheter des billets
      </a>
    </div>
  </div>
</template>

<style scoped></style>
