import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

import App from './App.vue'
import router from './router'
import '@tabler/icons-webfont/dist/tabler-icons.min.css'
import './assets/main.css'
import 'leaflet/dist/leaflet.css'

const app = createApp(App)
const pinia = createPinia()

// Pinia doit être installé avant le router car les guards de navigation utilisent les stores
app.use(pinia)
app.use(router)

// Si un token est déjà présent, on rafraîchit les données utilisateur au démarrage
// Cela permet de détecter un compte supprimé ou un token révoqué sans attendre la prochaine action
const authStore = useAuthStore()
if (authStore.token) {
  authStore.fetchUser()
}

app.mount('#app')
