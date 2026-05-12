<template>
  <div class="task-card" :class="`priority-${task.priority}`">
    <div class="task-card-header">
      <span class="priority-badge" :class="`badge-${task.priority}`">
        {{ task.priority.charAt(0).toUpperCase() + task.priority.slice(1) }}
      </span>
    </div>
    <p class="task-title">{{ task.title }}</p>
    <p class="task-desc">{{ task.description }}</p>
    <div class="task-card-footer">
      <div class="status-dots">
        <button
          v-for="s in statuses"
          :key="s.value"
          class="status-dot"
          :class="{ active: task.status === s.value }"
          :title="s.label"
          @click="taskStore.moveTask(task.id, s.value)"
        />
      </div>
      <button class="delete-btn" title="Delete task" @click="taskStore.deleteTask(task.id)">
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
import { useTaskStore } from '../store/index.js'

defineProps({
  task: { type: Object, required: true },
})

const taskStore = useTaskStore()

const statuses = [
  { value: 'todo', label: 'To-Do' },
  { value: 'in-progress', label: 'In Progress' },
  { value: 'done', label: 'Done' },
]
</script>

<style scoped>
.task-card {
  background: var(--bg-card);
  border-radius: var(--radius-md);
  padding: 12px 12px 10px;
  border-left: 3px solid transparent;
  transition: transform 0.15s, box-shadow 0.15s;
  cursor: default;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.priority-low  { border-left-color: var(--priority-low); }
.priority-medium { border-left-color: var(--priority-medium); }
.priority-high { border-left-color: var(--priority-high); }

.task-card-header {
  margin-bottom: 6px;
}

.priority-badge {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: var(--radius-pill);
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.badge-low    { background: var(--priority-low);    color: #fff; }
.badge-medium { background: var(--priority-medium); color: #fff; }
.badge-high   { background: var(--priority-high);   color: #fff; }

.task-title {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text-primary);
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-desc {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-bottom: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.task-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.status-dots {
  display: flex;
  gap: 6px;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--border-color);
  border: 1.5px solid var(--text-muted);
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
  padding: 0;
}

.status-dot.active {
  background: var(--accent-blue);
  border-color: var(--accent-blue);
}

.status-dot:hover {
  border-color: var(--accent-blue);
}

.delete-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
  border-radius: var(--radius-sm);
  transition: color 0.2s, background 0.2s;
}

.delete-btn:hover {
  color: var(--accent-red);
  background: rgba(229, 57, 53, 0.12);
}
</style>