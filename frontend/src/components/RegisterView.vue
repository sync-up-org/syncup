<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="logo-badge">SyncUp</div>

      <div class="field-group">
        <input v-model="form.username" class="auth-input" type="text" placeholder="Username"
          @keyup.enter="handleRegister" />
        <input v-model="form.email" class="auth-input" type="email" placeholder="Email"
          @keyup.enter="handleRegister" />
        <input v-model="form.password" class="auth-input" type="password" placeholder="Password"
          @keyup.enter="handleRegister" />
      </div>

      <p v-if="error" class="auth-error">{{ error }}</p>

      <button class="btn-register" :disabled="loading" @click="handleRegister">
        <span v-if="loading" class="spinner" />
        <span v-else>REGISTER</span>
      </button>

      <p class="auth-hint">
        If you already have an account.<br />
        Please click
        <router-link to="/login" class="auth-link">Login</router-link>
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

const form = reactive({ username: '', email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleRegister() {
  error.value = ''
  if (!form.username || !form.email || !form.password) {
    error.value = 'Please fill in all fields.'
    return
  }
  loading.value = true
  try {
    await authStore.register(form.username, form.email, form.password)
    router.push('/login')
  } catch (e) {
    error.value = e.message || 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped src="../styles/RegisterView.css"></style>
