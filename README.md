# Hajusrakendused

Full-stack web app combining 5 distributed application features into one platform.

**Live demo:** https://hajusrakendused-main-jiuhhi.free.laravel.cloud/

## Tech Stack

- Laravel 12 + PHP 8.2+
- Vue 3 + TypeScript + Inertia.js
- Tailwind CSS + reka-ui
- PostgreSQL / SQLite
- Stripe, OpenWeatherMap, Leaflet

## Features

1. **Weather** — search weather by city via OpenWeatherMap (cached)
2. **Map** — Leaflet map with marker CRUD
3. **Blog** — posts and comments with auth
4. **Shop** — 9 products, cart, Stripe checkout
5. **Recipes API** — JSON API with filtering, sorting, search, and rate limiting

## Setup

```bash
git clone https://github.com/EmilySelge/hajusrakendused.git
cd hajusrakendused
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=ProductSeeder
composer dev
```

Add to `.env`:
```bash
OPENWEATHERMAP_API_KEY=your_key
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_key
```

Visit `http://localhost:8000`.
