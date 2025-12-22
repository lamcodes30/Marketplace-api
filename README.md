# Marketplace API

A comprehensive REST API built with Laravel 8 for a multi-vendor catering and marketplace platform. This API manages users, catering services, menus, orders, payments, and more.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Database Models](#database-models)
- [Setup & Development](#setup--development)

## Features

- **User Management**: Registration, login, password management
- **Multi-Role System**: Support for Users, Agents, Catering Partners, Marketing
- **Menu & Catalog Management**: Categories, products, discounts
- **Catering Services**: Catering profiles, service management
- **Order Management**: Checkout, order tracking, cancellation
- **Payment Integration**: Multiple payment gateways (Espay, Paspay)
- **Location Services**: Province, district, sub-district, village hierarchies
- **Notifications**: Real-time notifications for users, agents, catering partners
- **Rating & Reviews**: Customer ratings and feedback system
- **Challenges & Rewards**: User challenges and loyalty program
- **Withdrawal System**: Agent and catering partner withdrawal management
- **Articles & Content**: Knowledge base and informational content

## Tech Stack

- **Framework**: Laravel 8.x
- **PHP**: ^7.3
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Passport (OAuth 2.0)
- **API Format**: RESTful JSON
- **Frontend Build**: Laravel Mix with Webpack

## Requirements

- PHP >= 7.3
- Composer
- Node.js & npm
- MySQL/MariaDB database
- Git

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Marketplace-api
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Create Passport encryption keys**
   ```bash
   php artisan passport:install
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed the database (optional)**
   ```bash
   php artisan db:seed
   ```

## Configuration

### Environment Setup

Edit `.env` file with your configuration:

```env
APP_NAME=Marketplace
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketplace
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=ssl
```

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   │       ├── AuthController.php
│   │   │       ├── UserController.php
│   │   │       ├── MenuController.php
│   │   │       ├── MitraKateringController.php
│   │   │       ├── PesananController.php
│   │   │       ├── WilayahController.php
│   │   │       └── EspayController.php
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Models/ (20+ models)
│   │   ├── User.php
│   │   ├── Menu.php
│   │   ├── Katering.php
│   │   ├── Profile_katering.php
│   │   ├── Pembelian.php
│   │   ├── Cart.php
│   │   ├── Payment.php
│   │   ├── Espay_payment.php
│   │   ├── Paspay.php
│   │   └── ...
│   └── Providers/
├── routes/
│   ├── api.php (API routes)
│   ├── web.php
│   └── channels.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── passport.php
│   └── ...
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── tests/
│   ├── Unit/
│   └── Feature/
└── storage/
    ├── app/
    ├── logs/
    └── framework/
```

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/ubah_password` - Change password
- `POST /api/hash_password` - Hash a password

### User Management
- `GET /api/user/get_artikel` - Get articles
- `GET /api/user/get_kategori` - Get categories
- `POST /api/user/update_user` - Update user profile
- `POST /api/user/cek_password` - Check password

### Menu & Catalog
- `POST /api/user/get_menu_kategori` - Get menu by category
- `POST /api/user/get_menu_diskon` - Get discounted menus
- `POST /api/user/get_menu_by_katering` - Get menus from specific catering
- `POST /api/user/pencarian_menu` - Search menus

### Orders & Checkout
- `POST /api/user/checkout_array` - Checkout multiple items
- `POST /api/user/selesai_transaksi_pembelian` - Complete purchase
- `POST /api/user/tolak_pesanan` - Reject order
- `POST /api/user/bayar_cashback` - Pay with cashback

### Payments
- `POST /api/inquiry` - Espay payment inquiry
- `POST /api/notif` - Payment notification handler

### Location Services
- `GET /api/get_provinsi` - Get provinces
- `POST /api/get_kabupaten` - Get districts
- `POST /api/get_kecamatan` - Get sub-districts
- `POST /api/get_desa` - Get villages

### Catering Services
- `POST /api/user/get_katering_by_id` - Get catering details
- `POST /api/user/get_katering_by_kota` - Get catering by city
- `POST /api/user/get_profile_katering` - Get catering profile

### Ratings & Reviews
- `POST /api/user/rating` - Submit rating

## Authentication

The API uses **Laravel Passport** for OAuth 2.0 authentication.

### Getting an Access Token

1. Register a new user:
   ```bash
   POST /api/register
   {
     "name": "User Name",
     "email": "user@example.com",
     "password": "password"
   }
   ```

2. Login to get token:
   ```bash
   POST /api/login
   {
     "email": "user@example.com",
     "password": "password"
   }
   ```

3. Use the returned `access_token` in Authorization header:
   ```
   Authorization: Bearer {access_token}
   ```

## Database Models

The application includes 20+ Eloquent models:

- **User** - User accounts
- **Menu** - Menu items
- **Katering** - Catering services
- **Profile_katering** - Catering profiles
- **Pembelian** - Purchase/Order records
- **Cart** - Shopping cart
- **Payment** - Payment records
- **Transaksi** - Transaction records
- **Rating** - User ratings
- **Kategori** - Categories
- **Banner** - Marketing banners
- **Artikel** - Articles
- **Provinsi, Kabupaten, Kecamatan** - Location hierarchy
- **Agen** - Agent profiles
- **Marketing** - Marketing partner profiles
- **Notifikasi** (multiple) - Notifications for different user types

## Setup & Development

### Running the Development Server

```bash
php artisan serve
```

Server will be available at `http://localhost:8000`

### Database Migrations

```bash
# Run all pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Refresh database (reset and re-seed)
php artisan migrate:refresh --seed
```

### Frontend Build

```bash
# Development build
npm run dev

# Production build
npm run production

# Watch for changes
npm run watch
```

### Testing

```bash
php artisan test
# or
./vendor/bin/phpunit
```

### Troubleshooting

- **Clear cache**: `php artisan cache:clear`
- **Clear config**: `php artisan config:clear`
- **Regenerate autoloader**: `composer dump-autoload`
- **Reset everything**: `php artisan migrate:refresh --seed`

## License

This project is licensed under the MIT License.

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
