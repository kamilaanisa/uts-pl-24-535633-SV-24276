<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const books = ref([])
const errors = ref({})
const editingId = ref(null)
const emptyForm = () => ({ title: '', author: '', year: '', stock: 0 })
const form = ref(emptyForm())

async function load() {
  const res = await api.get('/books')
  books.value = res.data.data
}

async function save() {
  errors.value = {}
  try {
    if (editingId.value) await api.put(`/books/${editingId.value}`, form.value)
    else await api.post('/books', form.value)
    reset()
    await load()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
    else throw e
  }
}

async function edit(book) {
  const res = await api.get(`/books/${book.id}`) // memakai endpoint "tampil satu"
  const b = res.data.data
  editingId.value = b.id
  form.value = { title: b.title, author: b.author, year: b.year, stock: b.stock }
}

function reset() {
  editingId.value = null
  form.value = emptyForm()
  errors.value = {}
}

async function remove(book) {
  if (!confirm(`Hapus "${book.title}"?`)) return
  await api.delete(`/books/${book.id}`)
  await load()
}

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('token')
    router.push('/login')
  }
}

onMounted(load)
</script>

<template>
  <main style="max-width: 800px; margin: 40px auto">
    <header style="display: flex; justify-content: space-between">
      <h1>Katalog Buku</h1>
      <button @click="logout">Logout</button>
    </header>

    <form @submit.prevent="save" style="margin-bottom: 24px">
      <h3>{{ editingId ? 'Ubah Buku' : 'Tambah Buku' }}</h3>
      <p>
        <input v-model="form.title" placeholder="Judul" />
        <small v-if="errors.title" style="color: red">{{ errors.title[0] }}</small>
      </p>
      <p>
        <input v-model="form.author" placeholder="Penulis" />
        <small v-if="errors.author" style="color: red">{{ errors.author[0] }}</small>
      </p>
      <p>
        <input v-model="form.year" type="number" placeholder="Tahun" />
        <small v-if="errors.year" style="color: red">{{ errors.year[0] }}</small>
      </p>
      <p>
        <input v-model="form.stock" type="number" placeholder="Stok" />
        <small v-if="errors.stock" style="color: red">{{ errors.stock[0] }}</small>
      </p>
      <button type="submit">{{ editingId ? 'Simpan Perubahan' : 'Tambah' }}</button>
      <button v-if="editingId" type="button" @click="reset">Batal</button>
    </form>

    <table border="1" cellpadding="6" style="width: 100%; border-collapse: collapse">
      <thead>
        <tr><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Stok</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <tr v-for="b in books" :key="b.id">
          <td>{{ b.title }}</td>
          <td>{{ b.author }}</td>
          <td>{{ b.year }}</td>
          <td>{{ b.stock }}</td>
          <td>
            <button @click="edit(b)">Ubah</button>
            <button @click="remove(b)">Hapus</button>
          </td>
        </tr>
        <tr v-if="!books.length"><td colspan="5">Belum ada data</td></tr>
      </tbody>
    </table>
  </main>
</template>