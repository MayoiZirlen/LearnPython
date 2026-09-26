#!/usr/bin/env bash
# Revisa que ninguna página muestre avisos de PHP (Warning, Notice, Deprecated, Fatal error).
#
# Levanta un servidor de prueba con los errores visibles (como XAMPP) y recorre el sitio completo:
#   bash tools/check_pages.sh [usuario] [contraseña]
# Si das usuario y contraseña, también revisa las páginas con sesión iniciada.
# Requiere MySQL/MariaDB encendido con la base de datos importada.
cd "$(dirname "$0")/.." || exit 1
PUERTO=8099
B="http://127.0.0.1:$PUERTO"
COOKIES=$(mktemp)
php -d display_errors=1 -d error_reporting=-1 -S "127.0.0.1:$PUERTO" -t . >/dev/null 2>&1 &
SERVIDOR=$!
trap 'kill $SERVIDOR 2>/dev/null; rm -f "$COOKIES"' EXIT
sleep 1

paginas=$(php -r 'define("ROOT", "."); require "includes/course.php"; foreach (lesson_order() as $s) echo "lesson.php?l=$s\n";'
          printf "index.php\nlearn.php\nplayground.php\nranking.php\nlogin.php\nregister.php\n")
total_fallos=0

revisar() {
    local fallos=0 n=0
    for p in $paginas "$@"; do
        n=$((n + 1))
        out=$(curl -s -L -b "$COOKIES" -c "$COOKIES" "$B/$p")
        if echo "$out" | grep -qiE "(warning|notice|deprecated|fatal error|parse error)</b>:"; then
            fallos=$((fallos + 1))
            echo "❌ $p: $(echo "$out" | grep -iE -m1 "warning|notice|deprecated|fatal" | sed 's/<[^>]*>//g' | cut -c1-160)"
        fi
    done
    echo "   $n páginas revisadas, $fallos con avisos"
    total_fallos=$((total_fallos + fallos))
}

echo "👤 Como invitado:"
revisar
if [ -n "$1" ]; then
    tok=$(curl -s -c "$COOKIES" -b "$COOKIES" "$B/login.php" | grep -o 'name="csrf" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
    curl -s -c "$COOKIES" -b "$COOKIES" -o /dev/null -X POST "$B/login.php" \
        --data-urlencode "csrf=$tok" --data-urlencode "login=$1" --data-urlencode "password=$2"
    echo "🔑 Con sesión de $1:"
    revisar profile.php
fi
[ "$total_fallos" -eq 0 ] && echo "✅ Sin avisos de PHP" || { echo "❌ $total_fallos páginas con avisos"; exit 1; }
