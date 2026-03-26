#!/bin/bash
set -e

echo "==> [Scheduler] Waiting for MySQL..."
until php -r "new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');" &>/dev/null; do
    sleep 2
done
echo "==> [Scheduler] MySQL is ready."

echo "==> [Scheduler] Waiting for app container to finish booting (15s)..."
sleep 15

echo "==> [Scheduler] Starting Laravel Scheduler loop..."
while true; do
    php artisan schedule:run --no-interaction >> /dev/null 2>&1
    sleep 60
done
