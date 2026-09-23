<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import { useEventsStore } from '@/stores/events'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
import 'leaflet.markercluster'

const eventsStore = useEventsStore()
const router = useRouter()
const mapContainer = ref(null)
let map = null
let clusterGroup = null

const locating = ref(false)

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
  clusterGroup.clearLayers()
}

function addMarkers() {
  clearMarkers()
  eventsStore.events.forEach((event) => {
    if (!event.latitude || !event.longitude) return

    const marker = L.marker([event.latitude, event.longitude], { icon: brandIcon })
    marker.eventId = event.id
    marker.bindPopup(`
      <div style="width:200px">
        ${event.image_url ? `<img src="${event.image_url}" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">` : ''}
        <strong style="color:#14532d">${event.title}</strong><br>
        <span style="color:#6b7280;font-size:13px">${event.city ?? ''}</span><br>
        <span style="color:#6b7280;font-size:13px">${event.date ?? ''}</span><br>
        <a href="#" data-event-id="${event.id}" class="leaflet-detail-link" style="color:#15803d;font-weight:500;">Voir le détail →</a>
      </div>
    `)
    clusterGroup.addLayer(marker)

    marker.on('click', () => {
      map.flyTo([event.latitude, event.longitude], map.getZoom(), { animate: false })
      eventsStore.selectEvent(event.id)
    })
  })

  const hasActiveSearch = eventsStore.lastSearchParams.keyword || eventsStore.lastSearchParams.city
  if (hasActiveSearch && clusterGroup.getLayers().length > 0) {
    map.fitBounds(clusterGroup.getBounds(), { padding: [50, 50], maxZoom: 14 })
  }
}

function onMapMoveEnd() {
  const hasActiveSearch = eventsStore.lastSearchParams.keyword || eventsStore.lastSearchParams.city
  if (hasActiveSearch) return

  const bounds = map.getBounds()
  const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`
  const params = { ...eventsStore.lastSearchParams, bbox }
  eventsStore.fetchEvents(params)
}

function initMap() {
  map = L.map(mapContainer.value).setView([50.5, 4.5], 8)

  L.tileLayer(
    `https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=${import.meta.env.VITE_CARTO_API_KEY}`,
    {
      attribution: '© OpenStreetMap contributors © CARTO',
      subdomains: 'abcd',
      maxZoom: 20,
    },
  ).addTo(map)

  clusterGroup = L.markerClusterGroup({
    maxClusterRadius: 80,
    disableClusteringAtZoom: 16,
  })
  map.addLayer(clusterGroup)

  if (navigator.geolocation) {
    locating.value = true
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords
        map.setView([latitude, longitude], 12)
        locating.value = false
      },
      () => {
        // Permission refusée ou erreur : on garde la vue par défaut (Belgique)
        locating.value = false
      },
      { timeout: 15000 },
    )
  }

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
    if (!id || !clusterGroup) return
    const marker = clusterGroup.getLayers().find((m) => m.eventId === id)
    if (!marker) return
    clusterGroup.zoomToShowLayer(marker, () => marker.openPopup())
  },
)

onUnmounted(() => {
  if (map) map.remove()
})
</script>

<template>
  <div class="relative w-full h-full">
    <div ref="mapContainer" class="w-full h-full"></div>

    <div
      v-if="locating"
      class="absolute top-4 left-1/2 -translate-x-1/2 bg-white shadow-md rounded-full px-4 py-2 text-sm text-gray-600 flex items-center gap-2 z-50"
    >
      <i class="ti ti-loader animate-spin text-brand-600"></i>
      Localisation en cours...
    </div>
  </div>
</template>

<style scoped>
:deep(.brand-marker) {
  filter: hue-rotate(-90deg) saturate(1.4) brightness(0.85);
}
</style>
