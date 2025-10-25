export default defineNuxtRouteMiddleware((to, from) => {
  if (process.server) return
  const token = localStorage.getItem('admin_token')
  if (!token) {
    if (to.path !== '/admin/login') {
      return navigateTo('/admin/login')
    }
  }
})

