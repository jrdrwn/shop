#!/usr/bin/env bash
# ==============================================================================
# Instant cPanel Update Script (Laravel + Filament)
# ==============================================================================
# Cara pakai langsung dari Terminal cPanel (di dalam folder aplikasi Laravel):
#
# 1) Repo Publik (GitHub):
#    curl -sSL https://raw.githubusercontent.com/jrdrwn/shop/main/scripts/cpanel-update.sh | bash
#
# 2) Repo Privat (menggunakan GitHub Personal Access Token):
#    curl -sSL -H "Authorization: token GH_TOKEN_ANDA" \
#      https://raw.githubusercontent.com/jrdrwn/shop/main/scripts/cpanel-update.sh | GITHUB_TOKEN="GH_TOKEN_ANDA" bash
#
# 3) Custom folder / branch / repo lain:
#    curl -sSL https://raw.githubusercontent.com/jrdrwn/shop/main/scripts/cpanel-update.sh | \
#      APP_DIR="$HOME/public_html" REPO="jrdrwn/shop" BRANCH="main" bash
# ==============================================================================

set -euo pipefail

REPO="${REPO:-jrdrwn/shop}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-$(pwd)}"
GITHUB_TOKEN="${GITHUB_TOKEN:-}"
RUN_COMPOSER="${RUN_COMPOSER:-auto}"
ARCHIVE_URL="${ARCHIVE_URL:-https://github.com/${REPO}/archive/refs/heads/${BRANCH}.tar.gz}"

echo "============================================================"
echo "  INSTANT UPDATE WEB TOKO (cPanel)"
echo "  Repo   : ${REPO} (branch: ${BRANCH})"
echo "  Folder : ${APP_DIR}"
echo "============================================================"

if [ ! -f "${APP_DIR}/artisan" ]; then
    echo "❌ ERROR: File 'artisan' tidak ditemukan di: ${APP_DIR}"
    echo "   Pastikan Anda berada di folder root Laravel atau set APP_DIR, contoh:"
    echo "   APP_DIR=\$HOME/nama-folder-toko bash ..."
    exit 1
fi

cd "${APP_DIR}"

# Deteksi binary PHP yang sesuai di cPanel (prioritas PHP 8.4 -> 8.3 -> default)
PHP_BIN="${PHP_BIN:-}"
if [ -z "${PHP_BIN}" ]; then
    for candidate in /usr/local/bin/ea-php84 /usr/local/bin/ea-php83 /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php "$(command -v php || true)"; do
        if [ -n "${candidate}" ] && [ -x "${candidate}" ]; then
            PHP_BIN="${candidate}"
            break
        fi
    done
fi

if [ -z "${PHP_BIN}" ]; then
    echo "❌ ERROR: Binary PHP tidak ditemukan."
    exit 1
fi

echo "▶ Menggunakan PHP: ${PHP_BIN} ($("${PHP_BIN}" -r 'echo PHP_VERSION;'))"

# Pastikan aplikasi kembali ONLINE meski terjadi error di tengah proses
cleanup() {
    "${PHP_BIN}" artisan up >/dev/null 2>&1 || true
    if [ -n "${TMP_DIR:-}" ] && [ -d "${TMP_DIR}" ]; then
        rm -rf "${TMP_DIR}"
    fi
}
trap cleanup EXIT

echo "▶ Mengaktifkan mode maintenance sementara..."
"${PHP_BIN}" artisan down --retry=60 >/dev/null 2>&1 || true

LOCK_HASH_BEFORE=""
if [ -f "composer.lock" ]; then
    LOCK_HASH_BEFORE="$(md5sum composer.lock 2>/dev/null | awk '{print $1}' || true)"
fi

# Mode 1: Jika folder sudah terhubung ke Git
if [ -d ".git" ] && command -v git >/dev/null 2>&1; then
    echo "▶ Mendeteksi repositori Git — menarik update terbaru dari branch '${BRANCH}'..."
    git fetch origin "${BRANCH}"
    git reset --hard "origin/${BRANCH}"
