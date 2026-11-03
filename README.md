# Ruang — To-Do app

Frontend Laravel Blade + JavaScript untuk daftar tugas pribadi. Tampilan mencakup register/login, ringkasan dashboard, tambah/edit/hapus tugas, status selesai, pencarian, filter, serta state loading, kosong, sukses, dan error.

## Menjalankan

```sh
composer install
npm install
copy .env.example .env
php artisan key:generate
npm run dev
php artisan serve
```

Buka `http://localhost:8000`. Mock aktif secara default dan menyimpan sesi serta tugas demo di `localStorage` browser.

- Akun demo: `demo@ruang.id`
- Kata sandi: `ruang1234`

## Beralih ke API

Setelah endpoint backend tersedia, ubah `FRONTEND_MOCK=false` di `.env`, lalu jalankan `php artisan config:clear`. Frontend memakai `APP_URL` sebagai host web dan mengirim bearer token untuk request terlindungi.

Kontrak endpoint yang digunakan adapter di `resources/js/app.js`:

| Method | Endpoint | Request |
| --- | --- | --- |
| POST | `/api/register` | `name`, `email`, `password`, `password_confirmation` |
| POST | `/api/login` | `email`, `password` |
| GET | `/api/tasks` | Bearer token |
| POST | `/api/tasks` | Bearer token, `title`, `deadline`, `status` |
| PUT | `/api/tasks/{id}` | Bearer token, `title`, `deadline`, `status` |
| PATCH | `/api/tasks/{id}/status` | Bearer token, `status` (`todo` atau `done`) |
| DELETE | `/api/tasks/{id}` | Bearer token |

Endpoint login dan register diharapkan mengembalikan `{ "user": { ... }, "token": "..." }`. Endpoint tugas dapat mengembalikan objek langsung atau objek resource Laravel dengan properti `data`. Error validasi sebaiknya menggunakan format Laravel (`message` dan `errors`).
