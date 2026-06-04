import axios from 'axios'
import router from '@/router'
import { useToastStore } from '@/stores/toasts'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const toastStore = useToastStore()

    if (!error.response) {
      toastStore.error('Impossible de contacter le serveur')
    } else {
      const status = error.response.status

      if (status === 401) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        if (router.currentRoute.value.name !== 'login') {
          router.push({ name: 'login' })
          toastStore.error('Session expirée, veuillez vous reconnecter')
        }
      } else if (status === 503) {
        toastStore.error('Service temporairement indisponible')
      } else if (status >= 500) {
        toastStore.error('Une erreur serveur est survenue')
      }
    }

    return Promise.reject(error)
  },
)

export default api
