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

<style scoped src="../styles/AppHeader.css"></style>