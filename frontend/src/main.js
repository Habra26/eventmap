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

app.use(pinia)
app.use(router)

const authStore = useAuthStore()
if (authStore.token) {
  authStore.fetchUser()
}

app.mount('#app')