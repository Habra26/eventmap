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
let isProgrammaticMove = false

function clearMarkers() {
  markers.forEach((m) => m.remove())
  markers = []
}

function addMarkers() {
  clearMarkers()
  eventsStore.events.forEach((event) => {
    if (!event.latitude || !event.longitude) return

    const marker = L.marker([event.latitude, event.longitude])
    marker.bindPopup(`
      <div style="width:200px">
        ${event.image_url ? `<img src="${event.image_url}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;">` : ''}
        <strong>${event.title}</strong><br>
        ${event.city ?? ''}<br>
        ${event.date ?? ''}<br>
        <a href="#" data-event-id="${event.id}" class="leaflet-detail-link" style="color:#1d4ed8;">Voir le détail</a>
      </div>
    `)
    marker.addTo(map)
    markers.push(marker)
    marker.on('click', () => {
      eventsStore.selectEvent(event.id)
    })
  })
}

function onMapMoveEnd() {
  if (isProgrammaticMove) {
    setTimeout(() => {
      isProgrammaticMove = false
    }, 1500)
    return
  }
  const bounds = map.getBounds()
  const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`
  eventsStore.fetchEvents({ bbox })
}

function initMap() {
  map = L.map(mapContainer.value).setView([50.5, 4.5], 8)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
  }).addTo(map)

  map.on('moveend', onMapMoveEnd)

  map.on('popupopen', (e) => {
    isProgrammaticMove = true
    map.panTo(e.popup.getLatLng())
    setTimeout(() => {
      isProgrammaticMove = false
    }, 1500)

    document.querySelectorAll('.leaflet-detail-link').forEach((el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault()
        const id = el.getAttribute('data-event-id')
        router.push(`/events/${id}`)
      })
    })
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
    if (eventsStore.selectedEventId) {
      const event = eventsStore.events.find((e) => e.id === eventsStore.selectedEventId)
      if (event?.latitude && event?.longitude) {
        markers
          .find((m) => {
            const pos = m.getLatLng()
            return pos.lat == event.latitude && pos.lng == event.longitude
          })
          ?.openPopup()
      }
    }
  },
)

watch(
  () => eventsStore.selectedEventId,
  (id) => {
    if (!id || !map) return
    const event = eventsStore.events.find((e) => e.id === id)
    if (!event?.latitude || !event?.longitude) return
    isProgrammaticMove = true
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

<style scoped></style>
