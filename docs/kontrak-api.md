# Kontrak API — To-Do List

Kontrak ini disepakati oleh **Backend (BE)** dan **Frontend (FE)** sebelum mulai bekerja paralel.
Perubahan apa pun pada kontrak **wajib disepakati, dicatat di bagian [Riwayat Perubahan](#riwayat-perubahan), dan dibagikan ke pasangan**.

| Item | Nilai |
|------|-------|
| Versi kontrak | 1.0 |AA
| Framework | Laravel + Laravel Sanctum |
| Base URL (lokal) | `http://localhost:8000/api` |
| Format data | JSON |
| Autentikasi | Bearer Token (Sanctum) |

---

## 1. Ketentuan Umum

### Header

Semua request:

```
Accept: application/json
Content-Type: application/json
```

Endpoint terlindungi (🔒) wajib menambahkan:

```
Authorization: Bearer {token}
```

### Format Respons

**Sukses**

```json
{
  "success": true,
  "message": "Pesan singkat",
  "data": {}
}
```

**Gagal**

```json
{
  "success": false,
  "message": "Pesan error",
  "errors": {
    "field": ["Pesan validasi"]
  }
}
```

> `errors` hanya ada pada error validasi (422).

### Kode Status HTTP

| Kode | Arti | Dipakai ketika |
|------|------|----------------|
| 200 | OK | Berhasil (GET, PUT, PATCH, DELETE, login, logout) |
| 201 | Created | Berhasil membuat data (register, tambah tugas) |
| 401 | Unauthorized | Token tidak ada / tidak valid / login salah |
| 403 | Forbidden | Mengakses tugas milik pengguna lain |
| 404 | Not Found | Data tidak ditemukan |
| 422 | Unprocessable Entity | Validasi input gagal |

---

## 2. Model Data

### User

| Field | Tipe | Keterangan |
|-------|------|------------|
| `id` | integer | ID pengguna |
| `name` | string | Nama pengguna |
| `email` | string | Email (unik) |

### Task

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `id` | integer | — | ID tugas (otomatis) |
| `title` | string | Ya | Judul tugas, maks. 255 karakter |
| `deadline` | string (`YYYY-MM-DD`) | Ya | Tanggal tenggat |
| `status` | string | Tidak | `todo` atau `done`. Default: `todo` |

Contoh objek Task:

```json
{
  "id": 1,
  "title": "Mengerjakan tugas PKL",
  "deadline": "2026-11-20",
  "status": "todo"
}
```

---

## 3. Endpoint Autentikasi

### 3.1 Register

`POST /api/register`

**Request body**

| Field | Tipe | Aturan |
|-------|------|--------|
| `name` | string | wajib, maks. 255 |
| `email` | string | wajib, format email, unik |
| `password` | string | wajib, min. 8 karakter |
| `password_confirmation` | string | wajib, sama dengan `password` |

```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "rahasia123",
  "password_confirmation": "rahasia123"
}
```

**Respons 201**

```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
}
```

**Respons 422** — email sudah dipakai

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email sudah terdaftar."]
  }
}
```

---

### 3.2 Login

`POST /api/login`

**Request body**

| Field | Tipe | Aturan |
|-------|------|--------|
| `email` | string | wajib, format email |
| `password` | string | wajib |

```json
{
  "email": "budi@example.com",
  "password": "rahasia123"
}
```

**Respons 200**

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz"
  }
}
```

**Respons 401** — email/password salah

```json
{
  "success": false,
  "message": "Email atau password salah"
}
```

**Respons 422** — field kosong / format salah

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email wajib diisi."]
  }
}
```

---

### 3.3 Profil 🔒

`GET /api/profile`

**Respons 200**

```json
{
  "success": true,
  "message": "Profil berhasil diambil",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@example.com"
  }
}
```

**Respons 401** — tanpa token / token tidak valid

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

---

### 3.4 Logout 🔒

`POST /api/logout`

Menghapus token yang sedang dipakai.

**Respons 200**

```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

**Respons 401** — tanpa token / token tidak valid (format sama seperti 3.3)

---

## 4. Endpoint Tugas (CRUD) 🔒

Semua endpoint di bagian ini **wajib Bearer token**. Setiap pengguna **hanya boleh mengakses tugas miliknya sendiri**.

### 4.1 Daftar Tugas

`GET /api/tasks`

**Query opsional**

| Parameter | Nilai | Keterangan |
|-----------|-------|------------|
| `status` | `todo` / `done` | Filter berdasarkan status |

**Respons 200**

```json
{
  "success": true,
  "message": "Daftar tugas berhasil diambil",
  "data": [
    {
      "id": 1,
      "title": "Mengerjakan tugas PKL",
      "deadline": "2026-11-20",
      "status": "todo"
    },
    {
      "id": 2,
      "title": "Belajar Laravel",
      "deadline": "2026-11-15",
      "status": "done"
    }
  ]
}
```

> Jika belum ada tugas, `data` berupa array kosong `[]` (tetap status 200).

---

### 4.2 Detail Tugas

`GET /api/tasks/{id}`

