# 📚 Sistem Pembayaran SPP Sekolah

Aplikasi manajemen pembayaran SPP berbasis web untuk **Yayasan Pendidikan**, mencakup jenjang **TK**, **SD**, dan **SMP**. Dibangun menggunakan Laravel 12 dengan fitur multi-role admin, setoran, laporan, dan cetak kartu pembayaran.

---

## 🖼️ Tampilan

> Kartu Uang Sekolah — cetak 4 kartu per halaman F4

![Kartu Pembayaran](docs/kartu-preview.png)

---

## ✨ Fitur

- **Multi-role user** — Admin Yayasan (semua jenjang), Admin TK, Admin SD, Admin SMP
- **Manajemen siswa** — CRUD lengkap + import massal via Excel
- **Pembayaran SPP** — Input pembayaran per bulan, multi-bulan, sekaligus
- **Setoran** — Rekap setoran per jenjang ke bendahara yayasan
- **Laporan** — Filter per jenjang, kelas, bulan, tanggal; rekap per kelas
- **Cetak kartu** — Kartu Uang Sekolah per siswa, 4 per halaman F4 (PDF)
- **Manajemen user** — CRUD admin, reset password
- **Formula tagihan** — SPP − Donatur + Mamin

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.4, Laravel 12 |
| Frontend | Bootstrap 5, Bootstrap Icons |
| Database | MySQL 8 |
| PDF | barryvdh/laravel-dompdf |
| Excel Import/Export | maatwebsite/excel |
| Auth | Laravel built-in session auth |

---

## ⚙️ Requirement

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js >= 18 (untuk asset compile, opsional)
- Extension PHP: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `gd`, `zip`

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/nama-repo.git
cd nama-repo
```

### 2. Install Dependency

```bash
composer install
```

### 3. Salin File Environment

```bash
cp .env.example .env
```

### 4. Generate App Key

```bash
php artisan key:generate
```

### 5. Konfigurasi Database

Buka `.env` dan sesuaikan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pembayaran_sekolah
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Buat Database & Jalankan Migrasi

```bash
# Buat database terlebih dahulu di MySQL, lalu:
php artisan migrate
```

### 7. Jalankan Seeder (Data Awal)

```bash
php artisan db:seed
```

Seeder akan membuat:
- User admin yayasan default
- Contoh data siswa (opsional)

### 8. Buat Storage Link

```bash
php artisan storage:link
```

### 9. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser: **http://127.0.0.1:8000**

---

## 👤 Akun Default

Setelah seeder, akun yang tersedia:

| Email | Password | Role |
|-------|----------|------|
| `admin@yayasan.sch.id` | `password` | Admin Yayasan |
| `admin.tk@yayasan.sch.id` | `password` | Admin TK |
| `admin.sd@yayasan.sch.id` | `password` | Admin SD |
| `admin.smp@yayasan.sch.id` | `password` | Admin SMP |

> ⚠️ **Ganti password** segera setelah login pertama kali.

---

## 📁 Struktur Direktori Penting

```
app/
├── Http/Controllers/
│   ├── SiswaController.php         # CRUD siswa
│   ├── SiswaImportController.php   # Import Excel
│   ├── PembayaranController.php    # Input pembayaran
│   ├── SetoranController.php       # Setoran ke yayasan
│   ├── LaporanController.php       # Laporan & rekap
│   ├── CetakController.php         # Cetak PDF kartu
│   └── UserController.php          # Manajemen admin
├── Models/
│   ├── Siswa.php
│   ├── Pembayaran.php
│   ├── Setoran.php
│   └── User.php
├── Imports/
│   └── SiswaImport.php             # Maatwebsite Excel import
└── Exports/
    └── SiswaTemplateExport.php     # Template Excel download

resources/views/
├── siswa/          # CRUD & import siswa
├── pembayaran/     # Input & detail pembayaran
├── setoran/        # Setoran per jenjang
├── laporan/        # Laporan & rekap
├── cetak/          # Kartu PDF
├── admin/users/    # Manajemen user
└── layouts/        # Layout utama
```

---

## 📊 Formula Tagihan

```
Tagihan per Bulan = SPP - Donatur + Mamin
```

- **SPP** (`nominal_pembayaran`) — Uang sekolah bruto per bulan
- **Donatur** (`nominal_donator`) — Keringanan dari donatur, mengurangi tagihan
- **Mamin** (`nominal_mamin`) — Makan & minum, hanya berlaku untuk TK

---

## 📥 Import Siswa via Excel

Template Excel dapat diunduh dari halaman **Import Siswa**. Format kolom:

| Kolom | Keterangan |
|-------|-----------|
| `nama` | Nama lengkap siswa (wajib) |
| `kelas` | KB/A/B (TK) · I–VI (SD) · VII–IX (SMP) (wajib) |
| `jenjang` | TK / SD / SMP (wajib) |
| `nominal_pembayaran` | SPP per bulan |
| `nominal_donator` | Keringanan (0 jika tidak ada) |
| `nominal_mamin` | Makan & minum, hanya TK |

> ID Siswa, Tanggal Masuk (1 Juli), dan Status (aktif) **digenerate otomatis**.

---

## 🖨️ Cetak Kartu

- Layout **4 kartu per halaman** ukuran F4 (215mm × 330mm)
- Header sekolah dengan logo, nama yayasan, dan alamat
- Tabel bulan Juli–Juni (1 tahun ajaran)
- Kolom: Uang Sekolah, Donatur, Mamin (TK), Yang Dibayar, Tanggal, Tanda Tangan
- Footer tanda tangan Kepala Sekolah & Orang Tua/Wali

---

## 🔐 Role & Akses

| Role | Akses |
|------|-------|
| `admin_yayasan` | Semua jenjang, laporan gabungan, manajemen user |
| `admin_tk` | Hanya data jenjang TK |
| `admin_sd` | Hanya data jenjang SD |
| `admin_smp` | Hanya data jenjang SMP |

---

## 🐛 Troubleshooting

**Route not defined**
```bash
php artisan route:clear
php artisan cache:clear
```

**View not found**
```bash
php artisan view:clear
```

**Storage permission error (Linux/Mac)**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Composer autoload error**
```bash
composer dump-autoload
```

---

## 📝 Lisensi

Hak cipta © 2025–2026 Yayasan Pendidikan Kristen. Seluruh hak dilindungi.

---

## 👨‍💻 Developer

Dibuat untuk kebutuhan internal yayasan. Untuk pertanyaan teknis, hubungi tim pengembang.
