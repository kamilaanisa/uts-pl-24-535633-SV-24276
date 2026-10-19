<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

async function login() {
  error.value = ''
  try {
    const res = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('token', res.data.token)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login gagal'
  }
}
</script>

<template>
  <main style="max-width: 360px; margin: 80px auto">
    <h1>Login</h1>
    <form @submit.prevent="login">
      <p><input v-model="email" type="email" placeholder="Email" required style="width: 100%" /></p>
      <p><input v-model="password" type="password" placeholder="Password" required style="width: 100%" /></p>
      <p v-if="error" style="color: red">{{ error }}</p>
      <button type="submit">Masuk</button>
    </form>
  </main>
</template>