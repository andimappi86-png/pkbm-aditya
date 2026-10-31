#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/media/Data D/pkbm aditya"
cd "$PROJECT_DIR"

PORT=8080
LOG_FILE="/tmp/cloudflared.log"

echo "[1/3] Memeriksa / Menjalankan server demo Laravel (port $PORT)..."
if ss -tulpn | grep -q ":$PORT "; then
    echo "Server demo sudah berjalan pada port $PORT."
else
    APP_ENV=demo php artisan serve --env=demo --port="$PORT" >/dev/null 2>&1 &
    sleep 2
    echo "Server demo aktif pada PID $!."
fi

echo "[2/3] Memulai Cloudflare Tunnel..."
rm -f "$LOG_FILE"
cloudflared tunnel --url "http://127.0.0.1:$PORT" --logfile "$LOG_FILE" >/dev/null 2>&1 &
CF_PID=$!
echo "cloudflared berjalan pada PID $CF_PID."

echo "[3/3] Menunggu URL publik dari Cloudflare..."
for _ in {1..15}; do
    if grep -q "trycloudflare.com" "$LOG_FILE" 2>/dev/null; then
        TUNNEL_URL=$(grep -o 'https://[-a-zA-Z0-9.]*\.trycloudflare\.com' "$LOG_FILE" | head -n 1)
        echo ""
        echo "=========================================================="
        echo " Website Demo PKBM Aditya Aktif:"
        echo " URL Publik: $TUNNEL_URL"
        echo "=========================================================="
        exit 0
    fi
    sleep 1
done

echo "Gagal mendapatkan URL publik dalam 15 detik. Periksa $LOG_FILE."
exit 1
