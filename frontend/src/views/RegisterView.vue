<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '', // Nom exact attendu par la règle 'confirmed' de Laravel
})

const errors = ref({})        // Erreurs de validation champ par champ
const generalError = ref(null)
const loading = ref(false)

// Validation côté client — les mêmes règles que le backend pour un retour immédiat à l'utilisateur
function validate() {
  const e = {}
  if (!form.value.name) e.name = 'Le nom est requis'
  if (!form.value.email) {
    e.email = "L'email est requis"
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    e.email = 'Email invalide'
  }
  if (!form.value.password) {
    e.password = 'Le mot de passe est requis'
  } else if (form.value.password.length < 8) {
    e.password = 'Au moins 8 caractères'
  }
  if (form.value.password !== form.value.password_confirmation) {
    e.password_confirmation = 'Les mots de passe ne correspondent pas'
  }
  errors.value = e
  return Object.keys(e).length === 0
}

async function handleSubmit() {
  generalError.value = null
  if (!validate()) return

  loading.value = true
  try {
    await authStore.register(
      form.value.name,
      form.value.email,
      form.value.password,
      form.value.password_confirmation,
    )
    router.push('/')
  } catch (e) {
    if (e.response?.status === 422) {
      // Le backend renvoie des erreurs par champ sous errors.{champ}[0] — on prend le premier message
      const backend = e.response.data.errors
      errors.value = Object.fromEntries(Object.entries(backend).map(([key, val]) => [key, val[0]]))
    } else {
      generalError.value = e.response?.data?.message ?? 'Une erreur est survenue'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Panneau de présentation — identique à LoginView pour cohérence visuelle -->
    <div class="hidden lg:flex lg:w-1/2 bg-brand-900 flex-col justify-center items-center px-12">
      <i class="ti ti-map-pin text-5xl text-brand-100 mb-6"></i>
      <h1 class="text-3xl font-bold text-white mb-3">EventMap</h1>
      <p class="text-brand-200 text-sm leading-relaxed mb-6 text-center">
        Découvre les évènements près de chez toi sur une carte interactive.
      </p>
      <div class="flex flex-col items-center gap-3 w-full">
        <span
          class="inline-flex items-center gap-2 bg-brand-800 text-brand-100 text-xs px-4 py-2 rounded-full"
        >
          <i class="ti ti-ticket"></i> Concerts, festivals, expos...
        </span>
        <span
          class="inline-flex items-center gap-2 bg-brand-800 text-brand-100 text-xs px-4 py-2 rounded-full"
        >
          <i class="ti ti-heart"></i> Sauvegarde tes favoris
        </span>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
      <div class="w-full max-w-sm">
        <!-- Logo mobile uniquement -->
        <div class="lg:hidden mb-6 text-center flex flex-col items-center">
          <i class="ti ti-map-pin text-3xl text-brand-900 mb-2 block"></i>
          <h1 class="text-xl font-bold text-brand-900">EventMap</h1>
        </div>

        <h2 class="text-2xl font-bold text-brand-900 mb-1">Créer un compte</h2>
        <p class="text-sm text-gray-500 mb-6">Rejoins EventMap gratuitement</p>

        <!-- novalidate désactive la validation HTML native — on gère tout en JS -->
        <form @submit.prevent="handleSubmit" novalidate>
          <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1" for="name">Nom</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Ton nom"
              class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
              :class="errors.name ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'"
            />
            <p v-if="errors.name" class="text-accent-600 text-xs mt-1">{{ errors.name }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1" for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="ton@email.com"
              class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
              :class="errors.email ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'"
            />
            <p v-if="errors.email" class="text-accent-600 text-xs mt-1">{{ errors.email }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1" for="password">Mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
              :class="
                errors.password ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'
              "
            />
            <p v-if="errors.password" class="text-accent-600 text-xs mt-1">{{ errors.password }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1" for="password_confirmation"
              >Confirmer le mot de passe</label
            >
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              placeholder="••••••••"
              class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
              :class="
                errors.password_confirmation
                  ? 'border-accent-500'
                  : 'border-gray-200 focus:border-brand-600'
              "
            />
            <p v-if="errors.password_confirmation" class="text-accent-600 text-xs mt-1">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <p v-if="generalError" class="text-accent-600 text-sm mb-4">{{ generalError }}</p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-brand-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <i v-if="loading" class="ti ti-loader animate-spin"></i>
            {{ loading ? 'Création...' : "S'inscrire" }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
          Déjà un compte ?
          <RouterLink to="/login" class="text-brand-700 font-medium hover:underline"
            >Se connecter</RouterLink
          >
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
