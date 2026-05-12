<template>
  <div class="search-bar">
    <input
      ref="inputRef"
      v-model="query"
      class="search-input"
      type="text"
      placeholder="Search tasks..."
      @input="taskStore.setSearch(query)"
      @keyup.escape="close"
    />
    <button class="search-icon-btn" @click="close">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTaskStore } from '../store/index.js'

const emit = defineEmits(['close'])
const taskStore = useTaskStore()
const query = ref(taskStore.searchQuery)
const inputRef = ref(null)

onMounted(() => inputRef.value?.focus())

function close() {
  taskStore.setSearch('')
  query.value = ''
  emit('close')
}
</script>

<style scoped>
.search-bar {
  display: flex;
  align-items: center;
  background: var(--bg-secondary);
  border-radius: var(--radius-pill);
  padding: 0 14px;
  gap: 8px;
  border: 1px solid var(--border-color);
  transition: border-color 0.2s;
}

.search-bar:focus-within {
  border-color: var(--accent-blue);
}

.search-input {
  flex: 1;
  background: none;
  border: none;
  outline: none;
  color: var(--text-primary);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.95rem;
  padding: 10px 0;
}

.search-input::placeholder {
  color: var(--text-muted);
}

.search-icon-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0;
  transition: color 0.2s;
}

.search-icon-btn:hover {
  color: var(--text-primary);
}
</style>