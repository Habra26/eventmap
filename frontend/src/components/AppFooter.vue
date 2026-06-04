<script setup>
const year = new Date().getFullYear()
</script>

<template>
  <footer class="bg-white border-t border-gray-100 px-6 py-6 mt-auto">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="text-center sm:text-left">
        <p
          class="text-brand-900 font-semibold flex items-center gap-1 justify-center sm:justify-start"
        >
          <i class="ti ti-map-pin"></i> EventMap
        </p>
        <p class="text-xs text-gray-400 mt-1">
          © {{ year }} EventMap · Données fournies par Ticketmaster
        </p>
      </div>
      <div class="flex items-center gap-4 text-sm">
        <RouterLink to="/legal" class="text-gray-500 hover:text-brand-900 transition-colors">
          Mentions légales
        </RouterLink>
      </div>
    </div>
  </footer>
</template>

<style scoped></style>
