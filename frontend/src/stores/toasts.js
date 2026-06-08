import { defineStore } from 'pinia'
import { ref } from 'vue'

// Store global pour les notifications temporaires (toasts)
// Utilisé par l'intercepteur axios et les actions utilisateur pour afficher des messages de retour
export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  // Compteur incrémental pour générer des IDs uniques même si plusieurs toasts sont ajoutés rapidement
  let nextId = 0

  // Ajoute un toast et programme sa suppression automatique après 4 secondes
  function addToast(message, type = 'success') {
    const id = nextId++
    toasts.value.push({ id, message, type })
    setTimeout(() => removeToast(id), 4000)
  }

  function removeToast(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  // Raccourcis pour ne pas avoir à passer le type à chaque appel
  function success(message) {
    addToast(message, 'success')
  }

  function error(message) {
    addToast(message, 'error')
  }

  return { toasts, addToast, removeToast, success, error }
})
