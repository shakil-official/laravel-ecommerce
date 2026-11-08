#!/bin/sh
# -----------------------------------------------------------------------------
# Container bootstrap for the Laravel app.
#   1. create .env from .env.docker if missing
#   2. composer install if vendor/ is missing
#   3. generate APP_KEY if empty
#   4. wait for MySQL, run migrations
#   5. seed demo data once (AUTO_SEED=true)
#   6. create the public/storage symlink and fix permissions
# Only runs the bootstrap when the container is started as php-fpm.
# -----------------------------------------------------------------------------
set -e

cd /var/www/html

if [ "$1" = "php-fpm" ]; then

    if [ ! -f .env ]; then
        echo "[entrypoint] .env not found - copying .env.docker"
        cp .env.docker .env
    fi

    if [ ! -f vendor/autoload.php ]; then
        echo "[entrypoint] vendor/ missing - running composer install (first start takes a few minutes)"
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi

    if ! grep -qE '^APP_KEY=base64:' .env; then
        echo "[entrypoint] generating application key"
        php artisan key:generate --force
    fi

    echo "[entrypoint] waiting for database ${DB_HOST:-mysql}:${DB_PORT:-3306} ..."
    tries=0
    until php artisan migrate --force --no-interaction; do
        tries=$((tries + 1))
        if [ "$tries" -ge 30 ]; then
            echo "[entrypoint] database is not reachable - giving up"
            exit 1
        fi
        echo "[entrypoint] database not ready yet (attempt $tries/30), retrying in 3s"
        sleep 3
    done

    if [ "${AUTO_SEED:-true}" = "true" ]; then
        php artisan db:seed --force --no-interaction
    fi

    php artisan storage:link --force >/dev/null 2>&1 || true
    php artisan config:clear >/dev/null 2>&1 || true
    php artisan view:clear >/dev/null 2>&1 || true

    mkdir -p storage/app/public/products storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R ug+rwX,o+rX storage bootstrap/cache || true
    chown -R www-data:www-data storage bootstrap/cache || true

    echo "[entrypoint] ready"
fi

exec "$@"
