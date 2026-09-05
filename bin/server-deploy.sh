#!/usr/bin/env bash
# Manual deploy to dev or prod — one script, explicit env argument, so a
# copy-pasted command can't silently target the wrong server. See
# efrumos-docs/manual-deploy-runbook.md for the reasoning/background.
#
# Usage: bin/server-deploy.sh <dev|prod> [--build-assets]
#
#   dev|prod        required, no default — picks SSH user/host/container.
#   --build-assets  also runs `npm run build` locally and uploads public/build
#                    (the server has no node/npm — see manual-deploy-runbook.md).
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

ENVIRONMENT="${1:-}"
BUILD_ASSETS=0
[ "${2:-}" = "--build-assets" ] && BUILD_ASSETS=1

if [ "$ENVIRONMENT" != "dev" ] && [ "$ENVIRONMENT" != "prod" ]; then
  echo "Usage: $0 <dev|prod> [--build-assets]" >&2
  echo "  (no default on purpose — this must be explicit)" >&2
  exit 1
fi

SSH_KEY="$HOME/.ssh/external"
REPO="git@github.com:Labs01k/efrumos.git"

if [ "$ENVIRONMENT" = "dev" ]; then
  SSH_TARGET="dev_efrumos_md@efrumos.md"
  CONTAINER="php8.2-dev_efrumos_md"
  DOMAIN="dev.efrumos.md"
else
  SSH_TARGET="hosting_efrumos_md@efrumos.md"
  CONTAINER="php8.2-hosting_efrumos_md"
  DOMAIN="www.efrumos.md"
fi

echo "==> Target: $ENVIRONMENT ($SSH_TARGET, container $CONTAINER, $DOMAIN)"
read -r -p "Type '$ENVIRONMENT' again to confirm: " CONFIRM
if [ "$CONFIRM" != "$ENVIRONMENT" ]; then
  echo "Aborted — confirmation didn't match." >&2
  exit 1
fi

ssh_do() { ssh -i "$SSH_KEY" "$SSH_TARGET" "$@"; }

if [ "$BUILD_ASSETS" = "1" ]; then
  echo "==> Building frontend assets locally (server has no node/npm)"
  npm ci
  npm run build
  echo "==> Uploading public/build to $ENVIRONMENT"
  rsync -az -e "ssh -i $SSH_KEY" public/build/ "$SSH_TARGET:~/public/build/"
fi

echo "==> Cloning current code to a temp dir on $ENVIRONMENT and syncing it into ~"
ssh_do "rm -rf /tmp/efrumos-deploy && git clone --depth 1 $REPO /tmp/efrumos-deploy"
ssh_do "rsync -a --exclude='.git' --exclude='.env' --exclude='mariadb.info' --exclude='php-conf.d' /tmp/efrumos-deploy/ ~/ && rm -rf /tmp/efrumos-deploy"

echo "==> composer install (host PHP/composer — richer extension set than the fpm container, see deploy-investigation.md)"
ssh_do "cd ~ && composer install --no-dev --optimize-autoloader"

if ! ssh_do "test -f ~/.env"; then
  echo "!! No .env on $ENVIRONMENT yet — deploy stopped here on purpose." >&2
  echo "!! Create it by hand first (see manual-deploy-runbook.md step 3), then re-run this script." >&2
  exit 1
fi

echo "==> Running migrations"
ssh_do "cd ~ && php artisan migrate --force"

# database/sql/*.sql — идемпотентные патчи схемы и данных, которые не входят
# в artisan migrate (структура сайта живёт в дампе). Локально их накатывает
# bin/local-up.sh; на сервере нет ни compose, ни mysql-клиента, поэтому здесь
# та же работа делается artisan-командой через подключение приложения.
# Без этого шага на сервере не появятся goods_shop_rests, shade_img,
# store_guid, orders.pickup_shop_id, координаты магазинов и раздел CMS
# «Палитра оттенков» — карточка товара и самовывоз упадут.
echo "==> Applying database/sql patches"
ssh_do "cd ~ && php artisan db:apply-sql-patches"

# Кеши, которые строятся командами по расписанию: на свежем сервере они пусты
# до первого запуска scheduler'а, и до тех пор блок «С этим товаром покупают»
# теряет источник «часто покупают вместе», а витрина вариантов оттенков пуста.
# Прогреваем сразу, чтобы деплой не оставлял сайт в частично рабочем виде.
# Регулярность обеспечивает cron (см. напоминание в конце скрипта).
echo "==> Warming recommendation/variant caches"
ssh_do "cd ~ && php artisan recommendations:recalc-bought-together && php artisan shades:rebuild-variants"

echo "==> Fixing storage/bootstrap permissions"
ssh_do "cd ~ && chown -R \$(whoami):www-data storage bootstrap/cache"

echo "==> Restarting $CONTAINER to pick up the new code"
ssh_do "docker restart $CONTAINER"

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
