# 📚 Aplikasi Perpustakaan — Bootcamp UKK RPL

Aplikasi manajemen perpustakaan berbasis **PHP + MySQL** (tanpa framework).

---

## Fitur

- ✅ Login & Session (dengan proteksi 3x salah)
- ✅ Role: Admin (petugas) dan User (siswa)
- ✅ CRUD Kategori
- ✅ CRUD Buku (dengan upload cover)
- ✅ CRUD User
- ✅ Sistem Peminjaman & Pengembalian
- ✅ Laporan peminjaman + Print
- ✅ Responsive (mobile-friendly)

---

## Cara Install (XAMPP)

1. Copy folder `perpustakaan/` ke `C:\xampp\htdocs\`
2. Buka **phpMyAdmin** → Import file `database.sql`
3. Buka browser: `http://localhost/perpustakaan/login.php`

---

## Akun Default

| Role  | Username | Password  |
|-------|----------|-----------|
| Admin | admin    | admin123  |
| Siswa | siswa1   | siswa123  |

---

## Struktur Folder

```
perpustakaan/
├── config.php          ← Koneksi database
├── login.php           ← Halaman login
├── logout.php          ← Proses logout
├── dashboard.php       ← Dashboard utama
├── database.sql        ← File SQL database
├── includes/
│   ├── navbar.php
│   └── sidebar.php
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── covers/         ← Upload cover buku (auto-create)
├── categories/
│   └── index.php       ← CRUD Kategori
├── books/
│   └── index.php       ← CRUD Buku
├── users/
│   └── index.php       ← CRUD User
├── peminjaman/
│   └── index.php       ← Peminjaman & Pengembalian
└── report/
    └── index.php       ← Laporan & Print
```

---

## Stack Teknologi

- **Frontend**: HTML5, CSS3 (Vanilla)
- **Backend**: PHP 7+ (tanpa framework)
- **Database**: MySQL via MySQLi
