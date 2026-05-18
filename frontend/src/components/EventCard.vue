<script setup>
defineProps({
  event: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div
    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
  >
    <img
      v-if="event.image_url"
      :src="event.image_url"
      :alt="event.title"
      class="w-full h-40 object-cover"
    />
    <div v-else class="w-full h-40 bg-blue-50 flex items-center justify-center">
      <i class="ti ti-calendar-event text-4xl text-blue-300"></i>
    </div>

    <div class="p-4">
      <p class="font-medium text-gray-800 truncate">{{ event.title }}</p>
      <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <i class="ti ti-map-pin text-blue-600"></i> {{ event.city ?? 'Ville inconnue' }}
      </p>
      <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
        <i class="ti ti-calendar text-blue-600"></i> {{ event.date ?? 'Date inconnue' }}
      </p>
      <RouterLink
        :to="{ name: 'event', params: { id: event.id } }"
        class="mt-3 inline-block text-sm text-blue-700 hover:underline"
      >
        Voir le détail →
      </RouterLink>
    </div>
  </div>
</template>

<style scoped></style>
