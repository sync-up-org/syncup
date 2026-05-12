<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="close">
      <div class="profile-modal">
        <button class="close-btn" @click="close">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>

        <!-- Avatar -->
        <div class="avatar-wrap">
          <div class="avatar">
            <img v-if="authStore.user?.avatar" :src="authStore.user.avatar" alt="avatar" />
            <svg v-else width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <circle cx="12" cy="9" r="4" />
              <path d="M4 20c.6-4 3.8-7 8-7s7.4 3 8 7" />
            </svg>
          </div>
        </div>

        <!-- View Mode -->
        <template v-if="!editing">
          <div class="username-display">
            <span>{{ authStore.user?.username || 'Username' }}</span>
            <div class="underline" />
          </div>
          <div class="profile-actions">
            <button class="btn-logout" @click="handleLogout">LOGOUT</button>
            <button class="btn-update" @click="editing = true">UPDATE</button>
          </div>
        </template>

        <!-- Edit Mode -->
        <template v-else>
          <div class="edit-field">
            <input
              v-model="newUsername"
              class="edit-input"
              type="text"
              placeholder="New username"
            />
            <div class="underline" />
          </div>
          <p v-if="editError" class="edit-error">{{ editError }}</p>
          <div class="profile-actions">
            <button class="btn-logout" @click="cancelEdit">CANCEL</button>
            <button class="btn-update" @click="confirmUpdate">CONFIRM</button>
          </div>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/index.js'

const emit = defineEmits(['close'])
const authStore = useAuthStore()
const router = useRouter()

const editing = ref(false)
const newUsername = ref(authStore.user?.username || '')
const editError = ref('')

function close() { emit('close') }

function handleLogout() {
  authStore.logout()
  router.push('/login')
}

function cancelEdit() {
  editing.value = false
  editError.value = ''
  newUsername.value = authStore.user?.username || ''
}

async function confirmUpdate() {
  if (!newUsername.value.trim()) {
    editError.value = 'Username cannot be empty.'
    return
  }
  // Replace with real API call:
  // await fetch('/api/user/update', { method: 'PUT', body: JSON.stringify({ username: newUsername.value }), headers: {...} })
  authStore.updateUser({ ...authStore.user, username: newUsername.value })
  editing.value = false
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn { from { opacity: 0; } }

.profile-modal {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: 28px 28px 24px;
  width: 280px;
  position: relative;
  box-shadow: var(--shadow);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  animation: slideUp 0.2s ease;
}

@keyframes slideUp { from { transform: translateY(16px); opacity: 0; } }

.close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
  border-radius: var(--radius-sm);
  display: flex;
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--text-primary);
}

.avatar-wrap {
  margin-top: 8px;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--bg-card);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  color: var(--text-muted);
  border: 2px solid var(--border-color);
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.username-display {
  width: 100%;
  text-align: center;
}

.username-display span {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.underline {
  height: 1px;
  background: var(--border-color);
  margin-top: 8px;
}

.edit-field {
  width: 100%;
}

.edit-input {
  width: 100%;
  background: none;
  border: none;
  border-bottom: 1.5px solid var(--border-color);
  color: var(--text-primary);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem;
  padding: 6px 0;
  outline: none;
  text-align: center;
  transition: border-color 0.2s;
}

.edit-input:focus {
  border-bottom-color: var(--accent-blue);
}

.edit-input::placeholder {
  color: var(--text-muted);
}

.edit-error {
  color: var(--accent-red);
  font-size: 0.8rem;
  text-align: center;
  margin-top: -8px;
}

.profile-actions {
  display: flex;
  gap: 12px;
  width: 100%;
  justify-content: center;
}

.btn-logout,
.btn-update {
  border: none;
  border-radius: var(--radius-pill);
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: 0.8rem;
  letter-spacing: 1px;
  padding: 9px 22px;
  cursor: pointer;
  transition: opacity 0.15s, transform 0.1s;
}

.btn-logout {
  background: var(--accent-red);
  color: #fff;
}

.btn-update {
  background: var(--accent-green);
  color: #fff;
}

.btn-logout:hover,
.btn-update:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}
</style>