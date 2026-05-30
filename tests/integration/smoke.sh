#!/usr/bin/env bash
# Prosty test integracyjny wybranych endpointow.
# Wymaga uruchomionej aplikacji pod http://localhost:8080.

set -e
BASE="${BASE:-http://localhost:8080}"
COOKIES=$(mktemp)

assert_status() {
    local url="$1" expected="$2"
    local code
    code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" "$url")
    if [ "$code" != "$expected" ]; then
        echo "[FAIL] $url -> $code (oczekiwano $expected)"
        exit 1
    fi
    echo "[OK]   $url -> $code"
}

echo "=== smoke test WypozyczalniaPRO ==="

assert_status "$BASE/"          200
assert_status "$BASE/login"     200
assert_status "$BASE/register"  200
assert_status "$BASE/equipment" 200

# 404 - nieistniejacy zasob
assert_status "$BASE/equipment/999999" 404

# 403/302 - panel admina bez logowania (redirect na login)
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" "$BASE/admin/users")
if [ "$code" != "302" ] && [ "$code" != "403" ]; then
    echo "[FAIL] /admin/users bez logowania -> $code"
    exit 1
fi
echo "[OK]   /admin/users bez logowania -> $code"

rm -f "$COOKIES"
echo "=== ALL OK ==="
