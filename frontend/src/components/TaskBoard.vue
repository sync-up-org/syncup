<template>
  <div class="board">
    <div class="board-header">
      <h2 class="board-header-title">Tasks</h2>
    </div>

    <p v-if="error" class="board-error">{{ error }}</p>

    <div v-if="taskStore.loading" class="loading">Loading tasks...</div>

    <template v-else-if="taskStore.tasks.length === 0">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
            <line x1="12" y1="8" x2="12" y2="16" />
            <line x1="8" y1="12" x2="16" y2="12" />
          </svg>
        </div>
        <p class="empty-state-title">No tasks yet</p>
        <p class="empty-state-desc">Create your first task by clicking the "Add Task" button above.</p>
      </div>
    </template>

    <div v-else class="columns">
      <div
        v-for="col in columns"
        :key="col.value"
        class="column"
        :class="{ 'drag-over': dragOverColumn === col.value }"
        @dragover.prevent="dragOverColumn = col.value"
        @dragenter.prevent="dragOverColumn = col.value"
        @dragleave="onDragLeave(col.value)"
        @drop="onDrop($event, col.value)"
      >
        <h3 class="column-title">
          <span class="col-dot" :class="col.value" />
          {{ col.label }}
          <span class="col-count">{{ grouped(col.value).length }}</span>
        </h3>
        <div class="column-cards">
          <TaskCard
            v-for="task in grouped(col.value)"
            :key="task.id"
            :task="task"
            @status-changed="refresh"
          />
          <p v-if="grouped(col.value).length === 0" class="empty-col">No tasks</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useTaskStore } from '../store/index.js'
import TaskCard from './TaskCard.vue'

const taskStore = useTaskStore()
const error = ref('')
const dragOverColumn = ref(null)

const columns = [
  { value: 'pending', label: 'Pending' },
  { value: 'incomplete', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
]

const grouped = (status) => computed(() =>
  taskStore.tasks.filter((t) => t.status === status)
).value

function refresh() {
  taskStore.fetchTasks()
}

function onDragLeave(colValue) {
  if (dragOverColumn.value === colValue) {
    dragOverColumn.value = null
  }
}

async function onDrop(e, targetStatus) {
  dragOverColumn.value = null
  try {
    const raw = e.dataTransfer.getData('text/plain')
    const { id } = JSON.parse(raw)
    const task = taskStore.tasks.find((t) => t.id === id)
    if (!task || task.status === targetStatus) return

    task.status = targetStatus
    await taskStore.updateTask(id, { status: targetStatus })
  } catch {
    refresh()
  }
}

onMounted(() => {
  taskStore.fetchTasks().catch((e) => {
    error.value = e.message
  })
})
</script>

<style scoped src="../styles/TaskBoard.css"></style>
