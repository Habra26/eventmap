<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const errors = ref({})
const generalError = ref(null)
const loading = ref(false)

function validate() {
  const e = {}
  if (!form.value.email) {
    e.email = "L'email est requis"
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    e.email = 'Email invalide'
  }
  if (!form.value.password) {
    e.password = 'Le mot de passe est requis'
  }
  errors.value = e
  return Object.keys(e).length === 0
}

async function handleSubmit() {
  generalError.value = null
  if (!validate()) return

  loading.value = true
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/')
  } catch (e) {
    generalError.value = e.response?.data?.message ?? 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex">
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
        <div class="lg:hidden mb-6 text-center flex flex-col items-center">
          <i class="ti ti-map-pin text-3xl text-brand-900 mb-2 block"></i>
          <h1 class="text-xl font-bold text-brand-900">EventMap</h1>
        </div>

        <h2 class="text-2xl font-bold text-brand-900 mb-1">Se connecter</h2>
        <p class="text-sm text-gray-500 mb-6">Accède à tes évènements favoris</p>

        <form @submit.prevent="handleSubmit" novalidate>
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
            <RouterLink
              to="/forgot-password"
              class="text-xs text-brand-700 hover:underline mt-1 inline-block"
            >
              Mot de passe oublié ?
            </RouterLink>
          </div>

          <p v-if="generalError" class="text-accent-600 text-sm mb-4">{{ generalError }}</p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-brand-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <i v-if="loading" class="ti ti-loader animate-spin"></i>
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
          Pas encore de compte ?
          <RouterLink to="/register" class="text-brand-700 font-medium hover:underline"
            >S'inscrire</RouterLink
          >
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
