<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toasts'
import { useEventsStore } from '@/stores/events'
import api from '@/axios'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

const name = ref('')
const email = ref('')
const profileErrors = ref({})
const profileLoading = ref(false)

const currentPassword = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const passwordErrors = ref({})
const passwordLoading = ref(false)

const deleteLoading = ref(false)

const searchHistory = ref([])
const historyLoading = ref(true)

const eventsStore = useEventsStore()

onMounted(async () => {
  name.value = authStore.user?.name ?? ''
  email.value = authStore.user?.email ?? ''

  try {
    const response = await api.get('/search-history')
    searchHistory.value = response.data
  } catch (e) {
  } finally {
    historyLoading.value = false
  }
})

function validateProfile() {
  const e = {}
  if (!name.value) e.name = 'Le nom est requis'
  if (!email.value) {
    e.email = "L'email est requis"
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    e.email = 'Email invalide'
  }
  profileErrors.value = e
  return Object.keys(e).length === 0
}

function validatePassword() {
  const e = {}
  if (!currentPassword.value) e.currentPassword = 'Le mot de passe actuel est requis'
  if (!password.value) {
    e.password = 'Le nouveau mot de passe est requis'
  } else if (password.value.length < 8) {
    e.password = 'Au moins 8 caractères'
  }
  if (password.value !== passwordConfirmation.value) {
    e.passwordConfirmation = 'Les mots de passe ne correspondent pas'
  }
  passwordErrors.value = e
  return Object.keys(e).length === 0
}

async function updateProfile() {
  if (!validateProfile()) return
  profileLoading.value = true
  try {
    const message = await authStore.updateProfile(name.value, email.value)
    toastStore.success(message)
  } catch (e) {
    toastStore.error(e.response?.data?.message ?? 'Erreur lors de la mise à jour')
  } finally {
    profileLoading.value = false
  }
}

async function updatePassword() {
  if (!validatePassword()) return
  passwordLoading.value = true
  try {
    const message = await authStore.updatePassword(
      currentPassword.value,
      password.value,
      passwordConfirmation.value,
    )
    toastStore.success(message)
    currentPassword.value = ''
    password.value = ''
    passwordConfirmation.value = ''
    passwordErrors.value = {}
  } catch (e) {
    toastStore.error(e.response?.data?.message ?? 'Erreur lors du changement de mot de passe')
  } finally {
    passwordLoading.value = false
  }
}

async function deleteAccount() {
  if (!confirm('Es-tu sûr de vouloir supprimer ton compte ? Cette action est irréversible.')) {
    return
  }
  deleteLoading.value = true
  try {
    await authStore.deleteAccount()
    toastStore.success('Compte supprimé')
    router.push('/register')
  } catch (e) {
    toastStore.error('Erreur lors de la suppression du compte')
  } finally {
    deleteLoading.value = false
  }
}

function formatEntry(entry) {
  const parts = []
  if (entry.keyword) parts.push(entry.keyword)
  if (entry.city) parts.push(entry.city)
  if (entry.category) parts.push(entry.category)
  if (entry.start_date || entry.end_date) {
    parts.push(`${entry.start_date ?? '...'} → ${entry.end_date ?? '...'}`)
  }
  return parts.length > 0 ? parts.join(' / ') : 'Recherche sans filtre'
}

async function clearHistory() {
  if (!confirm('Vider tout ton historique de recherches ?')) return
  try {
    await api.delete('/search-history')
    searchHistory.value = []
    toastStore.success('Historique vidé')
  } catch (e) {
    toastStore.error("Erreur lors de la suppression de l'historique")
  }
}

