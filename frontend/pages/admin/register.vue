<template>
  <div class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
    <ThemeToggle />
    <div class="mx-auto max-w-4xl px-4 py-8 sm:py-12">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500 grid place-items-center text-white shadow-lg">✦</div>
        <div>
          <h1 class="m-0 text-xl sm:text-2xl font-bold">Admin Console</h1>
          <p class="m-0 text-slate-500 dark:text-slate-400 text-sm">管理者ユーザー登録</p>
        </div>
      </div>

      <form @submit.prevent="onSubmit" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow p-5 sm:p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">名前</label>
            <input v-model="form.name" type="text" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 outline-none placeholder:text-slate-400" placeholder="山田 太郎" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">メールアドレス</label>
            <input v-model="form.email" type="email" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 outline-none placeholder:text-slate-400" placeholder="you@example.com" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">パスワード</label>
              <input v-model="form.password" type="password" minlength="6" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 outline-none placeholder:text-slate-400" placeholder="••••••••" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">パスワード（確認）</label>
              <input v-model="form.password_confirmation" type="password" minlength="6" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 outline-none placeholder:text-slate-400" placeholder="••••••••" />
            </div>
          </div>

          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input id="enable2fa" type="checkbox" v-model="form.enable_2fa" class="w-4 h-4" />
            二段階認証を有効化する（推奨）
          </label>
        </div>

        <button :disabled="loading" class="w-full mt-5 bg-gradient-to-r from-indigo-500 to-cyan-500 text-white font-bold py-2.5 rounded-lg shadow disabled:opacity-70">
          {{ loading ? '送信中...' : '登録する' }}
        </button>

        <p v-if="error" class="mt-3 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2" role="alert">{{ error }}</p>

        <div class="mt-4 text-sm">
          <NuxtLink to="/admin/login" class="text-indigo-600 dark:text-indigo-300 underline">すでにアカウントをお持ちの方はこちら</NuxtLink>
        </div>
      </form>

      <div v-if="result" class="mt-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow p-5 sm:p-6">
        <p class="mb-2">{{ result.message }}</p>
        <div v-if="result.two_factor_enabled" class="space-y-2">
          <p class="text-sm text-slate-700 dark:text-slate-300">二段階認証が有効化されました。認証アプリで以下のQRを読み取ってください。</p>
          <img :src="qrImageUrl" alt="2FA QR Code" class="border border-slate-200 dark:border-slate-700 rounded" />
          <p class="text-sm text-slate-700 dark:text-slate-300">認証アプリが使えない場合は、以下のシークレットを手入力してください:</p>
          <code class="block mt-1 p-2 bg-slate-50 dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-700 text-sm">{{ result.secret }}</code>
          <div class="pt-2">
            <button @click="goLogin" class="px-4 py-2 rounded-lg bg-slate-800 text-white dark:bg-slate-700">ログイン画面へ</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import { useAdminAuth } from '~/composable/useAdminAuth'
import ThemeToggle from '~/components/ThemeToggle.vue'
import { useToast } from '~/composable/useToast'

const { loading, error, result, registerAdmin } = useAdminAuth()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  enable_2fa: true,
})

const toast = useToast()

const onSubmit = async () => {
  try {
    const res = await registerAdmin({ ...form })
    if (res && !res.two_factor_enabled) {
      toast.success('登録が完了しました。ログインしてください。')
      navigateTo('/admin/login?registered=1')
    }
  } catch (e: any) {
    if (e?.status === 422 && e?.data?.errors?.email) {
      toast.error('既に登録されているメールアドレスです')
    }
  }
}

const goLogin = () => navigateTo('/admin/login?registered=1')

// Backendは otpauth URL を返します。QR表示には画像生成サービスを利用します。
const qrImageUrl = computed(() => {
  const data = result.value?.qr_url
  if (!data) return ''
  const encoded = encodeURIComponent(data)
  return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encoded}`
})
</script>

<style scoped></style>
