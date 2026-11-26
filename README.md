# Jani / Janan Storefront and Administration

Laravel storefront and administration platform for the Persian Jani / Janan store. Modules include products and variants, product media, categories, brands, customers, orders, inventory, accounting, cheque payment and wholesale workflows.

**Live storefront** · [jananunderwear.com](https://jananunderwear.com/)

## Technical highlights

- Persian RTL storefront with size and color variants, customer accounts and retail and wholesale ordering.
- Admin workflows for inventory, order fulfillment, cheque review and double-entry accounting.
- Holoo / Nila catalog import with spreadsheet preview, validation and explicit approval before writing changes.
- Production runbook and release scripts for Nginx and PHP-FPM deployment.

## Stack

- PHP 8.2+ and Laravel 12
- Blade, Vite and the Laravel Eloquent ORM
- MySQL/MariaDB for the local XAMPP setup
- PHPUnit tests (the test suite uses in-memory SQLite)

## Windows / XAMPP setup

### 1. Clone the current storefront

```bat
git clone https://github.com/MREZA-MJDi/psychic-spork.git jani
cd jani
```

The Holoo integration is included in `main`.

### 2. Create the private local env

Start MySQL in XAMPP and create a database named `jani` in phpMyAdmin using a Unicode `utf8mb4` collation. Create `.env` before running Composer's Laravel discovery scripts, and do not overwrite an existing local `.env`:

```bat
if not exist .env copy .env.example .env
```

Review the `DB_*` values in `.env`. The example assumes MySQL at `127.0.0.1:3306`, database `jani`, user `root`, and an empty password. Adjust them to match XAMPP on your machine, then install PHP dependencies:

The local `.env.example` uses `CACHE_STORE=file` so Composer's `package:discover` can run before migrations create the database-backed `cache` table. The service provider also avoids querying persistent site settings during non-test Artisan maintenance commands. Keep `DB_CONNECTION=mysql` for the local XAMPP app; SQLite is used only by the automated test configuration.


```bat
composer install
```

The sample file intentionally leaves `JANAN_ADMIN_*` values commented. In local mode, the application has a fallback admin identity; see the local login note below. Never use local fallback credentials on a shared or production server.

### 3. Migrate and build

```bat
php artisan key:generate
php artisan config:clear
php artisan migrate
php artisan storage:link
npm install
npm run build
```

To create/synchronize only the admin user, run the targeted seeder:

```bat
php artisan db:seed --class=AdminUserSeeder
```

Do **not** run `php artisan db:seed` unless you intentionally want all configured seeders, including demonstration catalog/order data. Never run `migrate:fresh` against a database containing data you need.

The local fallback admin credentials, when `APP_ENV=local` and `JANAN_ADMIN_*` variables are not set, are:

- Phone: `09120000000`
- Password: `password`

These are local-development defaults only; set a unique password before exposing the site on any shared network.

### 4. Run the app

```bat
php artisan serve
```

Open `http://127.0.0.1:8000`. For frontend hot reload, use `npm run dev` in another terminal instead of relying on a stale production build during active UI development.

## Holoo / Nila product sync

The `.env.example` keeps Holoo/Nila sync disabled and ZarinPal in sandbox mode. Configure `NILA_BASE_URL`, `NILA_USERNAME`, `NILA_PASSWORD` and `NILA_DATABASE` in the **private local** `.env`, then set `NILA_ENABLED=true` only after confirming that the web service is reachable from the Laravel host.

First run the HTTP/database tests and a no-write preview:

```bat
php artisan test --filter=NilaHolooWebServiceTest
php artisan test --filter=NilaCatalogImporterTest
php artisan config:clear
php artisan nila:sync-products --dry-run --limit=10
```

The preview does not write catalog rows. Verify product names, ERP/SKU codes, price field, price unit and stock against a known Holoo item before making any write import. Janan's storefront prices are labeled in toman; if the selected Holoo field is in rial, the multiplier needs to be adjusted accordingly.

Once the sample is verified, a small local write run is:

```bat
php artisan nila:sync-products --limit=10
```

New products are inactive by default, and images/galleries remain managed in Janan. In production, write imports require `--confirm-production`. See [docs/JANAN_NILA_INTEGRATION.md](docs/JANAN_NILA_INTEGRATION.md) for the detailed field mapping and runbook.

## Tests

```bat
php artisan test
```

Holoo feature tests mock HTTP responses. They verify the expected request/response handling but do not prove that a real Holoo installation is reachable or that its price units are correct.

## Security and operations

Do not commit `.env` or real service/payment credentials. Keep the Holoo web-service port behind a trusted LAN/VPN rather than exposing it publicly. Do not use production payment credentials in development. Check both authorized and unauthorized access to administrative routes before deployment.

## Production deployment

1. Back up the database, preserve the existing `.env` and `storage/app/public`, and make the repository's `public` directory the domain's document root. Configure HTTPS on the origin. The Nginx example in `deploy/nginx/jani.conf.example` needs the real server names, document root, PHP-FPM socket and TLS configuration.
2. For a fresh install, populate a private `.env` from `.env.production.example` and generate the application key once. Set `APP_URL` to the canonical HTTPS URL, configure the production database and credentials, and verify the session domain. Run `bash deploy/deploy.sh` from the checked-out release.
3. Keep shared CDN caching limited to versioned static assets. Bypass the cache for HTML, session responses, search, account, cart, checkout and payment callbacks. Preserve older Vite fingerprints through the rollback window so already-open pages can load their assets.
4. Check `/up` and verify that the homepage actually belongs to Janan. Check `/products`, `/drclubz`, `/wholesale`, and `/wholesale/catalog`, then verify login, cart and a controlled payment callback against the configured gateway.

On a VPS shared with other sites, use a dedicated application user and database. For Debian or Ubuntu, `deploy/php-fpm/janan.conf.example` defines a dedicated PHP-FPM pool and socket. Install it in the selected PHP version's `fpm/pool.d`, validate it with that version's `php-fpm -t`, and point Janan's Nginx virtual host at `/run/php/janan.sock`. Adjust the pool user, Nginx worker group, and working directory if they differ on the host.

Product and variant uploads accept common image files up to 10 MiB each, with a 32 MiB request limit and a 32-megapixel decoding limit. Large images are resized without cropping and stored as WebP, up to 1,600 pixels on the longest edge and 512 KiB. Enable GD with WebP support and EXIF in PHP CLI and FPM. Existing media URLs remain valid; new uploads are compressed on the server. The production example reserves 2 GiB of free local disk space for co-hosted applications. For a hard Janan-only storage quota, use a filesystem quota or a dedicated volume for `storage/app/public`.
