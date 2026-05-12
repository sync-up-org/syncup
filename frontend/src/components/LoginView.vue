<template>
  <div class="auth-page">
    <div class="auth-brand">
      <div class="brand-content">
        <div class="brand-logo">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="32" height="32" rx="7" fill="currentColor" opacity="0.1"/>
            <path d="M25 13A11 11 0 0 0 7 16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
            <path d="M25 13l-4.5 2M25 13l-2-4.5" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 19A11 11 0 0 0 25 16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" opacity="0.5"/>
            <path d="M7 19l4.5-2M7 19l2 4.5" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
          </svg>
        </div>
        <h1 class="brand-title">SyncUp</h1>
        <p class="brand-tagline">Sync your tasks. Elevate your workflow.</p>
        <div class="brand-features">
          <div class="feature">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <span>Real-time task sync across devices</span>
          </div>
          <div class="feature">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <span>Organise with status boards</span>
          </div>
          <div class="feature">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <span>Track progress effortlessly</span>
          </div>
        </div>
      </div>
    </div>

    <div class="auth-main">
      <div class="auth-card">
        <div class="card-header">
          <div class="card-logo">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="32" height="32" rx="7" fill="currentColor" opacity="0.1"/>
              <path d="M25 13A11 11 0 0 0 7 16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
              <path d="M25 13l-4.5 2M25 13l-2-4.5" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M7 19A11 11 0 0 0 25 16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" opacity="0.5"/>
              <path d="M7 19l4.5-2M7 19l2 4.5" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
            </svg>
          </div>
          <h2 class="card-title">Welcome back</h2>
          <p class="card-subtitle">Sign in to your account to continue.</p>
        </div>

        <form class="auth-form" @submit.prevent="handleLogin">
          <div class="field-group">
            <label class="field-label" for="email">Email</label>
            <input id="email" v-model="form.email" class="auth-input" type="email" placeholder="name@example.com"
              autocomplete="email" @keyup.enter="handleLogin" />
          </div>

          <div class="field-group">
            <label class="field-label" for="password">Password</label>
            <div class="pw-field">
              <input id="password" v-model="form.password" class="auth-input" :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password" autocomplete="current-password" @keyup.enter="handleLogin" />
              <button class="pw-toggle" type="button" :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = !showPassword" tabindex="-1">
                <svg v-if="showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
            </div>
          </div>

          <p v-if="error" class="auth-error">{{ error }}</p>

          <button class="btn-submit" :disabled="loading">
            <span v-if="loading" class="spinner" />
            <span v-else>Sign in</span>
          </button>
        </form>

        <p class="auth-hint">
          Don't have an account?
          <router-link to="/register" class="auth-link">Create one</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/index.js'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)



async function handleLogin() {
  error.value = ''
  if (!form.email || !form.password) {
    error.value = 'Please fill in all fields.'
    return
  }
  loading.value = true
  try {
    await authStore.login(form.email, form.password)
    await authStore.fetchProfile()
    router.push('/app')
  } catch (e) {
    error.value = e.message || 'Invalid credentials. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped src="../styles/LoginView.css"></style>
