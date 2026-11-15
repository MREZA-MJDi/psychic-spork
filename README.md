# Jani / Janan Storefront and Administration

Laravel storefront and administration platform for the Persian Jani / Janan store. Modules include products and variants, product media, categories, brands, customers, orders, inventory, accounting, cheque payment and wholesale workflows.

## Stack

- PHP 8.2+ and Laravel 12
- Blade, Vite and the Laravel Eloquent ORM
- MySQL/MariaDB for the local XAMPP setup
- PHPUnit tests (the test suite uses in-memory SQLite)

## Windows / XAMPP setup

### 1. Get the Holoo integration branch

Until PR [#52](https://github.com/MREZA-MJDi/psychic-spork/pull/52) is merged, the integration code is on its own branch.

For a fresh clone:

```bat
git clone --branch feat/nila-holoo-webservice-sync-2026-10-25 --single-branch https://github.com/MREZA-MJDi/psychic-spork.git jani
cd jani
```

If the repository is already cloned in `C:\\xampp2\\htdocs\\jani`, keep that copy and run:

```bat
cd /d C:\\xampp2\\htdocs\\jani
git fetch origin
git switch --track origin/feat/nila-holoo-webservice-sync-2026-10-25
```

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
