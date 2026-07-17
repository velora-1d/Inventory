#!/bin/sh

echo "Waiting for database connection..."
# Tunggu sampai database MySQL siap menerima koneksi (menggunakan port 3306)
until nc -z -v -w30 db 3306
do
  echo "Database is not ready yet. Retrying in 2 seconds..."
  sleep 2
done

echo "Database is up! Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
