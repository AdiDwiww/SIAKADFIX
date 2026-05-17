# 📚 SIA Akademik – Setup Guide

## Stack
- **Backend**: PHP + MySQL (REST API)
- **Frontend**: HTML + CSS + JavaScript (Vanilla)
- **Server lokal**: XAMPP

---

## ⚡ Langkah Setup

### 1. Copy Project ke XAMPP
Copy seluruh folder `UAS` ke dalam `C:\xampp\htdocs\`:
```
C:\xampp\htdocs\uas\
```

### 2. Import Database
1. Buka XAMPP → Start **Apache** dan **MySQL**
2. Buka `http://localhost/phpmyadmin`
3. Klik **Import** → pilih file `database/schema.sql`
4. Klik **Go**

### 3. Akses Aplikasi
Buka browser → `http://localhost/uas/`

---

## 🔑 Akun Default
| Username | Password | Role  |
|----------|----------|-------|
| `admin`  | `admin123` | ADMIN |

---

## 📁 Struktur Project
```
uas/
├── api/                  ← PHP REST API (backend)
│   ├── config/           ← Koneksi database
│   ├── helpers/          ← JWT, Response, Upload
│   ├── middleware/        ← Auth middleware
│   ├── controllers/       ← AuthController, DashboardController, dll
│   ├── routes/api.php    ← URL Router
│   └── index.php         ← Entry point
├── assets/
│   ├── css/style.css     ← Global CSS
│   └── js/               ← api.js, auth.js, layout.js, notifications.js
├── database/schema.sql   ← Script database
├── pages/                ← HTML pages
│   ├── login.html
│   ├── register.html
│   ├── dashboard.html
│   ├── mahasiswa.html
│   ├── dosen.html
│   ├── kuliah.html
│   └── change-password.html
├── uploads/              ← Foto mahasiswa & dosen (auto-created)
└── index.html            ← Redirect otomatis
```

## 🔐 Role & Akses
| Fitur            | ADMIN | DOSEN | MAHASISWA |
|------------------|:-----:|:-----:|:---------:|
| Dashboard        | ✅    | ✅    | ✅        |
| Lihat Mahasiswa  | ✅    | ✅    | ❌        |
| CRUD Mahasiswa   | ✅    | ❌    | ❌        |
| Lihat Dosen      | ✅    | ❌    | ❌        |
| CRUD Dosen       | ✅    | ❌    | ❌        |
| Lihat Mata Kuliah| ✅    | ✅    | ✅        |
| CRUD Mata Kuliah | ✅    | ❌    | ❌        |
| Ganti Password   | ✅    | ✅    | ✅        |

## ⚙️ Konfigurasi Database
Edit file `api/config/Database.php` jika perlu:
```php
private string $host     = 'localhost';
private string $dbname   = 'uas_psi';
private string $username = 'root';
private string $password = '';
```

## 📸 Upload Foto
- Format: JPG, PNG, GIF, WebP
- Ukuran maksimal: **5 MB**
- Foto disimpan di: `uploads/mahasiswa/` dan `uploads/dosen/`
- Folder dibuat otomatis saat pertama upload
