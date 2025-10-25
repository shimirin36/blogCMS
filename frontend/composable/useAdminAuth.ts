import { ref } from 'vue'

export interface AdminRegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  enable_2fa?: boolean
}

export interface AdminRegisterResponse {
  message: string
  two_factor_enabled: boolean
  secret: string | null
  qr_url: string | null
}

export function useAdminAuth() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const result = ref<AdminRegisterResponse | null>(null)
  const token = useState<string | null>('admin_token', () => null)
  const admin = useState<any | null>('admin_profile', () => null)
  const locked = useState<boolean>('admin_locked', () => false)
  const lockSeconds = useState<number>('admin_lock_seconds', () => 0)
  const suspended = useState<boolean>('admin_suspended', () => false)
  const notFound = useState<boolean>('admin_not_found', () => false)

  const apiBase = () => {
    const config = useRuntimeConfig()
    let base = (config.public?.apiBase as string | undefined) ?? 'http://localhost:8080/api'
    // 万一 'http://laravel/api' が設定されていた場合、ブラウザからは到達できないため置き換え
    if (process.client && /http:\/\/laravel(\/|$)/.test(base)) {
      base = 'http://localhost:8080/api'
    }
    return base
  }

  const getToken = () => {
    if (token.value) return token.value
    if (process.client) {
      const t = localStorage.getItem('admin_token')
      if (t) token.value = t
      return t
    }
    return null
  }

  const registerAdmin = async (payload: AdminRegisterPayload) => {
    loading.value = true
    error.value = null
    result.value = null
    try {
      const res = await $fetch<AdminRegisterResponse>(`${apiBase()}/admin/register`, {
        method: 'POST',
        body: payload,
      })
      result.value = res
      return res
    } catch (e: any) {
      error.value = e?.data?.message || e?.message || '登録に失敗しました'
      throw e
    } finally {
      loading.value = false
    }
  }

  type LoginPayload = { email: string; password: string; code?: string }
  type LoginSuccess = { token: string; admin: any }
  type Login2FARequired = { two_factor_required: true; status: string; email: string; message: string }

  const login = async (payload: LoginPayload): Promise<LoginSuccess | Login2FARequired> => {
    loading.value = true
    error.value = null
    try {
      const res = await $fetch<LoginSuccess | Login2FARequired>(`${apiBase()}/admin/login`, {
        method: 'POST',
        body: payload,
      })
      // 2FAが不要でトークンが返った場合
      if ((res as any).token) {
        const r = res as LoginSuccess
        token.value = r.token
        admin.value = r.admin
        if (process.client) localStorage.setItem('admin_token', r.token)
      }
      locked.value = false
      lockSeconds.value = 0
      return res
    } catch (e: any) {
      if (e?.data?.not_found || e?.status === 404) {
        notFound.value = true
        locked.value = false
        suspended.value = false
        error.value = 'このメールアドレスは登録されていません。新規登録してください。'
      } else if (e?.data?.suspended || e?.status === 423) {
        suspended.value = true
        locked.value = false
        notFound.value = false
        lockSeconds.value = 0
        error.value = 'アカウントが凍結されています。管理者にお問い合わせください。'
      } else if (e?.data?.locked || e?.status === 429) {
        locked.value = true
        notFound.value = false
        lockSeconds.value = Number(e?.data?.lockout_seconds ?? 0)
        error.value = '現在ログイン試行ができません。しばらく待ってから再度お試しください。'
      } else {
        error.value = e?.data?.error || e?.data?.message || e?.message || 'ログインに失敗しました'
      }
      throw e
    } finally {
      loading.value = false
    }
  }

  const fetchMe = async () => {
    loading.value = true
    error.value = null
    try {
      const t = getToken()
      const res = await $fetch<any>(`${apiBase()}/admin/me`, {
        method: 'GET',
        headers: t ? { Authorization: `Bearer ${t}` } : {},
      })
      admin.value = res
      return res
    } catch (e: any) {
      error.value = e?.data?.error || e?.data?.message || e?.message || '取得に失敗しました'
      throw e
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    error.value = null
    try {
      const t = getToken()
      await $fetch(`${apiBase()}/admin/logout`, {
        method: 'POST',
        headers: t ? { Authorization: `Bearer ${t}` } : {},
      })
    } catch (_) {
      // ignore
    } finally {
      token.value = null
      admin.value = null
      if (process.client) localStorage.removeItem('admin_token')
      loading.value = false
    }
  }

  return { loading, error, result, token, admin, locked, lockSeconds, suspended, notFound, registerAdmin, login, fetchMe, logout }
}
