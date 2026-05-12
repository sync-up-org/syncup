import { defineStore } from 'pinia'
import { api } from '../api/index.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async register(username, email, password) {
      const data = await api.register({ username, email, password })
      return data
    },

    async login(email, password) {
      const data = await api.login({ email, password })
      this.token = data.token
      localStorage.setItem('auth_token', data.token)
      return data
    },

    async fetchProfile() {
      const user = await api.getProfile()
      this.user = user
      return user
    },

    async updateProfile(payload) {
      const data = await api.updateProfile(payload)
      this.user = data.data || data
      return data
    },

    async deleteUser(id) {
      const data = await api.deleteUser(id)
      this.logout()
      return data
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('auth_token')
    },
  },
})

export const useTaskStore = defineStore('task', {
  state: () => ({
    tasks: [],
    searchQuery: '',
    loading: false,
  }),

  actions: {
    async fetchTasks(params = {}) {
      this.loading = true
      try {
        const data = await api.getTasks(params)
        this.tasks = data.data || []
        return data
      } finally {
        this.loading = false
      }
    },

    async addTask(payload) {
      const data = await api.createTask(payload)
      this.tasks.unshift(data.data || data)
      return data
    },

    async updateTask(id, payload) {
      const data = await api.updateTask(id, payload)
      const idx = this.tasks.findIndex((t) => t.id === id)
      if (idx !== -1) this.tasks[idx] = data.data || data
      return data
    },

    async deleteTask(id) {
      await api.deleteTask(id)
      this.tasks = this.tasks.filter((t) => t.id !== id)
    },

    setSearch(q) {
      this.searchQuery = q
    },
  },
})
