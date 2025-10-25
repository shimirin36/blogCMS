import { ref } from 'vue'

export type ToastType = 'info' | 'success' | 'warning' | 'error'

interface ToastItem {
  id: number
  message: string
  type: ToastType
  duration: number
}

export function useToast() {
  const toasts = useState<ToastItem[]>('toasts', () => [])
  let idSeq = 1

  const remove = (id: number) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const show = (message: string, type: ToastType = 'info', duration = 3000) => {
    const id = idSeq++
    const item: ToastItem = { id, message, type, duration }
    toasts.value = [...toasts.value, item]
    if (duration > 0) {
      setTimeout(() => remove(id), duration)
    }
    return id
  }

  const success = (msg: string, duration = 2500) => show(msg, 'success', duration)
  const error = (msg: string, duration = 3500) => show(msg, 'error', duration)
  const warning = (msg: string, duration = 3500) => show(msg, 'warning', duration)

  return { toasts, show, success, error, warning, remove }
}

