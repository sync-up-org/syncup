<template>
  <div class="board">
    <div class="board-header">
      <p v-if="error" class="board-error">{{ error }}</p>
    </div>

    <div v-if="taskStore.loading" class="loading">Loading tasks...</div>

    <div v-else class="columns">
      <div v-for="col in columns" :key="col.value" class="column">
        <div class="column-title">
          <span class="col-dot" :class="col.value" />
          {{ col.label }}
          <span class="col-count">{{ grouped(col.value).length }}</span>
        </div>
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

onMounted(() => {
  taskStore.fetchTasks().catch((e) => {
    error.value = e.message
  })
})
</script>

<style scoped src="../styles/TaskBoard.css"></style>
