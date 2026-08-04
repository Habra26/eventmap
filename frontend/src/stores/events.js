import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

export const useEventsStore = defineStore('events', () => {
  const events = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedEventId = ref(null)
  const lastSearchParams = ref({}) 

  function selectEvent(id) {
    selectedEventId.value = id
  }

  async function fetchEvents(params = {}) {
    loading.value = true
    error.value = null
    lastSearchParams.value = { ...params }
    try {
      const response = await api.get('/events', { params })
      events.value = response.data
    } catch (e) {
      error.value = 'Impossible de charger les évènements'
    } finally {
      loading.value = false
    }
  }

  return { events, loading, error, fetchEvents, selectedEventId, selectEvent, lastSearchParams }
})