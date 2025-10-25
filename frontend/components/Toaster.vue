<template>
  <div class="fixed right-4 top-4 z-[60] space-y-2 w-[min(92vw,360px)]">
    <transition-group name="toast-fade" tag="div">
      <div v-for="t in toasts" :key="t.id"
           class="rounded-lg border px-4 py-3 shadow flex items-start gap-2"
           :class="typeClass(t.type)">
        <div class="text-base leading-5">{{ t.message }}</div>
        <button class="ml-auto text-slate-500 hover:text-slate-700 dark:text-slate-300" @click="remove(t.id)">×</button>
      </div>
    </transition-group>
  </div>
</template>

<script setup lang="ts">
import { useToast } from '~/composable/useToast'
const { toasts, remove } = useToast()

const typeClass = (type: 'info'|'success'|'warning'|'error') => {
  switch (type) {
    case 'success':
      return 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200'
    case 'warning':
      return 'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200'
    case 'error':
      return 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'
    default:
      return 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'
  }
}
</script>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active { transition: all .2s ease; }
.toast-fade-enter-from { opacity: 0; transform: translateY(-6px); }
.toast-fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>

