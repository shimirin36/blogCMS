// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  srcDir: './', // ← 明示的に指定（重要）
  devtools: { enabled: true },
  runtimeConfig: {
    public: {
      apiBase: 'http://laravel.test/api'
    }
  }
})
