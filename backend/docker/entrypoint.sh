#!/usr/bin/env bash
set -e
cd /app

# Persist generated files on a Render disk, if one is attached (paid plans only).
if [ -d /data ]; then
  mkdir -p /data/reports
  rm -rf storage/app/reports
  ln -s /data/reports storage/app/reports
fi

# SQLite fallback: make sure the DB file exists (only used if DB_CONNECTION=sqlite).
if [ "${DB_CONNECTION}" = "sqlite" ] && [ -n "${DB_DATABASE}" ]; then
  mkdir -p "$(dirname "${DB_DATABASE}")"
  touch "${DB_DATABASE}"
fi

php artisan config:cache
php artisan route:cache
php artisan migrate --force

# One-time seeding: set RUN_SEED=true in Render, deploy once, then remove it.
if [ "${RUN_SEED}" = "true" ]; then
  php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