**Respons 200**

```json
{
  "success": true,
  "message": "Detail tugas berhasil diambil",
  "data": {
    "id": 1,
    "title": "Mengerjakan tugas PKL",
    "deadline": "2026-11-20",
    "status": "todo"
  }
}
```

**Respons 403** — tugas milik pengguna lain

```json
{
  "success": false,
  "message": "Anda tidak berhak mengakses tugas ini"
}
```

**Respons 404** — tugas tidak ada

```json
{
  "success": false,
  "message": "Tugas tidak ditemukan"
}
```

---

### 4.3 Tambah Tugas

`POST /api/tasks`

**Request body**

| Field | Tipe | Aturan |
|-------|------|--------|
| `title` | string | wajib, maks. 255 |
| `deadline` | string | wajib, format `YYYY-MM-DD` |
| `status` | string | opsional, `todo` / `done` (default `todo`) |

```json
{
  "title": "Mengerjakan tugas PKL",
  "deadline": "2026-11-20"
}
```

**Respons 201**

```json
{
  "success": true,
  "message": "Tugas berhasil ditambahkan",
  "data": {
    "id": 1,
    "title": "Mengerjakan tugas PKL",
    "deadline": "2026-11-20",
    "status": "todo"
  }
}
```

**Respons 422**

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "title": ["Judul wajib diisi."],
    "deadline": ["Format tanggal harus YYYY-MM-DD."]
  }
}
```

---

### 4.4 Ubah Tugas

`PUT /api/tasks/{id}`

**Request body** — sama seperti 4.3 (`title` dan `deadline` wajib).

```json
{
  "title": "Mengerjakan laporan PKL",
  "deadline": "2026-11-25",
  "status": "todo"
}
```

**Respons 200**

```json
{
  "success": true,
  "message": "Tugas berhasil diubah",
  "data": {
    "id": 1,
    "title": "Mengerjakan laporan PKL",
    "deadline": "2026-11-25",
    "status": "todo"
  }
}
```

**Error:** 401, 403, 404, 422 (format sama seperti di atas).

---

### 4.5 Ubah Status Tugas (Selesai / Belum)

`PATCH /api/tasks/{id}/status`

**Request body**

| Field | Tipe | Aturan |
|-------|------|--------|
| `status` | string | wajib, `todo` atau `done` |

```json
{
  "status": "done"
}
```

**Respons 200**

```json
{
  "success": true,
  "message": "Status tugas berhasil diubah",
  "data": {
    "id": 1,
    "title": "Mengerjakan laporan PKL",
    "deadline": "2026-11-25",
    "status": "done"
  }
}
```

**Error:** 401, 403, 404, 422 (format sama seperti di atas).

---

### 4.6 Hapus Tugas

`DELETE /api/tasks/{id}`

**Respons 200**

```json
{
  "success": true,
  "message": "Tugas berhasil dihapus",
  "data": null
}
```

**Error:** 401, 403, 404 (format sama seperti di atas).

---

## 5. Ringkasan Endpoint

| Method | URL | Auth | Fungsi |
|--------|-----|------|--------|
| POST | `/api/register` | — | Daftar akun |
| POST | `/api/login` | — | Login, mendapat token |
| GET | `/api/profile` | 🔒 | Profil pengguna |
| POST | `/api/logout` | 🔒 | Logout |
| GET | `/api/tasks` | 🔒 | Daftar tugas |
| GET | `/api/tasks/{id}` | 🔒 | Detail tugas |
| POST | `/api/tasks` | 🔒 | Tambah tugas |
| PUT | `/api/tasks/{id}` | 🔒 | Ubah tugas |
| PATCH | `/api/tasks/{id}/status` | 🔒 | Ubah status tugas |
| DELETE | `/api/tasks/{id}` | 🔒 | Hapus tugas |

---

## 6. Catatan untuk FE (Mock)

- Buat mock JSON sesuai contoh respons di atas, **termasuk kasus gagal** (401, 403, 404, 422).
- Panggil API melalui **satu fungsi pemanggil terpusat** (misalnya `apiRequest(method, url, body)`), sehingga saat API asli siap, hanya sumber datanya yang diganti dari mock ke API.
- Simpan token dari respons login/register, lalu kirim sebagai `Authorization: Bearer {token}` pada endpoint 🔒.
- Siapkan tampilan untuk kondisi: **loading, data kosong, berhasil, dan error validasi**.

## 7. Catatan untuk BE

- Tulis spesifikasi OpenAPI (L5-Swagger) sejak awal dan bagikan hasil JSON-nya ke FE.
- Pastikan respons nyata **sama persis** dengan kontrak ini (nama field, tipe data, status code).
- Tugas hanya boleh diakses pemiliknya (gunakan Policy atau cek `user_id`).
- Jangan commit `.env`, `vendor`, `node_modules`, atau token asli.

---

## Riwayat Perubahan

| Tanggal | Versi | Perubahan | Disetujui oleh |
|---------|-------|-----------|----------------|
| 2026-11-03 | 1.0 | Kontrak awal | (BE) ........ & (FE) ........ |
