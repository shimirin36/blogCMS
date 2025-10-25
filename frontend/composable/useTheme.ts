import { ref } from 'vue'

export type Theme = 'light' | 'dark'

export function useTheme() {
  const theme = useState<Theme>('theme', () => {
    if (process.client) {
      const saved = (localStorage.getItem('theme') as Theme | null)
      if (saved) return saved
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
      return prefersDark ? 'dark' : 'light'
    }
    return 'light'
  })

  const apply = (t: Theme) => {
    if (process.client) {
      document.documentElement.classList.toggle('dark', t === 'dark')
      localStorage.setItem('theme', t)
    }
    theme.value = t
  }

  const toggle = () => apply(theme.value === 'dark' ? 'light' : 'dark')

  return { theme, apply, toggle }
}

