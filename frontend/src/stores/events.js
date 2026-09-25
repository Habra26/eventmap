import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

export const useEventsStore = defineStore('events', () => {
  const events = ref([])
  const markers = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedEventId = ref(null)
  const lastSearchParams = ref({})
  const currentPage = ref(1)
  const totalPages = ref(1)
  const total = ref(0)

  function selectEvent(id) {
    selectedEventId.value = id
  }

  async function fetchEvents(params = {}, { keepMarkers = false } = {}) {
    loading.value = true
    error.value = null
    lastSearchParams.value = { ...params }
    try {
      const response = await api.get('/events', { params })
      events.value = response.data.data
      if (!keepMarkers) {
        markers.value = response.data.markers ?? response.data.data
      }
      currentPage.value = response.data.current_page
      totalPages.value = response.data.last_page
      total.value = response.data.total
    } catch (e) {
      error.value = 'Impossible de charger les évènements'
    } finally {
      loading.value = false
    }
  }

  // Changer de page ne change pas les marqueurs : la carte n'est pas redessinée
  function goToPage(page) {
    fetchEvents({ ...lastSearchParams.value, page }, { keepMarkers: true })
  }

  return {
    events,
    markers,
    loading,
    error,
    fetchEvents,
    selectedEventId,
    selectEvent,
    lastSearchParams,
    currentPage,
    totalPages,
    total,
    goToPage,
  }
})
