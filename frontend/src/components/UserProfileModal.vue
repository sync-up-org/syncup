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

        <div class="avatar-wrap">
          <div class="avatar">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <circle cx="12" cy="9" r="4" />
              <path d="M4 20c.6-4 3.8-7 8-7s7.4 3 8 7" />
            </svg>
          </div>
        </div>

        <template v-if="!editing">
          <div class="username-display">
            <span>{{ authStore.user?.username || 'Username' }}</span>
            <div class="underline" />
          </div>
          <div class="profile-actions">
            <button class="btn-logout" @click="handleLogout">LOGOUT</button>
            <button class="btn-update" @click="startEdit">UPDATE</button>
          </div>
        </template>

        <template v-else>
          <div class="edit-field">
            <input v-model="newUsername" class="edit-input" type="text" placeholder="New username" />
            <div class="underline" />
          </div>
          <p v-if="editError" class="edit-error">{{ editError }}</p>
          <div class="profile-actions">
            <button class="btn-logout" @click="cancelEdit">CANCEL</button>
            <button class="btn-update" :disabled="saving" @click="confirmUpdate">
              <span v-if="saving" class="spinner" />
              <span v-else>CONFIRM</span>
            </button>
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
const saving = ref(false)

function close() { emit('close') }

function handleLogout() {
  authStore.logout()
  router.push('/login')
}

function startEdit() {
  newUsername.value = authStore.user?.username || ''
  editing.value = true
}

function cancelEdit() {
  editing.value = false
  editError.value = ''
}

async function confirmUpdate() {
  if (!newUsername.value.trim()) {
    editError.value = 'Username cannot be empty.'
    return
  }
  saving.value = true
  try {
    await authStore.updateProfile({ username: newUsername.value })
    editing.value = false
  } catch (e) {
    editError.value = e.message || 'Update failed.'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped src="../styles/UserProfileModal.css"></style>
