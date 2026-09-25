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

// true pendant un déplacement de carte déclenché par l'application (et non par l'utilisateur)
let programmaticMove = false

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
  eventsStore.markers.forEach((event) => {
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
      // L'évènement est sur une autre page de la liste : on charge cette page
      if (event.page && event.page !== eventsStore.currentPage) {
        eventsStore.goToPage(event.page)
      }
    })
  })

  const hasActiveSearch = eventsStore.lastSearchParams.keyword || eventsStore.lastSearchParams.city
  if (hasActiveSearch && clusterGroup.getLayers().length > 0) {
    programmaticMove = true
    map.fitBounds(clusterGroup.getBounds(), { padding: [50, 50], maxZoom: 14 })
    setTimeout(() => (programmaticMove = false), 500)
  }
}

function onMapMoveEnd() {
  // Déplacement fait par l'application : on ne relance pas de recherche
  if (programmaticMove) return

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

  // Pas de recentrage sur l'utilisateur si une recherche par mot-clé ou ville est en cours :
  // la carte doit rester cadrée sur les résultats
  const hasActiveSearch = eventsStore.lastSearchParams.keyword || eventsStore.lastSearchParams.city
  if (navigator.geolocation && !hasActiveSearch) {
    locating.value = true
    navigator.geolocation.getCurrentPosition(
      (position) => {
        locating.value = false
        // La carte a pu être détruite entre-temps (l'utilisateur a quitté la page)
        if (!map) return
        const { latitude, longitude } = position.coords
        map.setView([latitude, longitude], 12)
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
  if (eventsStore.markers.length > 0) {
    addMarkers()
  }
})

watch(
  () => eventsStore.markers,
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

    programmaticMove = true
    clusterGroup.zoomToShowLayer(marker, () => {
      marker.openPopup()
      // Laisse le temps aux évènements zoomend de passer avant de réactiver la recherche
      setTimeout(() => (programmaticMove = false), 300)
    })
  },
)

onUnmounted(() => {
  if (map) map.remove()
  map = null
  clusterGroup = null
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
