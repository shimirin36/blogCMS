<template>
  <div class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
    <ThemeToggle />

    <div class="mx-auto max-w-4xl px-4 py-8 sm:py-12">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500 grid place-items-center text-white shadow-lg">✦</div>
        <div>
          <h1 class="m-0 text-xl sm:text-2xl font-bold">Admin Console</h1>
          <p class="m-0 text-slate-500 dark:text-slate-400 text-sm">安全にサインインしてください</p>
        </div>
      </div>

      <form @submit.prevent="onSubmit" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow p-5 sm:p-6" aria-labelledby="loginTitle">
        <p v-if="justRegistered" class="mb-3 text-sm text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg px-3 py-2">
          登録が完了しました。ログインしてください。
        </p>
        <h2 id="loginTitle" class="text-lg sm:text-xl font-bold mb-3">管理者ログイン</h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">メールアドレス</label>
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2">
              <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/></svg>
              <input v-model="form.email" :disabled="(locked && remaining > 0) || suspended" type="email" required placeholder="you@example.com" class="flex-1 bg-transparent outline-none text-sm placeholder:text-slate-400 disabled:opacity-60" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">パスワード</label>
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2">
              <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 8h2v-3h-2v3Zm3-8H10V6a2 2 0 1 1 4 0v2Z"/></svg>
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" :disabled="(locked && remaining > 0) || suspended" minlength="6" required placeholder="••••••••" class="flex-1 bg-transparent outline-none text-sm placeholder:text-slate-400 disabled:opacity-60" />
              <button type="button" class="text-indigo-500 text-xs font-semibold disabled:opacity-60" @click="showPassword = !showPassword" :aria-pressed="showPassword" :disabled="(locked && remaining > 0) || suspended">
                {{ showPassword ? '隠す' : '表示' }}
              </button>
            </div>
          </div>

          <div v-if="twoFactorRequired">
            <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">2FAコード（6桁）</label>
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2">
              <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 5h18v14H3zM5 7h14v10H5z"/></svg>
              <input v-model="form.code" :disabled="(locked && remaining > 0) || suspended" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="123456" class="flex-1 bg-transparent outline-none text-sm placeholder:text-slate-400 disabled:opacity-60" />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">認証アプリに表示された6桁のコードを入力してください。</p>
          </div>
        </div>

        <button :disabled="loading || (locked && remaining > 0) || suspended" class="w-full mt-4 bg-gradient-to-r from-indigo-500 to-cyan-500 text-white font-bold py-2.5 rounded-lg shadow disabled:opacity-70">
          <span v-if="loading" class="inline-block w-4 h-4 border-2 border-white/50 border-t-white rounded-full animate-spin align-[-2px] mr-2" aria-hidden="true"></span>
          {{ loading ? '認証中...' : (twoFactorRequired ? '2FAでログイン' : 'ログイン') }}
        </button>

        <p v-if="notFound" class="mt-3 text-sm text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg px-3 py-2" role="alert">
          入力されたメールアドレスは登録されていません。<NuxtLink to="/admin/register" class="underline">新規登録</NuxtLink> を行ってください。
        </p>
        <p v-else-if="suspended" class="mt-3 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2" role="alert">
          アカウントが凍結されています。管理者にお問い合わせください。
        </p>
        <p v-else-if="locked" class="mt-3 text-sm text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg px-3 py-2" role="alert">
          あなたは現在ログイン試行ができません。{{ remaining ? `あと${remainingText}` : '' }}お待ちください。
        </p>
        <p v-else-if="error" class="mt-3 text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2" role="alert">{{ error }}</p>

        <div class="mt-4 flex items-center justify-center gap-2 flex-wrap text-sm">
          <NuxtLink to="/admin/start" class="text-indigo-600 dark:text-indigo-300 underline">初めて利用する方はこちら</NuxtLink>
          <span aria-hidden="true" class="text-slate-400">・</span>
          <NuxtLink to="/admin/register" class="text-indigo-600 dark:text-indigo-300 underline">新規登録</NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, watch, onUnmounted } from 'vue'
import { useAdminAuth } from '~/composable/useAdminAuth'
import ThemeToggle from '~/components/ThemeToggle.vue'

const { loading, error, token, login, locked, lockSeconds, suspended } = useAdminAuth()
const twoFactorRequired = ref(false)
const showPassword = ref(false)
const route = useRoute()
const justRegistered = computed(() => route.query.registered)

const form = reactive<{ email: string; password: string; code?: string }>({
  email: '',
  password: '',
})

// カウントダウン処理
const remaining = ref(0)
let timer: any = null
watch([locked, lockSeconds], ([isLocked, secs]) => {
  if (!isLocked) {
    remaining.value = 0
    if (timer) { clearInterval(timer); timer = null }
    return
  }
  remaining.value = Math.ceil(Number(secs || 0))
  if (timer) { clearInterval(timer); timer = null }
  if (remaining.value > 0) {
    timer = setInterval(() => {
      remaining.value = Math.max(0, remaining.value - 1)
      if (remaining.value <= 0 && timer) {
        clearInterval(timer); timer = null
      }
    }, 1000)
  }
}, { immediate: true })

onUnmounted(() => { if (timer) clearInterval(timer) })

// mm:ss 形式の残り時間表記
const remainingText = computed(() => {
  const total = Number(remaining.value || 0)
  const m = Math.floor(total / 60)
  const s = Math.floor(total % 60)
  const mm = String(m).padStart(2, '0')
  const ss = String(s).padStart(2, '0')
  return `${mm}:${ss}`
})

const onSubmit = async () => {
  try {
    const res = await login({ ...form })
    if ((res as any).two_factor_required) {
      twoFactorRequired.value = true
    } else if ((res as any).token) {
      navigateTo('/admin')
    }
  } catch (e) {
    // error は composable 側でセットされる
  }
}
  
</script>

<style scoped></style>
