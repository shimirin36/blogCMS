<template>
  <div class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
    <ThemeToggle />
    <div class="mx-auto max-w-5xl px-4 py-8 sm:py-12">
      <h1 class="text-2xl sm:text-3xl font-extrabold mb-4">はじめての管理者セットアップ</h1>
      <ol class="list-decimal pl-5 space-y-4 text-slate-700 dark:text-slate-300">
        <li>
          <strong class="block mb-1">管理者アカウントを作成</strong>
          <p><NuxtLink to="/admin/register" class="text-indigo-600 dark:text-indigo-300 underline">新規登録ページ</NuxtLink> で名前・メール・パスワードを設定します。</p>
        </li>
        <li>
          <strong class="block mb-1">二段階認証(推奨)を有効化</strong>
          <p>登録時に表示されるQRを認証アプリ（Google Authenticator 等）で読み取ります。</p>
        </li>
        <li>
          <strong class="block mb-1">ログインする</strong>
          <p><NuxtLink to="/admin/login" class="text-indigo-600 dark:text-indigo-300 underline">ログインページ</NuxtLink> からサインイン。2FA有効時は6桁コードを入力します。</p>
        </li>
      </ol>

      <div class="mt-6 flex gap-3 flex-wrap">
        <NuxtLink class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-cyan-500 text-white shadow" to="/admin/register">新規登録へ</NuxtLink>
        <NuxtLink class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800" to="/admin/login">ログインへ</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import ThemeToggle from '~/components/ThemeToggle.vue'
</script>

<style scoped></style>
