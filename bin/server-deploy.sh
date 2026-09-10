#!/usr/bin/env bash
# Manual deploy to dev or prod, run over the existing SSH tunnel — explicit
# env+branch arguments, so a copy-pasted command can't silently target the
# wrong server or deploy the wrong branch. See
# efrumos-docs/manual-deploy-runbook.md for the reasoning/background.
#
# The server never talks to GitHub itself — no deploy key, no server-side
# git clone (tried a GitHub deploy key first: `gh repo deploy-key add`
# 404'd, this account isn't a repo admin). Code goes over the same SSH
# tunnel this script already uses for everything else: rsync of whatever
# branch is checked out HERE, in this local working tree.
#
# Usage: bin/server-deploy.sh <dev|prod> <branch> [--build-assets]
#
#   dev|prod        required, no default — picks SSH user/host/container.
#   branch          required, no default — must match the branch actually
#                    checked out locally (checked below); this is a
#                    safety label, not something that triggers a checkout.
#   --build-assets  also runs `npm run build` locally and uploads public/build
#                    (the server has no node/npm — see manual-deploy-runbook.md).
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

ENVIRONMENT="${1:-}"
BRANCH="${2:-}"
BUILD_ASSETS=0
shift 2 2>/dev/null || true
for arg in "$@"; do
  [ "$arg" = "--build-assets" ] && BUILD_ASSETS=1
done

if [ "$ENVIRONMENT" != "dev" ] && [ "$ENVIRONMENT" != "prod" ]; then
  echo "Usage: $0 <dev|prod> <branch> [--build-assets]" >&2
  echo "  (no default on purpose — this must be explicit)" >&2
  exit 1
fi

if [ -z "$BRANCH" ]; then
  echo "Usage: $0 <dev|prod> <branch> [--build-assets]" >&2
  echo "  branch is required — no default (used to silently deploy main)." >&2
  exit 1
fi

CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
  echo "!! Requested branch '$BRANCH' but the local checkout is on '$CURRENT_BRANCH'." >&2
  echo "!! This script deploys whatever is checked out here — git checkout '$BRANCH' first." >&2
  exit 1
fi

# dev is meant to always track stage, not a random feature branch someone
# happened to have checked out — a plain typo/mistake here would silently
# ship the wrong thing to what people expect to be "the stage environment".
if [ "$ENVIRONMENT" = "dev" ] && [ "$BRANCH" != "stage" ]; then
  echo "!! dev is meant to track 'stage', not '$BRANCH'."
  read -r -p "Type '$BRANCH' again to confirm you really want dev on a non-stage branch: " BRANCH_CONFIRM
  if [ "$BRANCH_CONFIRM" != "$BRANCH" ]; then
    echo "Aborted — confirmation didn't match." >&2
    exit 1
  fi
fi

# Override with DEPLOY_SSH_KEY=/path/to/key if your key isn't at the
# default location (e.g. a different machine/account than the original
# deploy setup).
SSH_KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/external}"

if [ ! -f "$SSH_KEY" ]; then
  echo "!! SSH key not found at $SSH_KEY" >&2
  echo "!! Set DEPLOY_SSH_KEY=/path/to/your/key if it's somewhere else." >&2
  exit 1
fi

if [ "$ENVIRONMENT" = "dev" ]; then
  SSH_TARGET="dev_efrumos_md@efrumos.md"
  CONTAINER="php8.2-dev_efrumos_md"
  DOMAIN="dev.efrumos.md"
else
  SSH_TARGET="hosting_efrumos_md@efrumos.md"
  CONTAINER="php8.2-hosting_efrumos_md"
  DOMAIN="www.efrumos.md"
fi

echo "==> Target: $ENVIRONMENT ($SSH_TARGET, container $CONTAINER, $DOMAIN), branch $BRANCH"
read -r -p "Type '$ENVIRONMENT' again to confirm: " CONFIRM
if [ "$CONFIRM" != "$ENVIRONMENT" ]; then
  echo "Aborted — confirmation didn't match." >&2
  exit 1
