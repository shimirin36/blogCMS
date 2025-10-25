<template>
  <button
    class="fixed right-4 top-4 z-50 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 shadow px-3 py-2"
    @click="toggle"
    :aria-label="theme === 'dark' ? 'ライトテーマに切り替え' : 'ダークテーマに切り替え'"
  >
    <span v-if="theme === 'dark'">☀️</span>
    <span v-else>🌙</span>
  </button>
  
</template>

<script setup lang="ts">
import { useTheme } from '~/composable/useTheme'
const { theme, toggle } = useTheme()
</script>

<style scoped></style>