function rerunSearch(entry) {
  const params = {}
  if (entry.keyword) params.keyword = entry.keyword
  if (entry.city) params.city = entry.city
  if (entry.category) params.category = entry.category
  if (entry.start_date) params.startDate = entry.start_date
  if (entry.end_date) params.endDate = entry.end_date

  eventsStore.fetchEvents(params)
  router.push('/')
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-6 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-brand-900">Mon profil</h1>
      <p class="text-sm text-gray-500 mt-1">Gère tes informations personnelles</p>
    </div>

    <!-- Infos personnelles -->
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h2>

      <label class="block text-sm text-gray-600 mb-1">Nom</label>
      <input
        v-model="name"
        type="text"
        class="w-full border rounded-xl px-3 py-2.5 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
        :class="profileErrors.name ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'"
      />
      <p v-if="profileErrors.name" class="text-accent-600 text-xs mb-3">{{ profileErrors.name }}</p>
      <div v-else class="mb-3"></div>

      <label class="block text-sm text-gray-600 mb-1">Email</label>
      <input
        v-model="email"
        type="email"
        class="w-full border rounded-xl px-3 py-2.5 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
        :class="
          profileErrors.email ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'
        "
      />
      <p v-if="profileErrors.email" class="text-accent-600 text-xs mb-3">
        {{ profileErrors.email }}
      </p>
      <div v-else class="mb-3"></div>

      <button
        @click="updateProfile"
        :disabled="profileLoading"
        class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <i v-if="profileLoading" class="ti ti-loader animate-spin"></i>
        {{ profileLoading ? 'Enregistrement...' : 'Enregistrer' }}
      </button>
    </section>
    <!-- Historique de recherche -->
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Historique des recherches</h2>
        <button
          v-if="searchHistory.length > 0"
          @click="clearHistory"
          class="text-xs text-gray-400 hover:text-accent-600 transition-colors"
        >
          Vider l'historique
        </button>
      </div>

      <p v-if="historyLoading" class="text-sm text-gray-500">Chargement...</p>
      <p v-else-if="searchHistory.length === 0" class="text-sm text-gray-500">
        Aucune recherche récente
      </p>
      <ul v-else class="space-y-2">
        <li
          v-for="entry in searchHistory"
          :key="entry.id"
          @click="rerunSearch(entry)"
          class="flex items-center justify-between bg-brand-50 hover:bg-brand-100 transition-colors rounded-xl px-4 py-3 text-sm cursor-pointer"
        >
          <span class="text-gray-700">{{ formatEntry(entry) }}</span>
          <i class="ti ti-search text-brand-600"></i>
        </li>
      </ul>
    </section>

    <!-- Mot de passe -->
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Changer le mot de passe</h2>

      <label class="block text-sm text-gray-600 mb-1">Mot de passe actuel</label>
      <input
        v-model="currentPassword"
        type="password"
        class="w-full border rounded-xl px-3 py-2.5 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
        :class="
          passwordErrors.currentPassword
            ? 'border-accent-500'
            : 'border-gray-200 focus:border-brand-600'
        "
      />
      <p v-if="passwordErrors.currentPassword" class="text-accent-600 text-xs mb-3">
        {{ passwordErrors.currentPassword }}
      </p>
      <div v-else class="mb-3"></div>

      <label class="block text-sm text-gray-600 mb-1">Nouveau mot de passe</label>
      <input
        v-model="password"
        type="password"
        class="w-full border rounded-xl px-3 py-2.5 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
        :class="
          passwordErrors.password ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'
        "
      />
      <p v-if="passwordErrors.password" class="text-accent-600 text-xs mb-3">
        {{ passwordErrors.password }}
      </p>
      <div v-else class="mb-3"></div>

      <label class="block text-sm text-gray-600 mb-1">Confirmer le nouveau mot de passe</label>
      <input
        v-model="passwordConfirmation"
        type="password"
        class="w-full border rounded-xl px-3 py-2.5 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
        :class="
          passwordErrors.passwordConfirmation
            ? 'border-accent-500'
            : 'border-gray-200 focus:border-brand-600'
        "
      />
      <p v-if="passwordErrors.passwordConfirmation" class="text-accent-600 text-xs mb-3">
        {{ passwordErrors.passwordConfirmation }}
      </p>
      <div v-else class="mb-3"></div>

      <button
        @click="updatePassword"
        :disabled="passwordLoading"
        class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <i v-if="passwordLoading" class="ti ti-loader animate-spin"></i>
        {{ passwordLoading ? 'Modification...' : 'Changer le mot de passe' }}
      </button>
    </section>

    <!-- Suppression compte -->
    <section class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
      <h2 class="text-lg font-semibold mb-2 text-red-600">Zone dangereuse</h2>
      <p class="text-sm text-gray-500 mb-4">
        La suppression de ton compte est définitive et supprime tous tes favoris.
      </p>
      <button
        @click="deleteAccount"
        :disabled="deleteLoading"
        class="bg-red-600 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <i v-if="deleteLoading" class="ti ti-loader animate-spin"></i>
        {{ deleteLoading ? 'Suppression...' : 'Supprimer mon compte' }}
      </button>
    </section>
  </div>
</template>

<style scoped></style>
