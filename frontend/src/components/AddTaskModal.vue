<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <!-- Priority badge (top right) -->
        <div class="modal-header">
          <div class="priority-selector">
            <button
              v-for="p in priorities"
              :key="p.value"
              class="priority-opt"
              :class="[`opt-${p.value}`, { selected: form.priority === p.value }]"
              @click="form.priority = p.value"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <div class="field-group">
          <div class="title-field">
            <input
              v-model="form.title"
              class="title-input"
              type="text"
              placeholder="Title"
            />
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
          <button class="btn-confirm" @click="handleConfirm">CONFIRM</button>
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

const priorities = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
]

const form = reactive({ title: '', description: '', priority: 'low' })
const error = ref('')

function handleConfirm() {
  if (!form.title.trim()) {
    error.value = 'Title is required.'
    return
  }
  taskStore.addTask({ ...form })
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn { from { opacity: 0; } }

.modal-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: 20px 20px 24px;
  width: 320px;
  box-shadow: var(--shadow);
  animation: slideUp 0.2s ease;
}

@keyframes slideUp { from { transform: translateY(16px); opacity: 0; } }

.modal-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}

.priority-selector {
  display: flex;
  gap: 6px;
}

.priority-opt {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  padding: 3px 10px;
  border-radius: var(--radius-pill);
  border: 1.5px solid transparent;
  cursor: pointer;
  background: var(--bg-card);
  color: var(--text-muted);
  transition: all 0.15s;
}

.priority-opt.opt-low.selected    { background: var(--priority-low);    color: #fff; border-color: var(--priority-low); }
.priority-opt.opt-medium.selected { background: var(--priority-medium); color: #fff; border-color: var(--priority-medium); }
.priority-opt.opt-high.selected   { background: var(--priority-high);   color: #fff; border-color: var(--priority-high); }
.priority-opt:not(.selected):hover { border-color: var(--text-muted); color: var(--text-primary); }

.field-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.title-field {
  position: relative;
}

.title-input {
  width: 100%;
  background: none;
  border: none;
  border-bottom: 1.5px solid var(--border-color);
  color: var(--text-primary);
  font-family: 'Space Mono', monospace;
  font-size: 1.4rem;
  font-weight: 700;
  padding: 4px 0 8px;
  outline: none;
  transition: border-color 0.2s;
}

.title-input:focus {
  border-bottom-color: var(--accent-blue);
}

.title-input::placeholder {
  color: var(--text-muted);
}

.desc-input {
  width: 100%;
  background: var(--bg-card);
  border: none;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  padding: 12px;
  outline: none;
  resize: none;
  transition: box-shadow 0.2s;
}

.desc-input:focus {
  box-shadow: 0 0 0 2px var(--accent-blue);
}

.desc-input::placeholder {
  color: var(--text-muted);
}

.form-error {
  color: var(--accent-red);
  font-size: 0.8rem;
  margin-top: 4px;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.btn-cancel,
.btn-confirm {
  border: none;
  border-radius: var(--radius-pill);
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: 0.82rem;
  letter-spacing: 1px;
  padding: 10px 28px;
  cursor: pointer;
  transition: opacity 0.15s, transform 0.1s;
}

.btn-cancel {
  background: var(--accent-red);
  color: #fff;
}

.btn-confirm {
  background: var(--accent-green);
  color: #fff;
}

.btn-cancel:hover,
.btn-confirm:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}
</style>