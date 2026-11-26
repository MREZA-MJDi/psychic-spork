#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$(readlink -f "$0")")/.."
fail() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }
command -v php >/dev/null || fail 'PHP CLI is missing.'
command -v composer >/dev/null || fail 'Composer is missing.'
command -v npm >/dev/null || fail 'Node.js/npm are required to build fresh Vite assets.'
[[ -f artisan && -f composer.lock && -f package-lock.json ]] || fail 'Run this script from a cloned Janan repository.'
[[ -f .env ]] || fail 'Create .env from .env.production.example and fill production values first.'

php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' || fail 'PHP 8.2+ is required; PHP 8.5 is supported.'
php -r 'exit(extension_loaded("gd") && extension_loaded("exif") && (imagetypes() & IMG_WEBP) ? 0 : 1);' \
    || fail 'Enable PHP GD with WebP support and the EXIF extension for image uploads.'
node -e 'const [a,b]=process.versions.node.split(".").map(Number); process.exit((a>22 || (a===22 && b>=12) || (a===20 && b>=19)) ? 0 : 1)' \
    || fail 'Vite 7 requires Node.js 20.19+ or 22.12+; use a supported LTS release.'

grep -Eq '^APP_ENV=production$' .env || fail 'APP_ENV must be production.'
grep -Eq '^APP_DEBUG=false$' .env || fail 'Set APP_DEBUG=false in .env.'
grep -Eq '^DB_CONNECTION=mysql$' .env || fail 'Set DB_CONNECTION=mysql in .env.'
grep -Eq '^APP_KEY=base64:.+' .env || fail 'Generate APP_KEY once with php artisan key:generate.'

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci --no-audit --no-fund
build_dir="$(mktemp -d storage/framework/vite-build.XXXXXX)"
maintenance=0
cleanup() {
    code=$?
    rm -rf -- "$build_dir"
    if [[ "$maintenance" == 1 ]]; then php artisan up || true; fi
    exit "$code"
}
trap cleanup EXIT

npm run build -- --outDir "$(realpath "$build_dir")"
[[ -s "$build_dir/manifest.json" && -d "$build_dir/assets" ]] || fail 'Vite build output is incomplete.'
# Keep previous fingerprints available to tabs opened before this release.
mkdir -p public/build/assets
cp -a "$build_dir/assets/." public/build/assets/

php artisan down --retry=60
maintenance=1
php artisan migrate --force
php artisan storage:link --force
php artisan optimize
# A Vite development marker takes precedence over the production manifest.
rm -f public/hot
mv "$build_dir/manifest.json" public/build/manifest.json
php artisan queue:restart
php artisan up
maintenance=0

printf 'Janan deploy finished. Verify /up and the storefront before announcing release.\n'
