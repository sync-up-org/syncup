<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <div class="field-group">
          <div class="title-field">
            <input v-model="form.title" class="title-input" type="text" placeholder="Title" />
            <div class="title-underline" />
          </div>
          <textarea
            v-model="form.description"
            class="desc-input"
            placeholder="Description"
            rows="5"
          />
        </div>

        <p v-if="error" class="form-error">{{ error }}</p>

        <div class="modal-actions">
          <button class="btn-cancel" @click="$emit('close')">CANCEL</button>
          <button class="btn-confirm" :disabled="saving" @click="handleConfirm">
            <span v-if="saving" class="spinner" />
            <span v-else>CONFIRM</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useTaskStore } from '../store/index.js'

const emit = defineEmits(['close'])
const taskStore = useTaskStore()

const form = reactive({ title: '', description: '' })
const error = ref('')
const saving = ref(false)

async function handleConfirm() {
  if (!form.title.trim()) {
    error.value = 'Title is required.'
    return
  }
  saving.value = true
  try {
    await taskStore.addTask({ title: form.title, description: form.description })
    emit('close')
  } catch (e) {
    error.value = e.message || 'Failed to create task.'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped src="../styles/AddTaskModal.css"></style>
