import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'
import { useFavoritesStore } from '@/stores/favorites'

export const useAuthStore = defineStore('auth', () => {
  // Initialisation depuis localStorage pour maintenir la session après un rechargement de page
  const user = ref(JSON.parse(localStorage.getItem('user')) ?? null)
  const token = ref(localStorage.getItem('token') ?? null)

  async function register(name, email, password, passwordConfirmation) {
    const response = await api.post('/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation, // Laravel attend ce nom de champ pour la règle 'confirmed'
    })

    user.value = response.data.user
    token.value = response.data.token

    // Persiste en localStorage pour survivre aux rechargements (l'intercepteur axios y lit le token)
    localStorage.setItem('user', JSON.stringify(response.data.user))
    localStorage.setItem('token', response.data.token)
  }

  async function login(email, password) {
    const response = await api.post('/login', {
      email,
      password,
    })

    user.value = response.data.user
    token.value = response.data.token

    localStorage.setItem('user', JSON.stringify(response.data.user))
    localStorage.setItem('token', response.data.token)

    // Charge les favoris immédiatement après la connexion pour qu'ils soient disponibles dans toute l'app
    const favoritesStore = useFavoritesStore()
    await favoritesStore.fetchFavorites()
  }

  // Rafraîchit les données utilisateur depuis le serveur
  async function fetchUser() {
    const response = await api.get('/user')
    user.value = response.data
    localStorage.setItem('user', JSON.stringify(response.data))
  }

  async function updateProfile(name, email) {
    const response = await api.put('/profile', { name, email })
    // Met à jour le store et le localStorage pour garder l'affichage cohérent sans rechargement
    user.value = response.data.user
    localStorage.setItem('user', JSON.stringify(response.data.user))
    return response.data.message
  }

  async function updatePassword(currentPassword, password, passwordConfirmation) {
    const response = await api.put('/profile/password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    })
    return response.data.message
  }

  async function deleteAccount() {
    await api.delete('/profile')
    // Nettoyage local après suppression du compte
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    const favoritesStore = useFavoritesStore()
    favoritesStore.clearFavorites()
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch (e) {
      // Si le token est déjà expiré côté serveur (401), on nettoie le store quand même
    }
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    // Vide les favoris en mémoire pour ne pas les afficher à un futur utilisateur sur le même appareil
    const favoritesStore = useFavoritesStore()
    favoritesStore.clearFavorites()
  }

  // Raccourci booléen, vrai si un token est présent (le token est supprimé à la déconnexion)
  const isAuthenticated = () => !!token.value

  return {
    user,
    token,
    register,
    login,
    logout,
    fetchUser,
    updateProfile,
    updatePassword,
    deleteAccount,
    isAuthenticated,
  }
})
