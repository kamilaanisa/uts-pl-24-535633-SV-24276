# Katalog Buku: UTS Perancangan Perangkat Lunak

![CI-CD](https://github.com/kamilaanisa/uts-pl-24-535633-SV-24276/actions/workflows/ci.yml/badge.svg)

Aplikasi web sederhana untuk mengelola katalog buku. Pengguna login, lalu bisa melihat, menambah, mengubah, dan menghapus data buku. Proyek ini dibuat sebagai tugas UTS dan menjadi latihan membangun satu sistem utuh: REST API, antarmuka web, pengujian otomatis, pipeline CI/CD, dan kontainerisasi.

| | |
|---|---|
| **Nama** | Kamila Anisa |
| **NIM** | 24/535633/SV/24276 |
| **Repository** | https://github.com/kamilaanisa/uts-pl-24-535633-SV-24276 |

## Ringkasan Sistem

Sistem terdiri dari tiga bagian yang berjalan bersama lewat Docker Compose:

- **Backend**: REST API dengan Laravel dan Laravel Sanctum, dijalankan di atas FrankenPHP.
- **Frontend**: Single Page Application dengan Vue.js 3, disajikan oleh Nginx.
- **Database**: MySQL 8.4 dengan data tersimpan di named volume.

### Alur Data

```
Browser ──► Nginx (frontend)  ──/api/*──►  FrankenPHP + Laravel (backend)  ──►  MySQL (db)
            sajikan file Vue              proxy lewat nama service            simpan data
```

1. Browser membuka halaman Vue yang disajikan Nginx.
2. Vue mengirim permintaan lewat Axios ke `/api/...`, dengan token Sanctum di header `Authorization`.
3. Nginx meneruskan permintaan `/api/` ke service `backend` melalui jaringan Docker Compose.
4. Laravel memvalidasi token dan data, lalu membaca atau menulis ke service `db`.
5. Hasilnya dikembalikan sebagai JSON dan ditampilkan oleh Vue.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel, Laravel Sanctum |
| Server aplikasi | FrankenPHP (Caddy) |
| Frontend | Vue.js 3, Vue Router, Axios, Vite |
| Web server frontend | Nginx |
| Database | MySQL 8.4 (SQLite untuk pengembangan lokal dan pengujian) |
| Kontainer | Docker, Docker Compose |
| CI/CD | GitHub Actions, GitHub Container Registry (ghcr.io) |
| Uji API | Postman, PHPUnit (Laravel Feature Test) |

## Struktur Repository

```
.
├── backend/                  # Laravel REST API
│   ├── app/
│   ├── routes/api.php
│   ├── tests/Feature/        # Feature test
│   ├── postman/              # Postman Collection
│   ├── Caddyfile             # Konfigurasi FrankenPHP
│   ├── docker-entrypoint.sh
│   └── Dockerfile
├── frontend/                 # Vue.js
│   ├── src/
│   ├── nginx.conf
│   └── Dockerfile
├── .github/workflows/ci.yml  # Pipeline CI/CD
├── docker-compose.yml
├── .env.example
└── README.md
```

## Fitur

- Login dan logout dengan token (Laravel Sanctum). Semua endpoint selain login hanya bisa diakses dengan token.
- CRUD buku: tampil semua, tampil satu, tambah, ubah, hapus.
- Validasi data dengan balasan JSON. Kode status: `201` saat data dibuat, `422` saat validasi gagal, `401` saat token tidak ada atau tidak valid.
- Alamat API di frontend diatur lewat variabel lingkungan (`VITE_API_URL`), tidak ditulis di komponen.

## Endpoint API

Base URL lokal: `http://localhost:8000/api`

| Method | Endpoint | Keterangan | Perlu token |
|---|---|---|---|
| POST | `/login` | Login, mengembalikan token | Tidak |
| POST | `/logout` | Logout, menghapus token aktif | Ya |
| GET | `/books` | Daftar semua buku | Ya |
| GET | `/books/{id}` | Detail satu buku | Ya |
| POST | `/books` | Tambah buku (`201`) | Ya |
| PUT | `/books/{id}` | Ubah buku | Ya |
| DELETE | `/books/{id}` | Hapus buku | Ya |

Data buku: `title`, `author`, `year`, `stock`.

Semua permintaan memakai header `Accept: application/json`, dan token dikirim sebagai `Authorization: Bearer <token>`.

Postman Collection tersedia di [`backend/postman/uts-pl.postman_collection.json`](backend/postman/uts-pl.postman_collection.json). Import ke Postman, jalankan request **Login** lebih dulu (token tersimpan otomatis), lalu request lainnya.

## Menjalankan dengan Docker Compose (disarankan)

Prasyarat: Docker Desktop (atau Docker Engine + Compose v2) dan Git.

1. Clone repository:

   ```bash
   git clone https://github.com/kamilaanisa/uts-pl-24-535633-SV-24276.git
   cd uts-pl-24-535633-SV-24276
   ```

2. Siapkan berkas environment:

   ```bash
   cp .env.example .env
   ```

   Edit `.env`, lalu isi semua nilainya. `APP_KEY` dibuat dengan:

   ```bash
   cd backend && composer install && php artisan key:generate --show
   ```

   Salin hasilnya (diawali `base64:`) ke `APP_KEY` di `.env`.

3. Jalankan seluruh sistem:

   ```bash
   docker compose up -d --build
   ```

4. Buka aplikasi:

   | Layanan | Alamat |
   |---|---|
   | Aplikasi web (Vue) | http://localhost:8080 |
   | API (Laravel) | http://localhost:8000/api |

   Login dengan email `admin@example.com` dan password sesuai `ADMIN_PASSWORD` di `.env`.

5. Perintah berguna:

   ```bash
   docker compose ps               # status container
   docker compose logs -f backend  # log backend (FrankenPHP)
   docker compose down             # hentikan, data tetap tersimpan
   docker compose down -v          # hentikan DAN hapus data database
   ```

Data database disimpan di named volume `db_data`, sehingga tetap ada setelah `docker compose down` lalu `docker compose up -d`.

### Berkas `.env`

Berkas `.env` berisi nilai rahasia dan **tidak ikut di-commit**. Contoh isinya ada di `.env.example`:

| Variabel | Fungsi |
|---|---|
| `APP_KEY` | Kunci enkripsi Laravel |
| `DB_DATABASE`, `DB_USERNAME` | Nama database dan pengguna MySQL |
| `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Password MySQL |
| `ADMIN_PASSWORD` | Password akun admin awal (`admin@example.com`) |

## Menjalankan Tanpa Docker (pengembangan lokal)

Prasyarat: PHP 8.3+, Composer 2, Node.js 22+ dan npm.

**Backend**

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# pastikan DB_CONNECTION=sqlite di .env, tambahkan ADMIN_PASSWORD=...
php artisan migrate --seed
php artisan serve
```

**Frontend** (terminal lain)

```bash
cd frontend
npm install
npm run dev
```

Frontend berjalan di http://localhost:5173 dan memanggil API di alamat pada `frontend/.env.development` (`VITE_API_URL`).

## Pengujian

```bash
cd backend
php artisan test
```

Feature test mencakup login, logout, akses tanpa token (`401`), tambah buku (`201`), validasi gagal (`422`), dan hapus buku.

## CI/CD

Pipeline ada di [`.github/workflows/ci.yml`](.github/workflows/ci.yml) dan berjalan lewat GitHub Actions.

| Job | Fungsi | Kapan berjalan |
|---|---|---|
| `backend-test` | `composer install` lalu `php artisan test` | Semua push dan Pull Request |
| `frontend-build` | `npm ci` lalu `npm run build` | Semua push dan Pull Request |
| `deploy` | Smoke test dengan Docker Compose, lalu build dan push image ke GHCR | **Hanya** push ke `main` |

- Image diunggah ke GitHub Container Registry: `uts-pl-backend` dan `uts-pl-frontend`, dengan tag berupa nomor commit (bukan `latest`).
- Nilai rahasia (`APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `ADMIN_PASSWORD`) disimpan di GitHub Secrets, tidak ada di dalam repository.

## Alur Kerja Git

| Branch | Fungsi |
|---|---|
| `main` | Rilis stabil, memicu job deploy |
| `dev` | Integrasi pengembangan |
| `feature/*` | Satu fitur per branch |

Perubahan masuk ke `dev` dan `main` hanya lewat Pull Request. Branch protection mewajibkan Pull Request dan status check `backend-test` dan `frontend-build` lulus sebelum merge.

## Catatan Docker

- **Backend**: image dasar `dunglas/frankenphp` dengan tag versi yang jelas. `composer.json` dan `composer.lock` dipasang sebelum kode aplikasi disalin supaya cache lapisan Docker bekerja. Request API dilayani FrankenPHP (terlihat di `docker compose logs backend`).
- **Frontend**: multi-stage build. Tahap pertama membangun aplikasi dengan Node.js, tahap kedua menyajikan hasilnya dengan Nginx, sehingga image akhir tidak berisi `node_modules`.
- Tidak ada password, key, atau token yang ditulis di Dockerfile. Semuanya masuk lewat environment saat container berjalan.