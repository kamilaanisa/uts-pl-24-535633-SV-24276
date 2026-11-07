<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function login() {
  error.value = ''
  loading.value = true
  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('token', res.data.token)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login gagal, coba lagi'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-wrap">
    <section class="login-card">
      <div class="logo">
        <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
        </svg>
      </div>
      <h1>Katalog Buku</h1>
      <p class="subtitle">Masuk untuk mengelola koleksi bukumu</p>

      <form @submit.prevent="login">
        <label class="field">
          <span>Email</span>
          <input v-model="email" type="email" placeholder="nama@email.com" autocomplete="username" required />
        </label>
        <label class="field">
          <span>Password</span>
          <input v-model="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required />
        </label>
        <p v-if="error" class="form-error">{{ error }}</p>
        <button class="btn btn-primary btn-block" type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>
    </section>
  </main>
</template>