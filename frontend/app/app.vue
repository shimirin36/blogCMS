<!-- frontend/app/app.vue -->
<template>
  <NuxtPage />
  <Toaster />
</template>

<script setup lang="ts">
// Initialize theme early on client
if (process.client) {
  const saved = localStorage.getItem('theme') as 'light' | 'dark' | null
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
  const theme = saved || (prefersDark ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', theme === 'dark')
}
</script>

<script setup lang="ts">
import Toaster from '~/components/Toaster.vue'
</script>

<style>
html, body, #__nuxt { height: 100%; }
body { margin: 0; }
</style>
