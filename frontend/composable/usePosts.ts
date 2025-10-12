import { ref } from 'vue'

export interface Post {
  id: number
  title: string
  content: string
}

export function usePosts() {
  const posts = ref<Post[]>([])

  const fetchPosts = async () => {
    try {
      const response = await $fetch<Post[]>('http://localhost:8080/api/posts')
      posts.value = response
    } catch (error) {
      console.error('API取得エラー:', error)
    }
  }

  return { posts, fetchPosts }
}
