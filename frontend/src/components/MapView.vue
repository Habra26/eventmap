<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import { useEventsStore } from '@/stores/events'

const eventsStore = useEventsStore()
const router = useRouter()
const mapContainer = ref(null) // Référence à l'élément DOM qui accueille la carte Leaflet

// map et markers sont des variables JS ordinaires (pas des ref) car Leaflet gère son propre état interne
// Les rendre réactifs via ref() causerait des conflits avec le système de mise à jour de Leaflet
let map = null
let markers = []

// Icône personnalisée qui pointe vers les images CDN de Leaflet
// Sans cela, les icônes sont souvent introuvables après le build Vite (chemin d'asset cassé)
const brandIcon = L.icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],   // Point de l'icône qui correspond à la position géographique
  popupAnchor: [1, -34],  // Décalage du popup par rapport à l'ancre de l'icône
  shadowSize: [41, 41],
  className: 'brand-marker',
})

// Supprime tous les marqueurs de la carte avant d'en ajouter de nouveaux (évite les doublons)
function clearMarkers() {
  markers.forEach((m) => m.remove())
  markers = []
}

// Crée un marqueur avec popup pour chaque événement ayant des coordonnées GPS
function addMarkers() {
  clearMarkers()
  eventsStore.events.forEach((event) => {
    if (!event.latitude || !event.longitude) return

    const marker = L.marker([event.latitude, event.longitude], { icon: brandIcon })

    // Le popup contient un lien HTML — la navigation Vue Router est gérée via délégation d'événement (voir initMap)
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

    // Au clic sur un marqueur, on centre la carte et on notifie le store pour synchroniser la liste
    marker.on('click', () => {
      map.flyTo([event.latitude, event.longitude], map.getZoom(), { animate: false })
      eventsStore.selectEvent(event.id)
    })
  })
}

// Déclenché à la fin d'un déplacement ou d'un zoom, recharge les événements dans la zone visible
function onMapMoveEnd() {
  const bounds = map.getBounds()
  // bbox = "south,west,north,east" — format attendu par le backend pour filtrer par zone géographique
  const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`
  const params = { ...eventsStore.currentParams, bbox }
  eventsStore.fetchEvents(params)
}

function initMap() {
  // Centre initial sur la Belgique, zoom 8 pour voir tout le pays
  map = L.map(mapContainer.value).setView([50.5, 4.5], 8)

  // Fond de carte CartoDB Voyager
  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap contributors © CARTO',
    subdomains: 'abcd',
    maxZoom: 20,
  }).addTo(map)

  map.on('dragend', onMapMoveEnd)
  map.on('zoomend', onMapMoveEnd)

  // Délégation d'événement sur le conteneur de la carte pour intercepter les clics sur les liens des popups
  // On ne peut pas attacher un écouteur Vue directement dans le HTML du popup (géré par Leaflet hors du DOM Vue)
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
  // Si des événements sont déjà chargés dans le store, on les affiche immédiatement
  if (eventsStore.events.length > 0) {
    addMarkers()
  }
})

// Rafraîchit les marqueurs chaque fois que la liste d'événements change dans le store
watch(
  () => eventsStore.events,
  () => {
    addMarkers()
  },
)

// Quand un événement est sélectionné dans la liste, centre la carte dessus et ouvre son popup
watch(
  () => eventsStore.selectedEventId,
  (id) => {
    if (!id || !map) return
    const event = eventsStore.events.find((e) => e.id === id)
    if (!event?.latitude || !event?.longitude) return
    // animate: false évite un conflit visuel si la carte est déjà en train de se déplacer
    map.flyTo([event.latitude, event.longitude], map.getZoom(), { animate: false })
    markers
      .find((m) => {
        const pos = m.getLatLng()
        return pos.lat == event.latitude && pos.lng == event.longitude
      })
      ?.openPopup()
  },
)

// Détruit l'instance Leaflet quand le composant est démonté pour libérer la mémoire et les écouteurs
onUnmounted(() => {
  if (map) map.remove()
})
</script>

<template>
  <div ref="mapContainer" class="w-full h-full"></div>
</template>

<style scoped>
/* Teinte verte appliquée aux marqueurs via un filtre CSS pour correspondre à la charte graphique */
:deep(.brand-marker) {
  filter: hue-rotate(-90deg) saturate(1.4) brightness(0.85);
}
</style>