fi

# BatchMode=yes: never fall back to an interactive password prompt (there's
# no one to type it in a script) — fail fast and loud instead if the key
# doesn't work, rather than hanging.
#
# ControlMaster=no / ControlPath=none: this host resets multiplexed master
# connections ("mux_client_request_session: read from master failed:
# Connection reset by peer" / "Failed to connect to new control master"),
# and a stale ~/.ssh/sockets/* then breaks every deploy. Force plain,
# independent connections. Combined with the single-session deploy below,
# the whole run is exactly ONE ssh connection — this host's connection-rate
# limiting denies a second key auth made seconds after a successful one.
SSH_OPTS=(-i "$SSH_KEY" -o BatchMode=yes -o ControlMaster=no -o ControlPath=none)
ssh_do() { ssh "${SSH_OPTS[@]}" "$SSH_TARGET" "$@"; }

if [ "$BUILD_ASSETS" = "1" ]; then
  echo "==> Building frontend assets locally (server has no node/npm)"
  npm ci
  npm run build
fi

# public/upfiles — real uploaded files (products/shops/shade photos), NOT
# the static git snapshot of the same path (38303 files, committed for
# local dev). A plain rsync (no --delete, but no exclude either) would
# silently overwrite any real file whose name collides with the older
# git snapshot. vendor/node_modules/storage — regenerated or already
# server-local, never overwrite from the local machine's copy.
# Only prod skips dev dependencies. dev is meant for actually poking at
# things (debugbar, ide-helper, faker) — --no-dev there just means anyone
# who flips DEBUGBAR_ENABLED=true (as happened the first time we deployed
# here) gets "Class ... not found" instead of a working toolbar.
if [ "$ENVIRONMENT" = "prod" ]; then
  COMPOSER_FLAGS="--no-dev --optimize-autoloader"
else
  COMPOSER_FLAGS="--optimize-autoloader"
fi

# public/build — normally regenerated server-side... except the server has
# no node/npm, so with --build-assets we ship the local build in the same
# tarball (drop it from the exclude list below).
BUILD_EXCLUDE=(--exclude='public/build')
[ "$BUILD_ASSETS" = "1" ] && BUILD_EXCLUDE=()

# ONE ssh connection for the whole deploy: the code tarball is streamed on
# stdin and the remote script starts by extracting it (`tar xzf -`), then
# runs composer/migrate/patches/caches/restart in the same session.
#
# Why one connection and not one-per-step (or even two): this host has
# aggressive connection-rate limiting — a second key auth seconds after a
# successful one gets "Permission denied (publickey,password)" even though
# the key is fine (seen repeatedly). ControlMaster multiplexing isn't a way
# around it either: this host resets the mux master and a stale
# ~/.ssh/sockets/* then wedges every later deploy.
#
# tar over ssh, not rsync — rsync isn't on every machine this runs from
# (plain Git-Bash/Windows has ssh/tar but no rsync). One-directional, not
# incremental, so tar is enough. Exclusions mirror the old rsync ones.
#
# 2026-09-10 incident: docker-compose.yml was NOT excluded on an earlier
# version — it overwrote the server's real one (managing
# php8.2-{dev,hosting}_efrumos_md) with this repo's local dev compose file;
# something reconciled against it and deleted the dev container. Never sync
# compose files or the Dockerfile — server-side container lifecycle is by
# hand, same as .env.
echo "==> Deploying $BRANCH to $ENVIRONMENT in one SSH session (sync + composer + migrations + patches + caches + restart)"
tar czf - \
  --exclude='.git' --exclude='.env' --exclude='mariadb.info' --exclude='php-conf.d' \
  --exclude='public/upfiles' "${BUILD_EXCLUDE[@]}" --exclude='vendor' \
  --exclude='node_modules' --exclude='storage/logs' --exclude='storage/framework' \
  --exclude='docker-compose*.yml' --exclude='Dockerfile' --exclude='docker' \
  --exclude='bootstrap/cache' \
  . | ssh "${SSH_OPTS[@]}" "$SSH_TARGET" "$(cat <<REMOTE_SCRIPT
