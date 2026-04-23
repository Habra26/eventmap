import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/axios'

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

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  const isAuthenticated = () => !!token.value

  return { user, token, register, logout, isAuthenticated }
})