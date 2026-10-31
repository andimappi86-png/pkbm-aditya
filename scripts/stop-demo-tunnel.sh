#!/usr/bin/env bash
set -euo pipefail

PORT=8080

echo "[1/2] Menghentikan cloudflared tunnel..."
pkill -f "cloudflared tunnel" || echo "cloudflared tidak sedang berjalan."

echo "[2/2] Menghentikan server Laravel demo (port $PORT)..."
if ss -tulpn | grep -q ":$PORT "; then
    PIDS=$(lsof -ti :"$PORT" || fuser "$PORT"/tcp 2>/dev/null || true)
    if [[ -n "$PIDS" ]]; then
        kill -9 $PIDS 2>/dev/null || true
        echo "Server demo di port $PORT dihentikan."
    fi
else
    echo "Tidak ada server pada port $PORT."
fi

echo "Semua service demo telah dihentikan dengan aman."
