import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'
import { useFavoritesStore } from '@/stores/favorites'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user')) ?? null)
  const token = ref(localStorage.getItem('token') ?? null)

  async function register(name, email, password, passwordConfirmation) {
    const response = await api.post('/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })

    user.value = response.data.user
    token.value = response.data.token

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

    const favoritesStore = useFavoritesStore()
    await favoritesStore.fetchFavorites()
  }

  async function fetchUser() {
    const response = await api.get('/user')
    user.value = response.data
    localStorage.setItem('user', JSON.stringify(response.data))
  }

  async function logout() {
    await api.post('/logout')
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    const favoritesStore = useFavoritesStore()
    favoritesStore.clearFavorites()
  }

  const isAuthenticated = () => !!token.value

  return { user, token, register, login, logout, fetchUser, isAuthenticated }
})
