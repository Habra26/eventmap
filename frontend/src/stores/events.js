import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

export const useEventsStore = defineStore('events', () => {
  const events = ref([])
  const loading = ref(false)
  const error = ref(null)

  // ID de l'événement sélectionné dans la liste — utilisé pour synchroniser la carte
  // Quand l'utilisateur clique sur une carte, la carte centre et ouvre le popup correspondant
  const selectedEventId = ref(null)

  function selectEvent(id) {
    selectedEventId.value = id
  }

  // Charge les événements depuis le backend avec des filtres optionnels (keyword, city, bbox, etc.)
  // params est un objet clé/valeur transmis directement en query string
  async function fetchEvents(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/events', { params })
      events.value = response.data
    } catch (e) {
      error.value = 'Impossible de charger les évènements'
    } finally {
      // finally garantit que loading repasse à false même en cas d'erreur
      loading.value = false
    }
  }

  return { events, loading, error, fetchEvents, selectedEventId, selectEvent }
})
