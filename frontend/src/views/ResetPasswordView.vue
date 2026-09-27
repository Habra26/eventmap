<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/axios'

const route = useRoute()
const router = useRouter()

const password = ref('')
const passwordConfirmation = ref('')
const errors = ref({})
const generalError = ref(null)
const successMessage = ref(null)
const loading = ref(false)

function validate() {
  const e = {}
  if (!password.value) {
    e.password = 'Le nouveau mot de passe est requis'
  } else if (password.value.length < 8) {
    e.password = 'Au moins 8 caractères'
  }
  if (password.value !== passwordConfirmation.value) {
    e.passwordConfirmation = 'Les mots de passe ne correspondent pas'
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
    const response = await api.post('/reset-password', {
      token: route.query.token,
      email: route.query.email,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    successMessage.value = response.data.message
    setTimeout(() => router.push('/login'), 2000)
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

      <h2 class="text-2xl font-bold text-brand-900 mb-1">Nouveau mot de passe</h2>
      <p class="text-sm text-gray-500 mb-6">Choisis un nouveau mot de passe pour ton compte</p>

      <form @submit.prevent="handleSubmit" novalidate>
        <div class="mb-4">
          <label class="block text-sm text-gray-600 mb-1" for="password"
            >Nouveau mot de passe</label
          >
          <input
            id="password"
            v-model="password"
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
          <label class="block text-sm text-gray-600 mb-1" for="passwordConfirmation"
            >Confirmer le mot de passe</label
          >
          <input
            id="passwordConfirmation"
            v-model="passwordConfirmation"
            type="password"
            placeholder="••••••••"
            class="w-full bg-gray-50 border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-100 transition"
            :class="
              errors.passwordConfirmation
                ? 'border-accent-500'
                : 'border-gray-200 focus:border-brand-600'
            "
          />
          <p v-if="errors.passwordConfirmation" class="text-accent-600 text-xs mt-1">
            {{ errors.passwordConfirmation }}
          </p>
        </div>

        <p v-if="generalError" class="text-accent-600 text-sm mb-4">{{ generalError }}</p>
        <p v-if="successMessage" class="text-green-600 text-sm mb-4">{{ successMessage }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-brand-900 text-white py-3 rounded-xl text-sm font-medium hover:bg-brand-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <i v-if="loading" class="ti ti-loader animate-spin"></i>
          {{ loading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped></style>
