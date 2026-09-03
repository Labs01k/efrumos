#!/usr/bin/env bash
# Bring up the local Docker dev stack (Windows/WSL2-safe: vendor/ and
# node_modules/ live in named volumes, not the Windows bind mount — see
# docker-compose.yml comments and efrumos-docs/deploy-investigation.md).
#
# Usage: bin/local-up.sh
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [ ! -f .env ]; then
  echo "==> No .env yet, copying .env.docker.example"
  cp .env.docker.example .env
fi

echo "==> Starting containers (db/redis/mailhog/app/nginx/node/queue/scheduler)"
docker compose --profile local-db up -d db redis mailhog app nginx node queue scheduler

# Named volumes start empty/root-owned on first creation — the app container
# runs as uid 1000 (see Dockerfile), so composer/npm can't write into a
# fresh volume until ownership is fixed once.
echo "==> Fixing vendor/node_modules volume ownership (idempotent, harmless if already correct)"
docker compose exec -T -u root app chown -R app:app /var/www/html/vendor /var/www/html/node_modules 2>/dev/null || true

if ! docker compose exec -T app test -f vendor/autoload.php 2>/dev/null; then
  echo "==> vendor/ is empty, running composer install"
  docker compose exec -T app composer install
fi

if ! docker compose exec -T app test -f .env 2>/dev/null; then
  : # .env is bind-mounted from the host, always present once copied above
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "==> No APP_KEY yet, generating one"
  docker compose exec -T app php artisan key:generate
fi

echo "==> Restarting queue/scheduler (they crash-loop until vendor/ exists)"
docker compose restart queue scheduler

# database/sql/*.sql — raw idempotent schema/data patches (per
# backend-handoff.md: "выполнить на проде при деплое — все идемпотентны").
# Not part of artisan migrate; applying them here so a fresh local DB
# doesn't silently miss real data (e.g. shop coordinates) that features
# depend on.
if compgen -G "database/sql/*.sql" > /dev/null; then
  echo "==> Applying database/sql/*.sql (idempotent, safe to re-run)"
  for f in database/sql/*.sql; do
    echo "    $f"
    docker compose exec -T db mysql -uroot -proot "${DB_DATABASE:-efrumos}" < "$f"
  done
fi

echo
echo "Done. Site:    http://localhost:${APP_PORT:-8080}"
echo "      Mailhog: http://localhost:8025"
echo "      Vite:    http://localhost:5173"
echo
echo "If this is the very first run, still needed by hand:"
echo "  - drop a DB dump into docker/mysql-init/ then 'docker compose down -v && bin/local-up.sh' to import it"
echo "  - docker compose exec app php artisan migrate"
