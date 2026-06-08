import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

export const useFavoritesStore = defineStore('favorites', () => {
  const favorites = ref([])
  const loading = ref(false)

  // Récupère tous les favoris de l'utilisateur connecté depuis le serveur
  async function fetchFavorites() {
    loading.value = true
    try {
      const response = await api.get('/favorites')
      favorites.value = response.data
    } catch (e) {
      // En cas d'erreur, on remet un tableau vide plutôt que de laisser un état incohérent
      favorites.value = []
    } finally {
      loading.value = false
    }
  }

  async function addFavorite(eventId) {
    await api.post('/favorites', { event_id: eventId })
    // Recharge la liste depuis le serveur après ajout pour garantir la synchronisation
    await fetchFavorites()
  }

  async function removeFavorite(eventId) {
    await api.delete(`/favorites/${eventId}`) // eventId = ticketmaster_id
    await fetchFavorites()
  }

  // Vérifie si un événement est déjà en favori à partir de son identifiant Ticketmaster
  // Utilisé dans EventCard et EventDetailView pour afficher l'état du bouton favori
  function isFavorite(ticketmasterId) {
    return favorites.value.some((f) => f.ticketmaster_id === ticketmasterId)
  }

  // Vide le store en mémoire, appelé à la déconnexion pour ne pas exposer les favoris d'un autre utilisateur
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
