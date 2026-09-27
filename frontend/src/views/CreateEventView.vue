<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/axios'
import { useToastStore } from '@/stores/toasts'
import Loader from '@/components/Loader.vue'

const route = useRoute()
const router = useRouter()
const toastStore = useToastStore()

// Mode modification si l'URL contient un id (/events/user-X/edit)
const isEdit = computed(() => !!route.params.id)
const eventId = computed(() => route.params.id)

const form = ref({
  title: '',
  description: '',
  date: '',
  time: '',
  venue: '',
  address: '',
  city: '',
  category: '',
})
const image = ref(null)
const imagePreview = ref(null)
const removeExistingImage = ref(false)
const errors = ref({})
const submitting = ref(false)
const loading = ref(false)

// Date du jour au format YYYY-MM-DD (heure locale), pour bloquer les dates passées
const today = (() => {
  const d = new Date()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${d.getFullYear()}-${month}-${day}`
})()

onMounted(async () => {
  if (!isEdit.value) return

  loading.value = true
  try {
    const response = await api.get(`/events/${eventId.value}`)
    const event = response.data

    // Seul le créateur peut accéder à la modification
    if (!event.is_owner) {
      toastStore.error('Tu ne peux pas modifier cet évènement')
      router.replace(`/events/${eventId.value}`)
      return
    }

    form.value = {
      title: event.title ?? '',
      description: event.description ?? '',
      date: event.date ?? '',
      time: event.time ?? '',
      venue: event.venue ?? '',
      address: event.address ?? '',
      city: event.city ?? '',
      category: event.category ?? '',
    }
    imagePreview.value = event.image_url
  } catch (e) {
    toastStore.error('Évènement introuvable')
    router.replace('/')
  } finally {
    loading.value = false
  }
})

function onImageChange(e) {
  const file = e.target.files[0] ?? null
  if (image.value && imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  image.value = file
  imagePreview.value = file ? URL.createObjectURL(file) : null
  if (file) removeExistingImage.value = false
}

function removeImage() {
  if (image.value && imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  // En modification, si c'était l'image déjà enregistrée, on demande sa suppression au backend
  if (isEdit.value && !image.value) removeExistingImage.value = true
  image.value = null
  imagePreview.value = null
}

async function handleSubmit() {
  errors.value = {}
  submitting.value = true

  const formData = new FormData()

  if (isEdit.value) {
    // En modification, on envoie tous les champs, même vides, pour pouvoir effacer une valeur
    Object.entries(form.value).forEach(([key, value]) => formData.append(key, value ?? ''))
    formData.append('_method', 'PUT')
    if (removeExistingImage.value) formData.append('remove_image', '1')
  } else {
    Object.entries(form.value).forEach(([key, value]) => {
      if (value) formData.append(key, value)
    })
  }
  if (image.value) formData.append('image', image.value)

  try {
    const url = isEdit.value ? `/user-events/${eventId.value.replace('user-', '')}` : '/user-events'
    const response = await api.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    toastStore.success(isEdit.value ? 'Évènement modifié' : 'Évènement créé')
    router.push(`/events/user-${response.data.id}`)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else if (e.response?.status === 403) {
      toastStore.error('Tu ne peux pas modifier cet évènement')
    } else {
      toastStore.error('Une erreur est survenue')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-6 py-8">
    <RouterLink
      :to="isEdit ? `/events/${eventId}` : '/'"
      class="text-sm text-brand-700 hover:text-brand-900 transition-colors flex items-center gap-1 mb-6"
    >
      <i class="ti ti-arrow-left"></i>
      {{ isEdit ? "Retour à l'évènement" : 'Retour aux évènements' }}
    </RouterLink>

    <Loader v-if="loading" message="Chargement..." />

    <template v-else>
      <h1 class="text-3xl font-bold text-brand-900 mb-2">
        {{ isEdit ? "Modifier l'évènement" : 'Créer un évènement' }}
      </h1>
      <p class="text-gray-500 mb-8">
        {{
          isEdit
            ? 'Mets à jour les informations de ton évènement.'
            : 'Partage ton évènement avec la communauté.'
        }}
        Les champs marqués d'un * sont obligatoires.
      </p>

      <form @submit.prevent="handleSubmit" class="flex flex-col gap-5">
        <!-- Titre -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            maxlength="255"
            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
            :class="{ 'border-accent-500': errors.title }"
          />
          <p v-if="errors.title" class="text-sm text-accent-600 mt-1">{{ errors.title[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1"
            >Description</label
          >
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            maxlength="2000"
            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
            :class="{ 'border-accent-500': errors.description }"
          ></textarea>
          <p v-if="errors.description" class="text-sm text-accent-600 mt-1">
            {{ errors.description[0] }}
          </p>
        </div>

        <!-- Date et heure -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
            <input
              id="date"
              v-model="form.date"
              type="date"
              :min="today"
              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
              :class="{ 'border-accent-500': errors.date }"
            />
            <p v-if="errors.date" class="text-sm text-accent-600 mt-1">{{ errors.date[0] }}</p>
          </div>
          <div>
            <label for="time" class="block text-sm font-medium text-gray-700 mb-1">Heure</label>
            <input
              id="time"
              v-model="form.time"
              type="time"
              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
              :class="{ 'border-accent-500': errors.time }"
            />
            <p v-if="errors.time" class="text-sm text-accent-600 mt-1">{{ errors.time[0] }}</p>
          </div>
        </div>

        <!-- Lieu -->
        <div>
          <label for="venue" class="block text-sm font-medium text-gray-700 mb-1"
            >Nom du lieu</label
          >
          <input
            id="venue"
            v-model="form.venue"
            type="text"
            maxlength="255"
            placeholder="Ex. : Salle Philharmonique"
            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
            :class="{ 'border-accent-500': errors.venue }"
          />
          <p v-if="errors.venue" class="text-sm text-accent-600 mt-1">{{ errors.venue[0] }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1"
              >Adresse *</label
            >
            <input
              id="address"
              v-model="form.address"
              type="text"
              maxlength="255"
              placeholder="Ex. : Place Saint-Lambert 1"
              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
              :class="{ 'border-accent-500': errors.address }"
            />
            <p v-if="errors.address" class="text-sm text-accent-600 mt-1">
              {{ errors.address[0] }}
            </p>
          </div>
          <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
            <input
              id="city"
              v-model="form.city"
              type="text"
              maxlength="100"
              placeholder="Ex. : Liège"
              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
              :class="{ 'border-accent-500': errors.city }"
            />
            <p v-if="errors.city" class="text-sm text-accent-600 mt-1">{{ errors.city[0] }}</p>
          </div>
        </div>

        <!-- Catégorie -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700 mb-1"
            >Catégorie</label
          >
          <select
            id="category"
            v-model="form.category"
            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 transition"
            :class="{ 'border-accent-500': errors.category }"
          >
            <option value="">Aucune</option>
            <option value="Music">Musique</option>
            <option value="Sports">Sports</option>
            <option value="Arts & Theatre">Arts & Théâtre</option>
            <option value="Family">Famille</option>
            <option value="Film">Cinéma</option>
            <option value="Miscellaneous">Autre</option>
          </select>
          <p v-if="errors.category" class="text-sm text-accent-600 mt-1">
            {{ errors.category[0] }}
          </p>
        </div>

        <!-- Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
          <div v-if="imagePreview" class="relative mb-2">
            <img
              :src="imagePreview"
              alt="Aperçu de l'image"
              class="w-full h-48 object-cover rounded-xl"
            />
            <button
              type="button"
              @click="removeImage"
              class="absolute top-2 right-2 bg-white/90 rounded-full p-2 shadow hover:scale-110 transition-transform"
              aria-label="Retirer l'image"
            >
              <i class="ti ti-x text-gray-600"></i>
            </button>
          </div>
          <label
            v-else
            for="image"
            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-brand-600 hover:bg-brand-50 transition"
          >
            <i class="ti ti-photo text-3xl text-brand-300 mb-1"></i>
            <span class="text-sm text-gray-500">Clique pour choisir une image (5 Mo max)</span>
          </label>
          <input id="image" type="file" accept="image/*" class="hidden" @change="onImageChange" />
          <p v-if="errors.image" class="text-sm text-accent-600 mt-1">{{ errors.image[0] }}</p>
        </div>

        <button
          type="submit"
          :disabled="submitting"
          class="bg-brand-900 text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-brand-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <i v-if="submitting" class="ti ti-loader animate-spin"></i>
          <template v-if="submitting">{{
            isEdit ? 'Enregistrement...' : 'Création en cours...'
          }}</template>
          <template v-else>{{
            isEdit ? 'Enregistrer les modifications' : "Créer l'évènement"
          }}</template>
        </button>
      </form>
    </template>
  </div>
</template>

<style scoped></style>
