#!/usr/bin/env sh
set -eu

cd /app

if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    case "${APP_URL:-}" in
        ""|http://localhost|https://localhost)
            export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
            ;;
    esac
fi

case "${APP_URL:-}" in
    https://*)
        export SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
        ;;
esac

mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R a+rw storage bootstrap/cache || true

# Never let a local Mac SQLite path leak into the Railway container.
if [ "${DB_CONNECTION:-}" = "sqlite" ]; then
    case "${DB_DATABASE:-}" in
        ""|/Users/*|database/database.sqlite|./database/database.sqlite)
            export DB_DATABASE="/app/database/database.sqlite"
            ;;
    esac

    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# Railpack may cache config during build before runtime variables are final.
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Running migrations and seeding database ..."
php artisan migrate --force

if [ "${RUN_DATABASE_SEEDER:-true}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-${APP_HTTP_PORT:-80}}"
