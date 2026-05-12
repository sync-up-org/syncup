import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/index.js'

const routes = [
  {
    path: '/',
    redirect: '/app',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../components/LoginView.vue'),
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../components/RegisterView.vue'),
  },
  {
    path: '/app',
    name: 'Main',
    component: () => import('../components/MainView.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next('/login')
  } else {
    next()
  }
})

export default router
