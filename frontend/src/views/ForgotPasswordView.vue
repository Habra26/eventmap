<script setup>
import { ref } from 'vue'
import api from '@/axios'

const email = ref('')
const errors = ref({})
const generalError = ref(null)
const successMessage = ref(null)
const loading = ref(false)

function validate() {
  const e = {}
  if (!email.value) {
    e.email = "L'email est requis"
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    e.email = 'Email invalide'
  }
  errors.value = e
  return Object.keys(e).length === 0
}

async function handleSubmit() {
  generalError.value = null
  successMessage.value = null
  if (!validate()) return

  loading.value = true
  try {
    const response = await api.post('/forgot-password', { email: email.value })
    successMessage.value = response.data.message
  } catch (e) {
    generalError.value = e.response?.data?.message ?? 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm">
      <div class="mb-6 text-center flex flex-col items-center">
        <i class="ti ti-map-pin text-3xl text-brand-900 mb-2 block"></i>
        <h1 class="text-xl font-bold text-brand-900">EventMap</h1>
      </div>

      <h2 class="text-2xl font-bold text-brand-900 mb-1">Mot de passe oublié</h2>
      <p class="text-sm text-gray-500 mb-6">
        Entre ton email, on t'envoie un lien de réinitialisation
      </p>

      <form @submit.prevent="handleSubmit" novalidate>
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1" for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="ton@email.com"
            class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
            :class="errors.email ? 'border-accent-500' : 'border-gray-200 focus:border-brand-600'"
          />
          <p v-if="errors.email" class="text-accent-600 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <p v-if="generalError" class="text-accent-600 text-sm mb-4">{{ generalError }}</p>
        <p v-if="successMessage" class="text-green-600 text-sm mb-4">{{ successMessage }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-brand-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <i v-if="loading" class="ti ti-loader animate-spin"></i>
          {{ loading ? 'Envoi...' : 'Envoyer le lien' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-4">
        <RouterLink to="/login" class="text-brand-700 font-medium hover:underline"
          >Retour à la connexion</RouterLink
        >
      </p>
    </div>
  </div>
</template>

<style scoped></style>
