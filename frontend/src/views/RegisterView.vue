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
  password_confirmation: '',
})

const error = ref(null)
const errors = ref({})

async function handleSubmit() {
  error.value = null
  errors.value = {}
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
      errors.value = e.response.data.errors
    } else {
      error.value = e.response?.data?.message ?? 'Une erreur est survenue'
    }
  }
}
</script>

<template>
  <div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 bg-blue-700 flex-col justify-center items-center px-12">
      <i class="ti ti-map-pin text-5xl text-blue-100 mb-6"></i>
      <h1 class="text-3xl font-medium text-blue-100 mb-3">EventMap</h1>
      <p class="text-blue-300 text-sm leading-relaxed mb-6 text-center">
        Découvre les évènements près de chez toi sur une carte interactive.
      </p>
      <div class="flex flex-col items-center gap-3 w-full">
        <span
          class="inline-flex items-center gap-2 bg-blue-800 text-blue-200 text-xs px-4 py-2 rounded-lg"
        >
          <i class="ti ti-ticket"></i> Concerts, festivals, expos...
        </span>
        <span
          class="inline-flex items-center gap-2 bg-blue-800 text-blue-200 text-xs px-4 py-2 rounded-lg"
        >
          <i class="ti ti-heart"></i> Sauvegarde tes favoris
        </span>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
      <div class="w-full max-w-sm">
        <div class="lg:hidden mb-6 text-center flex flex-col items-center">
          <i class="ti ti-map-pin text-3xl text-blue-700 mb-2 block"></i>
          <h1 class="text-xl font-medium text-blue-700">EventMap</h1>
        </div>

        <h2 class="text-xl font-medium mb-1">Créer un compte</h2>
        <p class="text-sm text-gray-500 mb-6">Rejoins EventMap gratuitement</p>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="block text-sm text-gray-500 mb-1" for="name">Nom</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Ton nom"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
            />
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-500 mb-1" for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="ton@email.com"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
            />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-500 mb-1" for="password">Mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
            />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-gray-500 mb-1" for="password_confirmation"
              >Confirmer le mot de passe</label
            >
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              placeholder="••••••••"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
            />
            <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">
              {{ errors.password_confirmation[0] }}
            </p>
          </div>

          <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>

          <button
            type="submit"
            class="w-full bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors"
          >
            S'inscrire
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
          Déjà un compte ?
          <RouterLink to="/login" class="text-blue-700 hover:underline">Se connecter</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
