<template>
  <div class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
    <ThemeToggle />
    <div class="max-w-5xl mx-auto px-4 py-8 sm:py-12">
      <h1 class="text-2xl sm:text-3xl font-extrabold mb-4">管理ダッシュボード</h1>

      <div v-if="loading">読み込み中...</div>
      <div v-else>
        <div v-if="admin">
          <p class="mb-2">こんにちは、{{ admin.email }} さん</p>
          <pre class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 text-sm overflow-auto">{{ admin }}</pre>

          <div class="mt-4">
            <button @click="onLogout" class="px-4 py-2 rounded-lg bg-slate-800 text-white dark:bg-slate-700">ログアウト</button>
          </div>
        </div>
        <div v-else class="text-slate-600 dark:text-slate-400">ユーザー情報を取得できませんでした。</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAdminAuth } from '~/composable/useAdminAuth'
import ThemeToggle from '~/components/ThemeToggle.vue'

definePageMeta({ middleware: 'admin-auth' })

const { loading, admin, fetchMe, logout } = useAdminAuth()

onMounted(async () => {
  try {
    await fetchMe()
  } catch (_) {
    // ミドルウェアで未ログインはログインへ誘導
  }
})

const onLogout = async () => {
  await logout()
  navigateTo('/admin/login')
}
</script>

<style scoped></style>
