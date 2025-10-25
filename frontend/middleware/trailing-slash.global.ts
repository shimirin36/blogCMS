export default defineNuxtRouteMiddleware((to) => {
  if (process.server) return
  const path = to.path
  if (path.length > 1 && path.endsWith('/')) {
    return navigateTo(path.replace(/\/+$/u, ''), { replace: true })
  }
})

