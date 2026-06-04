import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  let nextId = 0

  function addToast(message, type = 'success') {
    const id = nextId++
    toasts.value.push({ id, message, type })
    setTimeout(() => removeToast(id), 4000)
  }

  function removeToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function success(message) {
    addToast(message, 'success')
  }

  function error(message) {
    addToast(message, 'error')
  }

  return { toasts, addToast, removeToast, success, error }
})
