#!/usr/bin/env bash
# =============================================================
# Korea Auto Export — packages the project into a single
# uploadable .tar.gz for shared hosting (Namecheap, OVH, etc.).
#
#   bin/prepare-deploy.sh           # default: dev .htaccess (HTTP)
#   bin/prepare-deploy.sh --prod    # swap in .htaccess.production
# =============================================================

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PROD_HTACCESS=false
for arg in "$@"; do
    if [[ "$arg" == "--prod" ]]; then
        PROD_HTACCESS=true
    fi
done

STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="/tmp/kae-deploy-${STAMP}"
ARCHIVE="${ROOT}/kae-deploy-${STAMP}.tar.gz"

echo "── Staging in: ${WORK}"
rm -rf "${WORK}"
mkdir -p "${WORK}/korea-auto-export"

# rsync copies everything except dev / runtime / vendor / build dirs.
rsync -a \
    --exclude='.git' \
    --exclude='.idea' \
    --exclude='.vscode' \
    --exclude='.DS_Store' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='composer.lock' \
    --exclude='storage/cache/*' \
    --exclude='storage/logs/*' \
    --exclude='storage/sessions/*' \
    --exclude='storage/backups/*' \
    --exclude='public/uploads/vehicles' \
    --exclude='public/uploads/testimonials' \
    --exclude='kae-deploy-*.tar.gz' \
    --exclude='/tmp/' \
    "${ROOT}/" "${WORK}/korea-auto-export/"

# Restore empty runtime dirs with .gitkeep so the structure is preserved
for d in storage/cache storage/logs storage/sessions storage/backups public/uploads; do
    mkdir -p "${WORK}/korea-auto-export/${d}"
    touch "${WORK}/korea-auto-export/${d}/.gitkeep"
done

# Drop in the production .htaccess if requested
if [[ "$PROD_HTACCESS" == "true" ]]; then
    if [[ -f "${WORK}/korea-auto-export/public/.htaccess.production" ]]; then
        cp "${WORK}/korea-auto-export/public/.htaccess.production" "${WORK}/korea-auto-export/public/.htaccess"
        echo "── Swapped in public/.htaccess.production"
    else
        echo "!! .htaccess.production not found, keeping dev .htaccess"
    fi
fi

# A small README at the root of the archive
cat > "${WORK}/korea-auto-export/DEPLOY_README.txt" <<EOF
Korea Auto Export — production drop-in
Generated ${STAMP}

Steps on the server:
1. Upload + extract this archive so the project sits ABOVE your domain's web root.
   On Namecheap that's usually one of:
     ~/koreaautoexport/        (if main domain or subdomain points at ~/koreaautoexport/public)
     ~/public_html/...         (if you must put it under public_html — read the guide)

2. Copy .env.example -> .env and edit DB_*, APP_URL, MAIL_*, SESSION_SECURE=true.

3. Generate APP_KEY:
     php bin/keygen.php --write

4. Run the installer:
     php bin/install.php          (omit --with-demo on production unless you want sample data)

5. Set permissions:
     chmod -R 0775 storage public/uploads
     chmod 0600 .env

6. Install daily backup cron in cPanel ("Cron Jobs"):
     0 3 * * * cd ~/koreaautoexport && /usr/bin/php bin/backup.php >> storage/logs/backup.log 2>&1

7. Enable AutoSSL in cPanel for HTTPS.
8. Once SSL is stable, swap the dev .htaccess with the production one:
     cp public/.htaccess.production public/.htaccess
EOF

# Pack
echo "── Packing ${ARCHIVE}"
tar -C "${WORK}" -czf "${ARCHIVE}" korea-auto-export
SIZE=$(du -h "${ARCHIVE}" | cut -f1)

# Clean stage
rm -rf "${WORK}"

echo
echo "✓ Deploy archive ready"
echo "  ${ARCHIVE}  (${SIZE})"
echo
echo "Upload this file to your Namecheap account via cPanel File Manager"
echo "or scp, then extract it on the server. See docs/10-namecheap-deploy.md"
echo "for the full step-by-step."
