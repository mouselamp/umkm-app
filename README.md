# Siomay Manager - Sistem Manajemen UMKM

Aplikasi berbasis web untuk manajemen usaha produksi makanan ringan (Siomay/Dimsum).

![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-blue)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4)

## ✨ Fitur

### Master Data
- 📦 Produk & Bahan Baku
- 👥 Pelanggan & Supplier
- 👨‍💼 Karyawan
- 📋 Resep Produksi (BOM)

### Transaksi
- 🛒 Pembelian Bahan Baku
- 🏭 Input Produksi
- 📝 Pesanan Penjualan

### Keuangan
- 💰 Modal Usaha
- 💳 Utang & Pembayaran
- 💵 Gaji Karyawan
- 🏢 Aset Tetap & Depresiasi

### Dashboard & Laporan
- 📊 Ringkasan Bisnis
- 📈 Stok Inventori
- 💹 Perhitungan Laba/Rugi

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11, PHP 8.2 |
| Database | MySQL 8.0+ |
| Auth | Laravel Sanctum (JWT Token) |
| Frontend | Alpine.js 3, Tailwind CSS 3 |
| Build | Vite 6 |

## 📋 Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL/MariaDB

## 🚀 Installation

```bash
# Clone repository
git clone https://github.com/yourusername/umkm.git
cd umkm

# Install PHP dependencies
composer install

# Copy environment
cp .env.example .env

# Generate key
php artisan key:generate

# Configure database in .env
# DB_DATABASE=umkm
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations with seeders
php artisan migrate --seed

# Install & build frontend
npm install
npm run build
```

## 💻 Development

```bash
# Run dev server with hot reload
npm run dev

# Available artisan commands
php artisan route:list --path=api   # List API routes
php artisan migrate:fresh --seed    # Reset database
php artisan optimize:clear          # Clear all cache
```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/Api/V1/    # API Controllers
│   ├── Http/Resources/              # API Resources
│   └── Models/                      # Eloquent Models
├── resources/
│   ├── views/                       # Blade templates
│   ├── js/app.js                    # Alpine.js setup
│   └── css/app.css                  # Tailwind CSS
└── routes/
    ├── api.php                      # API routes
    └── web.php                      # Web routes
```

## 🔌 API Endpoints

Base URL: `/api/v1`

| Module | Endpoints |
|--------|-----------|
| Auth | `/auth/login`, `/auth/register`, `/auth/me` |
| Products | `/products` |
| Materials | `/materials`, `/material-categories` |
| Customers | `/customers` |
| Suppliers | `/suppliers` |
| Employees | `/employees` |
| Recipes | `/recipes` |
| Purchases | `/purchases` |
| Productions | `/productions` |
| Orders | `/orders` |
| Finance | `/capitals`, `/debts`, `/wages`, `/assets` |
| Reports | `/reports/dashboard`, `/reports/profit` |

## 📄 License

MIT License
