import axios from 'axios'
import router from '@/router'
import { useToastStore } from '@/stores/toasts'

// Instance axios centralisée — toutes les requêtes API passent par ici
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api', // ex. https://api.eventmap.com/api
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json', // Demande explicitement du JSON pour que Laravel retourne des erreurs JSON
  },
})

// Intercepteur de requête : injecte automatiquement le token Bearer dans chaque requête sortante
// Cela évite de devoir passer le token manuellement à chaque appel api.get/post/...
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Intercepteur de réponse : gestion centralisée des erreurs HTTP
api.interceptors.response.use(
  (response) => response, // Les réponses 2xx passent sans modification

  (error) => {
    const toastStore = useToastStore()

    if (!error.response) {
      // Pas de réponse = serveur injoignable
      toastStore.error('Impossible de contacter le serveur')
    } else {
      const status = error.response.status

      if (status === 401) {
        // Token expiré ou invalide : on nettoie la session et on redirige vers la page de connexion
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

    // On rejette la promesse pour que les blocs catch des appelants puissent aussi réagir si besoin
    return Promise.reject(error)
  },
)

export default api
