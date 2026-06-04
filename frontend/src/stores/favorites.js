import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

export const useFavoritesStore = defineStore('favorites', () => {
  const favorites = ref([])
  const loading = ref(false)

  async function fetchFavorites() {
    loading.value = true
    try {
      const response = await api.get('/favorites')
      favorites.value = response.data
    } catch (e) {
      favorites.value = []
    } finally {
      loading.value = false
    }
  }

 async function addFavorite(eventId) {
  await api.post('/favorites', { event_id: eventId })
  await fetchFavorites()
}

  async function removeFavorite(eventId) {
    await api.delete(`/favorites/${eventId}`)
    await fetchFavorites()
  }

  function isFavorite(ticketmasterId) {
    return favorites.value.some((f) => f.ticketmaster_id === ticketmasterId)
  }

  function clearFavorites() {
    favorites.value = []
  }

  return {
    favorites,
    loading,
    fetchFavorites,
    addFavorite,
    removeFavorite,
    isFavorite,
    clearFavorites,
  }
})
