# Store Administration Platform

This Laravel application contains an e-commerce storefront and a broad administration area. The current routes include product, product variant/media, brand, category, customer, order, inventory, financial, site-content, and account operations. This repository also contains cheque and wholesale-related controllers; consult the current routes and tests for exact behavior.

## Dedicated admin dashboard
The project includes its own dedicated administration dashboard for managing the store's operational modules. The current repository includes cheque- and wholesale-related code; consult the routes and tests for the exact behavior of each module.

## Stack
- PHP `^8.2`, Laravel `^12.0`
- Blade, Vite and Laravel's Eloquent ORM
- Relational database with migrations and seeders
- PHPUnit tests

## Requirements
PHP 8.2+, Composer, Node.js/npm, and a Laravel-supported database.

## Local installation
```bash
git clone https://github.com/MREZA-MJDi/psychic-spork.git
cd psychic-spork
composer install
```

Copy `.env.example` to `.env` (`copy .env.example .env` in Windows CMD; `cp .env.example .env` on macOS/Linux). Create a disposable local database, set `DB_*` values, and configure any external services using local credentials.

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. For hot reload, run `npm run dev` in another terminal.

## Tests
```bash
php artisan test
```

## Security and data handling
Do not use production payment credentials in development. Review seeders before running them, and never run `migrate:fresh` against data you need. Administrative routes should be tested with both authorized and unauthorized users.

## Links
- Repository: https://github.com/MREZA-MJDi/psychic-spork
- Laravel documentation: https://laravel.com/docs/12.x