else
    # Mode 2: Tanpa .git (instalasi via zip / File Manager) -> download archive langsung
    echo "▶ Folder bukan Git repo — mengunduh arsip update dari: ${ARCHIVE_URL}"
    TMP_DIR="$(mktemp -d)"
    ARCHIVE_FILE="${TMP_DIR}/update.tar.gz"

    if [ -n "${GITHUB_TOKEN}" ]; then
        curl -fSL -H "Authorization: token ${GITHUB_TOKEN}" "${ARCHIVE_URL}" -o "${ARCHIVE_FILE}"
    else
        curl -fSL "${ARCHIVE_URL}" -o "${ARCHIVE_FILE}"
    fi

    echo "▶ Mengekstrak file update..."
    tar -xzf "${ARCHIVE_FILE}" -C "${TMP_DIR}"
    EXTRACTED_DIR="$(find "${TMP_DIR}" -mindepth 1 -maxdepth 1 -type d | head -n 1)"

    if [ -z "${EXTRACTED_DIR}" ] || [ ! -f "${EXTRACTED_DIR}/artisan" ]; then
        echo "❌ ERROR: Arsip update tidak valid atau tidak berisi project Laravel."
        exit 1
    fi

    echo "▶ Menyinkronkan file (melindungi .env, storage/, vendor/, dan .htaccess)..."
    if command -v rsync >/dev/null 2>&1; then
        rsync -a \
            --exclude='.env' \
            --exclude='.env.*' \
            --exclude='.htaccess' \
            --exclude='public/.htaccess' \
            --exclude='storage/' \
            --exclude='vendor/' \
            --exclude='node_modules/' \
            --exclude='public/storage' \
            --exclude='public/build' \
            --exclude='.git/' \
            "${EXTRACTED_DIR}/" "${APP_DIR}/"
    else
        # Fallback jika rsync tidak tersedia di cPanel
        for item in app bootstrap config database public resources routes scripts composer.json composer.lock; do
            if [ -e "${EXTRACTED_DIR}/${item}" ]; then
                if [ "${item}" = "public" ]; then
                    mkdir -p "${APP_DIR}/public"
                    cp -rf "${EXTRACTED_DIR}/public/js" "${APP_DIR}/public/" 2>/dev/null || true
                    cp -rf "${EXTRACTED_DIR}/public/css" "${APP_DIR}/public/" 2>/dev/null || true
                else
                    cp -rf "${EXTRACTED_DIR}/${item}" "${APP_DIR}/"
                fi
            fi
        done
    fi
fi

# Cek apakah perlu jalankan composer install
LOCK_HASH_AFTER=""
if [ -f "composer.lock" ]; then
    LOCK_HASH_AFTER="$(md5sum composer.lock 2>/dev/null | awk '{print $1}' || true)"
fi

COMPOSER_BIN="$(command -v composer || true)"
if [ -n "${COMPOSER_BIN}" ]; then
    if [ "${RUN_COMPOSER}" = "1" ] || { [ "${RUN_COMPOSER}" = "auto" ] && [ "${LOCK_HASH_BEFORE}" != "${LOCK_HASH_AFTER}" ]; }; then
        echo "▶ Menjalankan composer install..."
        "${PHP_BIN}" "${COMPOSER_BIN}" install --no-dev --optimize-autoloader --no-interaction
    fi
fi

echo "▶ Menjalankan migrasi database..."
"${PHP_BIN}" artisan migrate --force --no-interaction

if [ ! -L "${APP_DIR}/public/storage" ] && [ ! -d "${APP_DIR}/public/storage" ]; then
    echo "▶ Membuat storage symlink..."
    "${PHP_BIN}" artisan storage:link || true
fi

echo "▶ Membersihkan & mengoptimalkan cache Laravel + Filament..."
"${PHP_BIN}" artisan optimize:clear
"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache
"${PHP_BIN}" artisan filament:optimize || true

echo "▶ Mengaktifkan kembali aplikasi..."
"${PHP_BIN}" artisan up

echo "============================================================"
echo "  ✅ UPDATE SELESAI! Web toko sudah aktif dengan versi terbaru."
echo "============================================================"
