// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  srcDir: './', // ← 明示的に指定（重要）
  devtools: { enabled: true },
  runtimeConfig: {
    public: {
      // ブラウザから到達可能なAPIのベースURL
      // 環境変数 NUXT_PUBLIC_API_BASE があればそれを使用
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080/api'
    }
  },
  modules: ['@nuxtjs/tailwindcss'],
  css: ['~/assets/css/tailwind.css']
})
