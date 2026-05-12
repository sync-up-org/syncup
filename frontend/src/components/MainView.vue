<template>
  <div class="main-page">
    <AppHeader
      @open-search="searchOpen = true"
      @open-add-task="addTaskOpen = true"
      @open-profile="profileOpen = true"
    />

    <!-- Search bar (conditionally shown) -->
    <div v-if="searchOpen" class="search-bar-wrap">
      <SearchBar @close="searchOpen = false" />
    </div>

    <!-- Task Board -->
    <TaskBoard />

    <!-- Modals -->
    <AddTaskModal v-if="addTaskOpen" @close="addTaskOpen = false" />
    <UserProfileModal v-if="profileOpen" @close="profileOpen = false" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppHeader from '../components/AppHeader.vue'
import SearchBar from '../components/SearchBar.vue'
import TaskBoard from '../components/TaskBoard.vue'
import AddTaskModal from '../components/AddTaskModal.vue'
import UserProfileModal from '../components/UserProfileModal.vue'

const searchOpen = ref(false)
const addTaskOpen = ref(false)
const profileOpen = ref(false)
</script>

<style scoped>
.main-page {
  min-height: 100vh;
  background: var(--bg-primary);
  display: flex;
  flex-direction: column;
}

.search-bar-wrap {
  padding: 0 24px 8px;
}
</style>