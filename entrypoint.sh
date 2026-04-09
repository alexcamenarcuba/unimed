#!/bin/bash
set -e

cd /var/www

# Garante sincronismo de dependências PHP com composer.lock
echo "Installing Composer dependencies..."
composer install --no-interaction

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
    echo "Installing Node dependencies..."
    npm install
fi

# Inicia Vite em background
echo "Starting Vite dev server..."
npm run dev > /tmp/vite.log 2>&1 &
VITE_PID=$!

# Aguarda o Vite estar pronto
echo "Waiting for Vite to start..."
for i in {1..30}; do
    if grep -q "ready in" /tmp/vite.log 2>/dev/null; then
        echo "Vite is ready!"
        break
    fi
    sleep 1
done

# Inicia Laravel
echo "Starting Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000

# Cleanup
kill $VITE_PID 2>/dev/null || true
wait $VITE_PID 2>/dev/null || true