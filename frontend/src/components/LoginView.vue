<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="logo-badge">SyncUp</div>

      <div class="field-group">
        <input
          v-model="form.username"
          class="auth-input"
          type="text"
          placeholder="Username"
          @keyup.enter="handleLogin"
        />
        <input
          v-model="form.password"
          class="auth-input"
          type="password"
          placeholder="Password"
          @keyup.enter="handleLogin"
        />
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

const form = reactive({ username: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  if (!form.username || !form.password) {
    error.value = 'Please fill in all fields.'
    return
  }
  loading.value = true
  try {
    // Replace with your real API call:
    // const res = await fetch('/api/login', { method: 'POST', body: JSON.stringify(form), headers: { 'Content-Type': 'application/json' } })
    // const data = await res.json()
    // authStore.login(data.user, data.token)

    // Simulated for now:
    await new Promise(r => setTimeout(r, 600))
    authStore.login({ username: form.username, avatar: null }, 'demo-token')
    router.push('/app')
  } catch (e) {
    error.value = 'Invalid credentials. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
}

.auth-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: 40px 32px 32px;
  width: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow);
}

.logo-badge {
  background: var(--accent-green);
  color: #fff;
  font-family: 'Space Mono', monospace;
  font-weight: 700;
  font-size: 1.2rem;
  padding: 8px 22px;
  border-radius: var(--radius-pill);
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.field-group {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.auth-input {
  width: 100%;
  background: var(--bg-input);
  border: none;
  border-radius: var(--radius-sm);
  color: var(--text-primary);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem;
  padding: 12px 14px;
  outline: none;
  transition: box-shadow 0.2s;
}

.auth-input:focus {
  box-shadow: 0 0 0 2px var(--accent-blue);
}

.auth-input::placeholder {
  color: var(--text-muted);
}

.auth-error {
  color: var(--accent-red);
  font-size: 0.82rem;
  text-align: center;
}

.btn-login {
  background: var(--accent-green);
  color: #fff;
  border: none;
  border-radius: var(--radius-pill);
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 1.5px;
  padding: 11px 36px;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-login:hover:not(:disabled) {
  background: #43a047;
  transform: translateY(-1px);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin { to { transform: rotate(360deg); } }

.auth-hint {
  font-size: 0.8rem;
  color: var(--text-secondary);
  text-align: center;
  line-height: 1.6;
}

.auth-link {
  color: var(--accent-blue);
  text-decoration: none;
}

.auth-link:hover {
  text-decoration: underline;
}
</style>