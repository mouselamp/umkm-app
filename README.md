# Sistem Manajemen Usaha Produksi & Penjualan Makanan Ringan (UMKM)

Aplikasi berbasis web untuk manajemen usaha produksi makanan ringan (Siomay/Dimsum).

## Tech Stack

### Backend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | Laravel | 11.47.0 |
| PHP | PHP | 8.2.29 |
| Database | MySQL | 8.0+ |
| Authentication | Laravel Sanctum | 4.2.1 |
| API | RESTful API | JSON |

### Frontend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| JavaScript | Alpine.js | 3.14.0 |
| CSS | Tailwind CSS | 3.4.13 |
| HTTP Client | Axios | 1.7.4 |
| Build Tool | Vite | 6.0.11 |

## Requirements

- Docker dengan container `php82-fpm`
- Node.js & npm
- MySQL/MariaDB

## Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd umkm
```

### 2. Install PHP Dependencies (via Docker)
```bash
docker exec -w /var/www/php82/umkm php82-fpm composer install
```

### 3. Copy Environment File
```bash
cp .env.example .env
```

### 4. Configure Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=your_mysql_host
DB_PORT=3306
DB_DATABASE=umkm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Generate Application Key
```bash
docker exec -w /var/www/php82/umkm php82-fpm php artisan key:generate
```

### 6. Run Migrations
```bash
docker exec -w /var/www/php82/umkm php82-fpm php artisan migrate
```

### 7. Install NPM Dependencies
```bash
npm install
```

### 8. Build Frontend Assets
```bash
# Development (with hot reload)
npm run dev

# Production
npm run build
```

## Development Commands

### Artisan Commands (via Docker)
```bash
# Clear all cache
docker exec -w /var/www/php82/umkm php82-fpm php artisan optimize:clear

# Clear config cache
docker exec -w /var/www/php82/umkm php82-fpm php artisan config:clear

# Run migrations
docker exec -w /var/www/php82/umkm php82-fpm php artisan migrate

# Fresh migration (reset database)
docker exec -w /var/www/php82/umkm php82-fpm php artisan migrate:fresh

# Create migration
docker exec -w /var/www/php82/umkm php82-fpm php artisan make:migration create_table_name

# Create model with migration
docker exec -w /var/www/php82/umkm php82-fpm php artisan make:model ModelName -m

# Create controller
docker exec -w /var/www/php82/umkm php82-fpm php artisan make:controller Api/V1/ControllerName --api

# Create API resource
docker exec -w /var/www/php82/umkm php82-fpm php artisan make:resource ResourceName

# Create form request
docker exec -w /var/www/php82/umkm php82-fpm php artisan make:request RequestName

# List routes
docker exec -w /var/www/php82/umkm php82-fpm php artisan route:list

# List API routes only
docker exec -w /var/www/php82/umkm php82-fpm php artisan route:list --path=api

# Tinker (interactive shell)
docker exec -it -w /var/www/php82/umkm php82-fpm php artisan tinker
```

### NPM Commands
```bash
# Install dependencies
npm install

# Development server with hot reload
npm run dev

# Production build
npm run build
```

### Composer Commands (via Docker)
```bash
# Install dependencies
docker exec -w /var/www/php82/umkm php82-fpm composer install

# Update dependencies
docker exec -w /var/www/php82/umkm php82-fpm composer update

# Dump autoload
docker exec -w /var/www/php82/umkm php82-fpm composer dump-autoload
```

## Project Structure

```
umkm/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/          # API Controllers
│   │   ├── Requests/            # Form Requests
│   │   └── Resources/           # API Resources
│   ├── Models/                  # Eloquent Models
│   └── Services/                # Business Logic
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   └── SPECIFICATION.md         # System Specification
├── public/
│   └── build/                   # Compiled assets
├── resources/
│   ├── css/
│   │   └── app.css              # Tailwind CSS
│   ├── js/
│   │   ├── app.js               # Alpine.js entry
│   │   └── bootstrap.js         # Axios config
│   └── views/
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
├── storage/
├── tests/
└── vendor/
```

## API Endpoints

Base URL: `/api`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /user | Get authenticated user (requires auth) |

*More endpoints will be added as development progresses.*

## Documentation

Lihat [docs/SPECIFICATION.md](docs/SPECIFICATION.md) untuk dokumentasi lengkap sistem termasuk:
- Latar belakang & tujuan
- Tech Stack detail
- Workflow diagrams
- Conceptual Data Model (CDM)
- API Endpoints structure
- Prioritas implementasi

## License

MIT License
