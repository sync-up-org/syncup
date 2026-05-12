<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './store/index.js'

const router = useRouter()
const auth = useAuthStore()

onMounted(async () => {
  if (auth.isAuthenticated) {
    try {
      await auth.fetchProfile()
    } catch {
      auth.logout()
      router.push('/login')
    }
  }
})
</script>

<template>
  <router-view />
</template>

<style>
body {
  margin: 0;
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
  background: #f9f9f9;
}
</style>
