<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toasts'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

const name = ref('')
const email = ref('')

const currentPassword = ref('')
const password = ref('')
const passwordConfirmation = ref('')

onMounted(() => {
  name.value = authStore.user?.name ?? ''
  email.value = authStore.user?.email ?? ''
})

async function updateProfile() {
  try {
    const message = await authStore.updateProfile(name.value, email.value)
    toastStore.success(message)
  } catch (e) {
    toastStore.error(e.response?.data?.message ?? 'Erreur lors de la mise à jour')
  }
}

async function updatePassword() {
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
  } catch (e) {
    toastStore.error(e.response?.data?.message ?? 'Erreur lors du changement de mot de passe')
  }
}

async function deleteAccount() {
  if (!confirm('Es-tu sûr de vouloir supprimer ton compte ? Cette action est irréversible.')) {
    return
  }
  try {
    await authStore.deleteAccount()
    toastStore.success('Compte supprimé')
    router.push('/register')
  } catch (e) {
    toastStore.error('Erreur lors de la suppression du compte')
  }
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
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm mb-4 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      />

      <label class="block text-sm text-gray-600 mb-1">Email</label>
      <input
        v-model="email"
        type="email"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm mb-4 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      />

      <button
        @click="updateProfile"
        class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors"
      >
        Enregistrer
      </button>
    </section>

    <!-- Mot de passe -->
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Changer le mot de passe</h2>

      <label class="block text-sm text-gray-600 mb-1">Mot de passe actuel</label>
      <input
        v-model="currentPassword"
        type="password"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm mb-4 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      />

      <label class="block text-sm text-gray-600 mb-1">Nouveau mot de passe</label>
      <input
        v-model="password"
        type="password"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm mb-4 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      />

      <label class="block text-sm text-gray-600 mb-1">Confirmer le nouveau mot de passe</label>
      <input
        v-model="passwordConfirmation"
        type="password"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm mb-4 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
      />

      <button
        @click="updatePassword"
        class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors"
      >
        Changer le mot de passe
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
        class="bg-red-600 text-white text-sm px-5 py-2.5 rounded-xl hover:bg-red-700 transition-colors"
      >
        Supprimer mon compte
      </button>
    </section>
  </div>
</template>

<style scoped></style>
