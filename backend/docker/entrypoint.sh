#!/usr/bin/env bash
set -e
cd /app

# Persist generated files (and the SQLite fallback DB) on the Render disk.
if [ -d /data ]; then
  mkdir -p /data/reports
  rm -rf storage/app/reports
  ln -s /data/reports storage/app/reports
fi

# SQLite fallback: make sure the DB file exists.
if [ "${DB_CONNECTION}" = "sqlite" ] && [ -n "${DB_DATABASE}" ]; then
  mkdir -p "$(dirname "${DB_DATABASE}")"
  touch "${DB_DATABASE}"
fi

php artisan config:cache
php artisan route:cache
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
