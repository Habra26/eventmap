<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/axios'
import { useToastStore } from '@/stores/toasts'
import Loader from '@/components/Loader.vue'
import ErrorMessage from '@/components/ErrorMessage.vue'

const toastStore = useToastStore()

const events = ref([])
const loading = ref(true)
const error = ref(null)
const deletingId = ref(null)

const upcomingEvents = computed(() => events.value.filter((e) => !e.is_past))
const pastEvents = computed(() => events.value.filter((e) => e.is_past).reverse())

onMounted(async () => {
  try {
    const response = await api.get('/my-events')
    events.value = response.data
  } catch (e) {
    error.value = 'Impossible de charger tes évènements'
  } finally {
    loading.value = false
  }
})

async function deleteEvent(event) {
  if (!confirm(`Supprimer définitivement « ${event.title} » ?`)) return

  deletingId.value = event.id
  try {
    await api.delete(`/user-events/${event.id.replace('user-', '')}`)
    events.value = events.value.filter((e) => e.id !== event.id)
    toastStore.success('Évènement supprimé')
  } catch (e) {
    toastStore.error('Impossible de supprimer cet évènement')
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-brand-900 mb-1">Mes évènements</h1>

    <Loader v-if="loading" message="Chargement..." />

    <ErrorMessage v-else-if="error" :message="error" />

    <!-- Aucun évènement -->
    <div v-else-if="events.length === 0" class="text-center py-16">
      <i class="ti ti-calendar-plus text-5xl text-brand-300 mb-4 block"></i>
      <p class="text-gray-600 mb-6">Tu n'as encore créé aucun évènement.</p>
      <RouterLink
        to="/events/create"
        class="bg-brand-900 text-white text-sm px-5 py-2.5 rounded-full hover:bg-brand-800 transition-colors inline-flex items-center gap-1"
      >
        <i class="ti ti-plus"></i> Créer mon premier évènement
      </RouterLink>
    </div>

    <template v-else>
      <!-- À venir -->
      <section class="mb-10">
        <h2 class="text-lg font-semibold text-brand-900 mb-3">
          À venir ({{ upcomingEvents.length }})
        </h2>
        <p v-if="upcomingEvents.length === 0" class="text-sm text-gray-500">
          Aucun évènement à venir.
        </p>
        <div v-else class="flex flex-col gap-3">
          <div
            v-for="event in upcomingEvents"
            :key="event.id"
            class="flex flex-col sm:flex-row sm:items-center gap-4 bg-white border border-gray-100 rounded-2xl p-3"
          >
            <img
              v-if="event.image_url"
              :src="event.image_url"
              :alt="event.title"
              class="w-full sm:w-28 h-28 sm:h-20 object-cover rounded-xl"
            />
            <div
              v-else
              class="w-full sm:w-28 h-28 sm:h-20 bg-brand-50 rounded-xl flex items-center justify-center"
            >
              <i class="ti ti-calendar-event text-2xl text-brand-300"></i>
            </div>

            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">{{ event.title }}</p>
              <p class="text-sm text-gray-500">
                {{ event.date }}<span v-if="event.time"> à {{ event.time }}</span> ·
                {{ event.city }}
              </p>
            </div>

            <div class="flex flex-wrap gap-2">
              <RouterLink
                :to="`/events/${event.id}`"
                class="border border-gray-200 text-brand-800 hover:bg-gray-50 transition-colors px-3 py-1.5 rounded-xl text-sm flex items-center gap-1"
              >
                <i class="ti ti-eye"></i> Voir
              </RouterLink>
              <RouterLink
                :to="`/events/${event.id}/edit`"
                class="border border-gray-200 text-brand-800 hover:bg-gray-50 transition-colors px-3 py-1.5 rounded-xl text-sm flex items-center gap-1"
              >
                <i class="ti ti-pencil"></i> Modifier
              </RouterLink>
              <button
                @click="deleteEvent(event)"
                :disabled="deletingId === event.id"
                class="border border-gray-200 text-accent-600 hover:bg-gray-50 transition-colors px-3 py-1.5 rounded-xl text-sm flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="deletingId === event.id" class="ti ti-loader animate-spin"></i>
                <i v-else class="ti ti-trash"></i>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Passés -->
      <section v-if="pastEvents.length > 0">
        <h2 class="text-lg font-semibold text-gray-500 mb-3">Passés ({{ pastEvents.length }})</h2>
        <div class="flex flex-col gap-3">
          <div
            v-for="event in pastEvents"
            :key="event.id"
            class="flex flex-col sm:flex-row sm:items-center gap-4 bg-white border border-gray-100 rounded-2xl p-3 opacity-70"
          >
            <img
              v-if="event.image_url"
              :src="event.image_url"
              :alt="event.title"
              class="w-full sm:w-28 h-28 sm:h-20 object-cover rounded-xl grayscale"
            />
            <div
              v-else
              class="w-full sm:w-28 h-28 sm:h-20 bg-gray-100 rounded-xl flex items-center justify-center"
            >
              <i class="ti ti-calendar-event text-2xl text-gray-300"></i>
            </div>

            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-700 truncate">{{ event.title }}</p>
              <p class="text-sm text-gray-500">
                {{ event.date }}<span v-if="event.time"> à {{ event.time }}</span> ·
                {{ event.city }}
              </p>
            </div>

            <div class="flex flex-wrap gap-2">
              <RouterLink
                :to="`/events/${event.id}`"
                class="border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors px-3 py-1.5 rounded-xl text-sm flex items-center gap-1"
              >
                <i class="ti ti-eye"></i> Voir
              </RouterLink>
              <button
                @click="deleteEvent(event)"
                :disabled="deletingId === event.id"
                class="border border-gray-200 text-accent-600 hover:bg-gray-50 transition-colors px-3 py-1.5 rounded-xl text-sm flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="deletingId === event.id" class="ti ti-loader animate-spin"></i>
                <i v-else class="ti ti-trash"></i>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<style scoped></style>
