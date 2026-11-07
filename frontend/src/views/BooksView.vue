<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const books = ref([])
const errors = ref({})
const editingId = ref(null)
const saving = ref(false)
const notice = ref('')
const emptyForm = () => ({ title: '', author: '', year: '', stock: 0 })
const form = ref(emptyForm())

const totalStock = computed(() => books.value.reduce((sum, b) => sum + Number(b.stock), 0))
const lowStock = computed(() => books.value.filter((b) => b.stock <= 3).length)

function showNotice(text) {
  notice.value = text
  setTimeout(() => (notice.value = ''), 3000)
}

function stockClass(stock) {
  if (stock === 0) return 'badge badge-out'
  if (stock <= 3) return 'badge badge-low'
  return 'badge badge-ok'
}

async function load() {
  const res = await api.get('/books')
  books.value = res.data.data
}

async function save() {
  errors.value = {}
  saving.value = true
  try {
    if (editingId.value) {
      await api.put(`/books/${editingId.value}`, form.value)
      showNotice('Buku berhasil diubah')
    } else {
      await api.post('/books', form.value)
      showNotice('Buku berhasil ditambahkan')
    }
    reset()
    await load()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
    else throw e
  } finally {
    saving.value = false
  }
}

async function edit(book) {
  const res = await api.get(`/books/${book.id}`)
  const b = res.data.data
  editingId.value = b.id
  form.value = { title: b.title, author: b.author, year: b.year, stock: b.stock }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function reset() {
  editingId.value = null
  form.value = emptyForm()
  errors.value = {}
}

async function remove(book) {
  if (!confirm(`Hapus "${book.title}"?`)) return
  await api.delete(`/books/${book.id}`)
  showNotice('Buku berhasil dihapus')
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
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <span class="brand-icon">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
          </svg>
        </span>
        Katalog Buku
      </div>
      <button class="btn btn-soft btn-sm" @click="logout">Keluar</button>
    </div>
  </header>

  <main class="container">
    <p v-if="notice" class="notice">{{ notice }}</p>

    <section class="stats">
      <div class="stat stat-accent">
        <div class="stat-label">Judul buku</div>
        <div class="stat-value">{{ books.length }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">Total stok</div>
        <div class="stat-value">{{ totalStock }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">Stok menipis</div>
        <div class="stat-value">{{ lowStock }}</div>
      </div>
    </section>

    <section class="card">
      <div class="card-title">
        <h2>{{ editingId ? 'Ubah Buku' : 'Tambah Buku' }}</h2>
      </div>
      <form @submit.prevent="save">
        <div class="form-grid">
          <label class="field">
            <span>Judul</span>
            <input v-model="form.title" placeholder="Contoh: Laskar Pelangi" />
            <small v-if="errors.title" class="field-error">{{ errors.title[0] }}</small>
          </label>
          <label class="field">
            <span>Penulis</span>
            <input v-model="form.author" placeholder="Contoh: Andrea Hirata" />
            <small v-if="errors.author" class="field-error">{{ errors.author[0] }}</small>
          </label>
          <label class="field">
            <span>Tahun terbit</span>
            <input v-model="form.year" type="number" placeholder="2005" />
            <small v-if="errors.year" class="field-error">{{ errors.year[0] }}</small>
          </label>
          <label class="field">
            <span>Stok</span>
            <input v-model="form.stock" type="number" placeholder="0" />
            <small v-if="errors.stock" class="field-error">{{ errors.stock[0] }}</small>
          </label>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit" :disabled="saving">
            {{ saving ? 'Menyimpan...' : editingId ? 'Simpan Perubahan' : 'Tambah Buku' }}
          </button>
          <button v-if="editingId" class="btn btn-soft" type="button" @click="reset">Batal</button>
        </div>
      </form>
    </section>

    <section class="card">
      <div class="card-title">
        <h2>Daftar Buku</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Judul</th>
              <th>Penulis</th>
              <th>Tahun</th>
              <th>Stok</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in books" :key="b.id">
              <td class="cell-title">{{ b.title }}</td>
              <td>{{ b.author }}</td>
              <td>{{ b.year }}</td>
              <td><span :class="stockClass(b.stock)">{{ b.stock }}</span></td>
              <td>
                <div class="actions">
                  <button class="btn btn-soft btn-sm" @click="edit(b)">Ubah</button>
                  <button class="btn btn-danger btn-sm" @click="remove(b)">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!books.length" class="empty">Belum ada buku. Tambahkan yang pertama lewat formulir di atas.</p>
      </div>
    </section>
  </main>
</template>