import { createRouter, createWebHistory } from 'vue-router'
import RegisterView from '@/views/RegisterView.vue'
import LoginView from '@/views/LoginView.vue'
import EventsView from '@/views/EventsView.vue'
import EventDetailView from '@/views/EventDetailView.vue'
import FavoritesView from '@/views/FavoritesView.vue'
import ProfileView from '@/views/ProfileView.vue'
import LegalView from '@/views/LegalView.vue'
import ForgotPasswordView from '@/views/ForgotPasswordView.vue'
import ResetPasswordView from '@/views/ResetPasswordView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { title: "S'inscrire" },
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { title: 'Se connecter' },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { title: 'Mot de passe oublié' },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordView,
      meta: { title: 'Réinitialiser le mot de passe' },
    },
    {
      path: '/',
      name: 'home',
      component: EventsView,
      meta: { title: 'Évènements' },
    },
    {
      path: '/events/:id',
      name: 'event',
      component: EventDetailView,
      meta: { title: 'Détail évènement' },
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: FavoritesView,
      meta: { title: 'Mes favoris', requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: { title: 'Mon profil', requiresAuth: true },
    },
    {
      path: '/legal',
      name: 'legal',
      component: LegalView,
      meta: { title: 'Mentions légales' },
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }
})

router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} · EventMap` : 'EventMap'
})

export default router
