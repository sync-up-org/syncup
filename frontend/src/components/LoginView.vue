<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="logo-badge">SyncUp</div>

      <div class="field-group">
        <input v-model="form.email" class="auth-input" type="email" placeholder="Email"
          @keyup.enter="handleLogin" />
        <input v-model="form.password" class="auth-input" type="password" placeholder="Password"
          @keyup.enter="handleLogin" />
      </div>

      <p v-if="error" class="auth-error">{{ error }}</p>

      <button class="btn-login" :disabled="loading" @click="handleLogin">
        <span v-if="loading" class="spinner" />
        <span v-else>LOGIN</span>
      </button>

      <p class="auth-hint">
        If you don't have an account.<br />
        Please click
        <router-link to="/register" class="auth-link">Sign up</router-link>
      </p>
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
