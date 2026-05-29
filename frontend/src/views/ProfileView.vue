<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const profileMessage = ref('')
const profileError = ref('')

const currentPassword = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const passwordMessage = ref('')
const passwordError = ref('')

onMounted(() => {
  name.value = authStore.user?.name ?? ''
  email.value = authStore.user?.email ?? ''
})

async function updateProfile() {
  profileMessage.value = ''
  profileError.value = ''
  try {
    profileMessage.value = await authStore.updateProfile(name.value, email.value)
  } catch (e) {
    profileError.value = e.response?.data?.message ?? 'Erreur lors de la mise à jour'
  }
}

async function updatePassword() {
  passwordMessage.value = ''
  passwordError.value = ''
  try {
    passwordMessage.value = await authStore.updatePassword(
      currentPassword.value,
      password.value,
      passwordConfirmation.value,
    )
    currentPassword.value = ''
    password.value = ''
    passwordConfirmation.value = ''
  } catch (e) {
    passwordError.value = e.response?.data?.message ?? 'Erreur lors du changement de mot de passe'
  }
}

async function deleteAccount() {
  if (!confirm('Es-tu sûr de vouloir supprimer ton compte ? Cette action est irréversible.')) {
    return
  }
  try {
    await authStore.deleteAccount()
    router.push('/register')
  } catch (e) {
    profileError.value = 'Erreur lors de la suppression du compte'
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-medium mb-8">Mon profil</h1>

    <!-- Infos personnelles -->
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-medium mb-4">Informations personnelles</h2>

      <label class="block text-sm text-gray-600 mb-1">Nom</label>
      <input
        v-model="name"
        type="text"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"
      />

      <label class="block text-sm text-gray-600 mb-1">Email</label>
      <input
        v-model="email"
        type="email"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"
      />

      <button
        @click="updateProfile"
        class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors"
      >
        Enregistrer
      </button>

      <p v-if="profileMessage" class="text-green-600 text-sm mt-3">{{ profileMessage }}</p>
      <p v-if="profileError" class="text-red-500 text-sm mt-3">{{ profileError }}</p>
    </section>

    <!-- Mot de passe -->
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-medium mb-4">Changer le mot de passe</h2>

      <label class="block text-sm text-gray-600 mb-1">Mot de passe actuel</label>
      <input
        v-model="currentPassword"
        type="password"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"
      />

      <label class="block text-sm text-gray-600 mb-1">Nouveau mot de passe</label>
      <input
        v-model="password"
        type="password"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"
      />

      <label class="block text-sm text-gray-600 mb-1">Confirmer le nouveau mot de passe</label>
      <input
        v-model="passwordConfirmation"
        type="password"
        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-blue-500"
      />

      <button
        @click="updatePassword"
        class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors"
      >
        Changer le mot de passe
      </button>

      <p v-if="passwordMessage" class="text-green-600 text-sm mt-3">{{ passwordMessage }}</p>
      <p v-if="passwordError" class="text-red-500 text-sm mt-3">{{ passwordError }}</p>
    </section>

    <!-- Suppression compte -->
    <section class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
      <h2 class="text-lg font-medium mb-2 text-red-600">Zone dangereuse</h2>
      <p class="text-sm text-gray-500 mb-4">
        La suppression de ton compte est définitive et supprime tous tes favoris.
      </p>
      <button
        @click="deleteAccount"
        class="bg-red-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
      >
        Supprimer mon compte
      </button>
    </section>
  </div>
</template>

<style scoped></style>
