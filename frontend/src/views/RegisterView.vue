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

async function handleSubmit() {
  error.value = null
  try {
    await authStore.register(
      form.value.name,
      form.value.email,
      form.value.password,
      form.value.password_confirmation
    )
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Une erreur est survenue'
  }
}
</script>

<template>
  <div>
    <h1>Créer un compte</h1>

    <form @submit.prevent="handleSubmit">
      <div>
        <label for="name">Nom</label>
        <input id="name" v-model="form.name" type="text" />
      </div>

      <div>
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" />
      </div>

      <div>
        <label for="password">Mot de passe</label>
        <input id="password" v-model="form.password" type="password" />
      </div>

      <div>
        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input id="password_confirmation" v-model="form.password_confirmation" type="password" />
      </div>

      <p v-if="error">{{ error }}</p>

      <button type="submit">S'inscrire</button>
    </form>
  </div>
</template>

<style scoped>
</style>