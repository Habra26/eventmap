<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import { useEventsStore } from '@/stores/events'

const eventsStore = useEventsStore()
const router = useRouter()
const mapContainer = ref(null)
let map = null
let markers = []

const brandIcon = L.icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
  className: 'brand-marker',
})

function clearMarkers() {
  markers.forEach((m) => m.remove())
  markers = []
}

function addMarkers() {
  clearMarkers()
  eventsStore.events.forEach((event) => {
    if (!event.latitude || !event.longitude) return

    const marker = L.marker([event.latitude, event.longitude], { icon: brandIcon })
    marker.bindPopup(`
      <div style="width:200px">
        ${event.image_url ? `<img src="${event.image_url}" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">` : ''}
        <strong style="color:#14532d">${event.title}</strong><br>
        <span style="color:#6b7280;font-size:13px">${event.city ?? ''}</span><br>
        <span style="color:#6b7280;font-size:13px">${event.date ?? ''}</span><br>
        <a href="#" data-event-id="${event.id}" class="leaflet-detail-link" style="color:#15803d;font-weight:500;">Voir le détail →</a>
      </div>
    `)
    marker.addTo(map)
    markers.push(marker)

    marker.on('click', () => {
      map.flyTo([event.latitude, event.longitude], map.getZoom(), { animate: false })
      eventsStore.selectEvent(event.id)
    })
  })
}

function onMapMoveEnd() {
  const bounds = map.getBounds()
  const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`
  const params = { ...eventsStore.currentParams, bbox }
  eventsStore.fetchEvents(params)
}

function initMap() {
  map = L.map(mapContainer.value).setView([50.5, 4.5], 8)

  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap contributors © CARTO',
    subdomains: 'abcd',
    maxZoom: 20,
  }).addTo(map)

  map.on('dragend', onMapMoveEnd)
  map.on('zoomend', onMapMoveEnd)

  mapContainer.value.addEventListener('click', (e) => {
    const link = e.target.closest('.leaflet-detail-link')
    if (link) {
      e.preventDefault()
      const id = link.getAttribute('data-event-id')
      router.push(`/events/${id}`)
    }
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

watch(
  () => eventsStore.selectedEventId,
  (id) => {
    if (!id || !map) return
    const event = eventsStore.events.find((e) => e.id === id)
    if (!event?.latitude || !event?.longitude) return
    map.flyTo([event.latitude, event.longitude], map.getZoom(), { animate: false })
    markers
      .find((m) => {
        const pos = m.getLatLng()
        return pos.lat == event.latitude && pos.lng == event.longitude
      })
      ?.openPopup()
  },
)

onUnmounted(() => {
  if (map) map.remove()
})
</script>

<template>
  <div ref="mapContainer" class="w-full h-full"></div>
</template>

<style scoped>
:deep(.brand-marker) {
  filter: hue-rotate(-90deg) saturate(1.4) brightness(0.85);
}
</style>
