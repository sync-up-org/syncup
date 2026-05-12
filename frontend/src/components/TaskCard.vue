<template>
  <div class="task-card">
    <p class="task-title">{{ task.title }}</p>
    <p v-if="task.description" class="task-desc">{{ task.description }}</p>
    <div class="task-card-footer">
      <div class="status-dots">
        <button
          v-for="s in statuses"
          :key="s.value"
          class="status-dot"
          :class="{ active: task.status === s.value }"
          :title="s.label"
          :disabled="changing"
          @click="moveTo(s.value)"
        />
      </div>
      <button class="delete-btn" title="Delete task" :disabled="changing" @click="handleDelete">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="3 6 5 6 21 6" />
          <path d="M19 6l-1 14H6L5 6" />
          <path d="M10 11v6M14 11v6" />
          <path d="M9 6V4h6v2" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useTaskStore } from '../store/index.js'

const props = defineProps({
  task: { type: Object, required: true },
})
const emit = defineEmits(['status-changed'])
const taskStore = useTaskStore()
const changing = ref(false)

const statuses = [
  { value: 'pending', label: 'Pending' },
  { value: 'incomplete', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
]

async function moveTo(status) {
  if (status === props.task.status) return
  changing.value = true
  try {
    await taskStore.updateTask(props.task.id, { status })
    emit('status-changed')
  } catch {
    // silently fail, board will refresh on next mount
  } finally {
    changing.value = false
  }
}

async function handleDelete() {
  changing.value = true
  try {
    await taskStore.deleteTask(props.task.id)
    emit('status-changed')
  } catch {
    // silently fail
  } finally {
    changing.value = false
  }
}
</script>

<style scoped src="../styles/TaskCard.css"></style>
