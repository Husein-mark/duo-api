# Project Duo: To-Do List Application

Project kolaborasi duo SMK PESAT Rekayasa Perangkat Lunak:
- **Backend (BE)**: Mengerjakan RESTful API, otentikasi Sanctum, otorisasi tugas per pengguna, pengujian otomatis, dan dokumentasi Swagger (OpenAPI).
- **Frontend (FE)**: Mengerjakan antarmuka Blade, interaktivitas JavaScript Fetch, dan integrasi API dari data Mock ke API Nyata.

---

## 🚀 Panduan Instalasi Backend

1. **Clone repository dan masuk ke direktori proyek:**
   ```bash
   git clone <url-repository>
   cd duo-api
   ```

2. **Checkout ke branch backend:**
   ```bash
   git checkout feat/backend
   ```

3. **Install dependensi Composer:**
   ```bash
   composer install
   ```

4. **Konfigurasi Environment:**
   Salin file `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   # Di Windows PowerShell:
   # Copy-Item .env.example .env
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

7. **Generate Dokumentasi Swagger (OpenAPI):**
   ```bash
   php artisan l5-swagger:generate
   ```

8. **Jalankan Development Server:**
   ```bash
   php artisan serve
   ```
   - Base API URL: `http://localhost:8000/api`
   - Akses Swagger UI: `http://localhost:8000/api/documentation`
   - File JSON OpenAPI: `docs/openapi.json`

9. **Jalankan Pengujian Otomatis (Automated Tests):**
   ```bash
   php artisan test
   ```

---

## 📋 Ringkasan Endpoint API

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/register` | — | Registrasi pengguna baru |
| POST | `/api/login` | — | Login & mendapatkan Bearer Token |
| GET | `/api/profile` | 🔒 | Mengambil profil user yang sedang login |
| POST | `/api/logout` | 🔒 | Logout & mencabut token akses saat ini |
| GET | `/api/tasks` | 🔒 | Mengambil semua tugas milik user (opsional: `?status=todo` / `done`) |
| POST | `/api/tasks` | 🔒 | Membuat tugas baru (`title`, `deadline`, `status`) |
| GET | `/api/tasks/{id}` | 🔒 | Mengambil detail tugas (hanya milik user yang login) |
| PUT | `/api/tasks/{id}` | 🔒 | Memperbarui seluruh informasi tugas |
| PATCH | `/api/tasks/{id}/status` | 🔒 | Memperbarui status tugas (`todo` / `done`) |
| DELETE | `/api/tasks/{id}` | 🔒 | Menghapus tugas |

Detail lengkap mengenai skema request, response JSON, dan kode status HTTP dapat dilihat pada [docs/kontrak-api.md](docs/kontrak-api.md) atau Swagger UI di `/api/documentation`.

---

## 💡 Refleksi Integrasi & Tanya Jawab (Tugas 04)

### 1. Apa perbedaan mock dan API nyata?
- **Mock API**:
  - Mengembalikan data tiruan (dummy/statis) tanpa terhubung ke basis data asli.
  - Memungkinkan pengembang Frontend (FE) mulai merancang UI, alur navigasi, penanganan status loading, data kosong, dan penanganan error tanpa perlu menunggu Backend selesai.
  - Perubahan data (tambah/edit/hapus) di mock biasanya tidak persisten (hilang saat refresh) atau hanya tersimpan sementara di memori/local storage.
- **API Nyata (Real API)**:
  - Berjalan di server sesungguhnya dengan logika bisnis riil, validasi input di server, enkripsi password (hash), otentikasi token (Sanctum), dan penyimpanan persisten di database (SQL/SQLite).
  - Mengimplementasikan aturan otorisasi ketat (misal: pengguna dilarang melihat atau mengubah tugas pengguna lain / HTTP 403 Forbidden).
  - Menghasilkan respon dinamis sesuai dengan kondisi data dan token yang valid di setiap siklus HTTP request.

### 2. Perbedaan kontrak apa yang kalian temukan, dan bagaimana menyelesaikannya?
- **Temuan yang sering terjadi**:
  - Format respon error validasi (Laravel secara default me-return key pesan tertentu, sedangkan FE membutuhkan key `errors` dan `message` yang terstandarisasi).
  - Format tanggal deadline (`YYYY-MM-DD` vs format ISO 8601 dengan timestamp).
  - Otorisasi tugas: ketika user mencoba mengakses ID tugas milik orang lain, API harus mengembalikan status `403 Forbidden` dengan pesan `"Anda tidak berhak mengakses tugas ini"`.
- **Cara Menyelesaikannya**:
  - Menggunakan **API Resource (`TaskResource`)** di Laravel untuk memformat output JSON secara konsisten sesuai kontrak: `id`, `title`, `deadline` (`YYYY-MM-DD`), dan `status`.
  - Mengonfigurasi **Exception Handler Global** di `bootstrap/app.php` untuk merender respon JSON `401`, `403`, `404`, dan `422` yang tepat persis dengan format kontrak.
  - Setiap kali ada penyesuaian endpoint atau aturan field, perubahan dicatat pada bagian *Riwayat Perubahan* di [docs/kontrak-api.md](docs/kontrak-api.md) dan dikomunikasikan secara transparan antara Backend dan Frontend.