set -euo pipefail
cd ~

echo "-- Extracting synced code"
tar xzf -

# Laravel needs these to exist even though git doesn't track them (runtime
# artifacts) — on a server that never had them (fresh dev, this session)
# they're just missing entirely, not "old and excluded from overwrite".
mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing bootstrap/cache

# .env at 640 (owner+group read), not 600: php-fpm's pool runs workers as
# www-data (group), not the deploying user — 600 makes dotenv's safeLoad()
# silently fail to read it (no exception, just no config at all) for every
# real web request while \`docker exec ... php artisan\` (running as root)
# keeps working fine and hides the problem. Found the hard way on dev.
chmod 640 .env 2>/dev/null || true

if [ ! -f .env ]; then
  echo "!! No .env on $ENVIRONMENT yet — deploy stopped here on purpose." >&2
  echo "!! Create it by hand first (see manual-deploy-runbook.md step 3), then re-run this script." >&2
  exit 1
fi

echo "-- composer install (host PHP/composer — richer extension set than the fpm container, see deploy-investigation.md)"
composer install $COMPOSER_FLAGS

echo "-- Running migrations"
php artisan migrate --force

# database/sql/*.sql — идемпотентные патчи схемы и данных, которые не входят
# в artisan migrate (структура сайта живёт в дампе). Локально их накатывает
# bin/local-up.sh; на сервере нет ни compose, ни mysql-клиента, поэтому здесь
# та же работа делается artisan-командой через подключение приложения.
# Без этого шага на сервере не появятся goods_shop_rests, shade_img,
# store_guid, orders.pickup_shop_id, координаты магазинов и раздел CMS
# «Палитра оттенков» — карточка товара и самовывоз упадут.
echo "-- Applying database/sql patches"
php artisan db:apply-sql-patches

# Кеши, которые строятся командами по расписанию: на свежем сервере они пусты
# до первого запуска scheduler'а, и до тех пор блок «С этим товаром покупают»
# теряет источник «часто покупают вместе», а витрина вариантов оттенков пуста.
# Прогреваем сразу, чтобы деплой не оставлял сайт в частично рабочем виде.
# Регулярность обеспечивает cron (см. напоминание в конце скрипта).
echo "-- Warming recommendation/variant caches"
php artisan recommendations:recalc-bought-together
php artisan shades:rebuild-variants

echo "-- Fixing storage/bootstrap permissions"
# Best-effort: runtime files created by php-fpm's www-data (logs, compiled
# views, sessions) can't be chown'd by the deploying user and don't need to
# be — they're already group-writable. Never let this abort the deploy
# before the restart below (set -e would otherwise kill it here).
chown -R \$(whoami):www-data storage bootstrap/cache 2>/dev/null || true

echo "-- Restarting $CONTAINER to pick up the new code"
docker restart $CONTAINER

echo "-- Post-deploy checks"
php artisan migrate:status 2>&1 | tail -n 5 || true
curl -sS -o /dev/null -w '   HTTP %{http_code} https://$DOMAIN/\n' "https://$DOMAIN/" || true
REMOTE_SCRIPT
)"

echo
echo "Done. Deployed to $ENVIRONMENT ($DOMAIN)."
[ "$BUILD_ASSETS" = "0" ] && echo "Note: ran without --build-assets — frontend assets were NOT rebuilt/uploaded."

# Разовые серверные настройки, которые этот скрипт не делает и делать не должен
# (они переживают деплой). Без первого пункта кеши протухнут через сутки,
# без второго очередь не разбирается и заказы не уходят в 1С/Bitrix24.
cat <<'REMINDER'

Проверьте один раз на сервере (deploy этого не настраивает):
  1) cron:   * * * * * cd ~ && php artisan schedule:run >> /dev/null 2>&1
  2) очередь: QUEUE_CONNECTION в .env + запущенный воркер (php artisan queue:work),
     иначе SubmitOrderToIntegrationLayerJob выполняется внутри запроса оформления.
REMINDER
