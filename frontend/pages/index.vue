<template>
  <div class="p-6">
    <h1>Nuxt 3 + Laravel + Docker 環境へようこそ！</h1>
    <button @click="fetchPosts">投稿を取得</button>

    <div v-if="posts.length" class="mt-4">
      <h2>Laravel API からのレスポンス:</h2>
      <ul>
        <li v-for="post in posts" :key="post.id">
          <strong>{{ post.title }}</strong><br />
          {{ post.content }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePosts } from '~/composable/usePosts';

const { posts, fetchPosts } = usePosts() // ← 自動インポート
</script>
