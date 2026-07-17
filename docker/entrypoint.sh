#!/bin/sh

echo "Waiting for database connection to ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}..."
# Tunggu sampai database siap menerima koneksi (membaca host & port secara dinamis dari env)
until nc -z -v -w30 "${DB_HOST:-127.0.0.1}" "${DB_PORT:-3306}"
do
  echo "Database is not ready yet. Retrying in 2 seconds..."
  sleep 2
done

echo "Database is up! Running migrations..."
php artisan migrate --force

# Check if users table is empty before seeding
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | grep -E '^[0-9]+$')

if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" -eq 0 ]; then
  echo "Users table is empty. Running seeders for initial setup..."
  php artisan db:seed --force
else
  echo "Users table already has data. Skipping seeders to prevent data loss."
fi

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
