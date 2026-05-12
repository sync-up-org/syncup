<template>
  <div class="search-bar">
    <input
      ref="inputRef"
      v-model="query"
      class="search-input"
      type="text"
      placeholder="Search tasks..."
      @input="onSearch"
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

let debounceTimer

onMounted(() => inputRef.value?.focus())

function onSearch() {
  taskStore.setSearch(query.value)
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    taskStore.fetchTasks({ search: query.value || undefined })
  }, 300)
}

function close() {
  clearTimeout(debounceTimer)
  taskStore.setSearch('')
  query.value = ''
  taskStore.fetchTasks()
  emit('close')
}
</script>

<style scoped src="../styles/SearchBar.css"></style>
