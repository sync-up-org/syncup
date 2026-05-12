<template>
  <header class="app-header">
    <h1 class="logo">SyncUp</h1>
    <div class="header-actions">
      <button class="icon-btn" aria-label="User profile" @click="$emit('open-profile')">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <circle cx="12" cy="12" r="10" />
          <circle cx="12" cy="9" r="3.5" />
          <path d="M6.2 20c.6-3.1 3-5 5.8-5s5.2 1.9 5.8 5" />
        </svg>
      </button>
      <button class="icon-btn" aria-label="Search" @click="$emit('open-search')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </button>
      <button class="btn-add-task" @click="$emit('open-add-task')">
        <span class="plus">+</span> Add Task
      </button>
    </div>
  </header>
</template>

<script setup>
defineEmits(['open-profile', 'open-search', 'open-add-task'])
</script>

<style scoped>
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
}

.logo {
  font-family: 'Space Mono', monospace;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.5px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 6px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s, background 0.2s;
}

.icon-btn:hover {
  color: var(--text-primary);
  background: var(--bg-secondary);
}

.btn-add-task {
  background: var(--accent-blue);
  color: #fff;
  border: none;
  border-radius: var(--radius-pill);
  font-family: 'DM Sans', sans-serif;
  font-weight: 600;
  font-size: 0.88rem;
  padding: 8px 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s, transform 0.1s;
}

.btn-add-task:hover {
  background: #4b5ce0;
  transform: translateY(-1px);
}

.plus {
  font-size: 1.1rem;
  line-height: 1;
}
</style>