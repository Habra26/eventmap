<script setup>
import { useToastStore } from '@/stores/toasts'

const toastStore = useToastStore()
</script>

<template>
  <!-- Positionné en fixed en bas à droite -->
  <!-- Les toasts s'empilent verticalement -->
  <div class="fixed bottom-4 right-4 z-9999 flex flex-col gap-2">
    <div
      v-for="toast in toastStore.toasts"
      :key="toast.id"
      class="flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm text-white min-w-62.5"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      <!-- Icône adaptée au type de toast -->
      <i :class="toast.type === 'success' ? 'ti ti-circle-check' : 'ti ti-alert-circle'"></i>
      <span class="flex-1">{{ toast.message }}</span>
      <!-- Bouton de fermeture manuelle, la fermeture automatique est gérée par le store -->
      <button @click="toastStore.removeToast(toast.id)" class="hover:opacity-70">
        <i class="ti ti-x"></i>
      </button>
    </div>
  </div>
</template>

<style scoped></style>
