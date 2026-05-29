<script setup>
import { useToastStore } from '@/stores/toasts'

const toastStore = useToastStore()
</script>

<template>
  <div class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2">
    <div
      v-for="toast in toastStore.toasts"
      :key="toast.id"
      class="flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm text-white min-w-[250px]"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      <i :class="toast.type === 'success' ? 'ti ti-circle-check' : 'ti ti-alert-circle'"></i>
      <span class="flex-1">{{ toast.message }}</span>
      <button @click="toastStore.removeToast(toast.id)" class="hover:opacity-70">
        <i class="ti ti-x"></i>
      </button>
    </div>
  </div>
</template>

<style scoped></style>
