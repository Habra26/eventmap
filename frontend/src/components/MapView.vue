<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue'
import L from 'leaflet'
import { useEventsStore } from '@/stores/events'
import { useRouter } from 'vue-router'

const eventsStore = useEventsStore()
const router = useRouter()
const mapContainer = ref(null)
let map = null

function initMap() {
  map = L.map(mapContainer.value).setView([50.5, 4.5], 8)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
  }).addTo(map)
}

function addMarkers() {
  eventsStore.events.forEach((event) => {
    if (!event.latitude || !event.longitude) return

    const marker = L.marker([event.latitude, event.longitude])
    marker.bindPopup(`
  <div style="width:200px">
    ${event.image_url ? `<img src="${event.image_url}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;">` : ''}
    <strong>${event.title}</strong><br>
    ${event.city ?? ''}<br>
    ${event.date ?? ''}<br>
    <a href="/events/${event.id}" style="color:#1d4ed8;">Voir le détail</a>
  </div>
`)
    marker.addTo(map)
  })
}

onMounted(() => {
  initMap()
  if (eventsStore.events.length > 0) {
    addMarkers()
  }
})

watch(
  () => eventsStore.events,
  () => {
    addMarkers()
  },
)

onUnmounted(() => {
  if (map) map.remove()
})
</script>

<template>
  <div ref="mapContainer" class="w-full h-full"></div>
</template>

<style scoped></style>
