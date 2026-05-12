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

const error = ref(null)

async function handleSubmit() {
  error.value = null
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Une erreur est survenue'
  }
}
</script>

<template>
  <div>
    <h1>Se connecter</h1>

    <form @submit.prevent="handleSubmit">
      <div>
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" />
      </div>

      <div>
        <label for="password">Mot de passe</label>
        <input id="password" v-model="form.password" type="password" />
      </div>

      <p v-if="error">{{ error }}</p>

      <button type="submit">Se connecter</button>
    </form>
  </div>
</template>

<style scoped></style>
