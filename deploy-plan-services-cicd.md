# План: единый docker-compose (dev/prod через параметры) + GitHub Actions + деплой `stage`

> **Обновление 2026-09-10 — актуальный план на сейчас, сфокусированный
> только на `stage`→dev + CI, см. раздел «АКТУАЛЬНЫЙ ПЛАН» ниже.** Всё,
> что дальше в этом файле после него (единый план от 04.09 с общим Redis/
> mailhog/queue-контейнерами на dev+prod) — более ранний, более полный
> черновик, специально отложенный вами на обдумывание. Он не отменён, но
> и не выполняется сейчас — актуальный план ниже сознательно уже него
> (без Redis/mailhog, без прода), чтобы получить рабочий CI для stage
> быстрее и с меньшим риском, не блокируясь на более крупном решении.

---

# АКТУАЛЬНЫЙ ПЛАН (2026-09-10): безопасный автодеплой `stage` → dev через GitHub Actions

## Контекст

Запрос сузился до конкретного: план деплоя + CI **для stage** (dev-среда).
Прод explicitly не в рамках этого захода.

Переподключился по SSH и перепроверил всё заново (04.09 → 10.09) — по
факту почти не изменилось, но нашёл 2 реальных бага в уже
эволюционировавшем `bin/server-deploy.sh` (сам скрипт с тех пор доработан
кем-то ещё — появились применение `database/sql`-патчей и прогрев кешей
рекомендаций/вариантов, чего раньше не было — но эти два бага остались):

1. **Клонирует ветку по умолчанию (`main`), не `stage`** — `git clone
   --depth 1 $REPO` без указания ветки. Задеплоить `stage` этим скриптом
   сегодня физически нельзя.
2. **rsync без `--exclude='public/upfiles'`** — на dev сейчас неопасно (там
   вообще ничего нет), но на проде это перезаписало бы совпадающие по имени
   файлы из 1.9GB реальных загруженных фото устаревшим git-снимком
   (`public/upfiles` закоммичен, 38303 файла). Раз скрипт общий для
   dev/prod — чинить нужно сейчас, а не откладывать до захода на прод.

Остальное подтверждено неизменным: dev пуст (только `public/`,
`mariadb.info`, `php-conf.d/`), прод не тронут, оба на одном физическом
хосте, `docker compose` v2.20.2 есть, диск 110GB свободно, crontab пуст,
`QUEUE_CONNECTION=sync` на проде (Redis нигде нет).

**Новое по сравнению со старым планом:** проверил `gh auth status` — токен
уже с правами `repo`, похоже, можно самому завести GitHub Secret
(`gh secret set`) без обращения к вам за ручным шагом в UI.

## Решение по масштабу: без Redis/mailhog/queue-контейнеров в этом заходе

Старый план (ниже в этом файле) предполагал общий Redis + отдельные
`queue`/`scheduler` контейнеры на сервере. Для того, чтобы стейдж вообще
начал автоматически деплоиться и быть проверяемым — это не обязательно:
`QUEUE_CONNECTION=sync` (как сейчас работает прод) заставит интеграционные
джобы (Epic 0/1) выполняться синхронно внутри запроса — не асинхронно, но
и не падать. Это даёт быстрый, low-risk путь к рабочему CI сейчас;
Redis/mailhog остаются отдельной, следующей задачей, если/когда решите
добавить асинхронность на dev — не блокирует и не удаляется из бэклога
(план ниже никуда не делся).

## Часть 1 — доработать `bin/server-deploy.sh`

Файл: `bin/server-deploy.sh`. Изменения:
- Обязательный второй позиционный параметр — ветка: `bin/server-deploy.sh
  <dev|prod> <branch> [--build-assets] [--ci]` (текущий разбор
  `--build-assets` из `$2` сдвинется на `$3`).
- `git clone --depth 1 --branch "$BRANCH" $REPO /tmp/efrumos-deploy` —
  вместо клонирования дефолтной ветки.
- `--exclude='public/upfiles'` добавить в существующую rsync-команду (там
  же, где уже `--exclude='.git' --exclude='.env' --exclude='mariadb.info'
  --exclude='php-conf.d'`).
- `--ci` — пропускает `read -r -p "Type '$ENVIRONMENT' again to confirm"`
  (нужно для неинтерактивного запуска из GitHub Actions).

## Часть 2 — первый ручной прогон на dev (нужен один раз, до CI)

Workflow специально остановится, если на сервере ещё нет `.env` — это уже
заложено в скрипте (`if ! ssh_do "test -f ~/.env"`). Значит первый раз
нужно сделать руками:

1. Создать `.env` на dev вручную по SSH — реальные DB-креды уже видны в
   `~/mariadb.info` (`server=172.17.0.1:3306, database=dev_efrumos_md,
   login=dev_efrumos_md, password=...`), плюс: `APP_ENV=local` (или
   `staging` — не `production`, чтобы не включать боевые ветки кода типа
   продового выбора эндпоинтов 1С), `QUEUE_CONNECTION=sync`,
   `INTEGRATION_MOCK_MODE=true` (без реальных 1С/Bitrix24 креденшлов на
   сервере — иначе честно упадёт на каждом заказе), `AMOCRM_ENABLED=false`
   (не слать тестовые заказы в реальный platon.progression.md),
   `GOOGLE_MAPS_KEY` — новый рабочий ключ, если хотите карту/Places API
   живыми на dev.
2. Прогнать доработанный `bin/server-deploy.sh dev stage` руками (не
   `--ci`, с обычным интерактивным подтверждением) — первый полный прогон:
   клонирование `stage`, `composer install`, `migrate --force` (пустая БД),
   применение `database/sql`-патчей, прогрев кешей рекомендаций/вариантов,
   restart контейнера.
3. **Проверка**: реальные HTTP-запросы на dev-домен (`/ru/`, `/ru/shops`,
   страница товара), `storage/logs/laravel.log` на unexpected
   warning/error, `php artisan migrate:status` — всё применено.

## Часть 3 — GitHub Actions: `.github/workflows/deploy-dev.yml`

Триггер: `push` в `stage`. Шаги:
1. `actions/checkout`.
2. `actions/setup-node` + `npm ci && npm run build` — на раннере (сервер
   без node/npm).
3. SSH-агент (`webfactory/ssh-agent`) с приватным ключом из секрета
   `DEPLOY_SSH_KEY` → `bin/server-deploy.sh dev stage --ci`.
4. `rsync` собранного `public/build/` на dev тем же ключом.
5. Healthcheck: `curl -f https://dev.efrumos.md/ru/` — job краснеет, если
   сайт не отвечает 200.

**Секрет `DEPLOY_SSH_KEY`** — заведу сам через `gh secret set
DEPLOY_SSH_KEY < ~/.ssh/external --repo Labs01k/efrumos` (токен уже
позволяет), не нужно лезть в GitHub UI руками. `known_hosts` —
`ssh-keyscan efrumos.md` прямо в workflow-шаге перед SSH, не обязательно
отдельным секретом.

## Явно вне рамок этого захода

- Прод (`deploy-prod.yml`, workflow_dispatch-триггер, бэкап перед
  деплоем) — план ниже в этом файле это уже описывает подробно; делать
  только по отдельному «давай», после того как dev-автодеплой пройдёт
  проверку несколько раз.
- Redis/mailhog/queue-scheduler контейнеры — не нужны для базового
  рабочего CI, отдельная задача при желании асинхронности на dev.
- Puppet-субдомен, GitHub Environment protection rules — вне моих
  возможностей/кода.

## Проверка

- Реальный push в `stage` → `deploy-dev.yml` триггерится автоматически,
  зелёный статус в Actions.
- Реальные HTTP-запросы на dev-домен после каждого прогона.
- `storage/logs/laravel.log` на сервере — сверить на ошибки от другого PHP
  8.2 (минимальный набор расширений в контейнере — bcmath/intl/zip/opcache
  отсутствуют, уже проверено что наш код их не использует).

---

# БОЛЕЕ РАННИЙ ПЛАН (2026-09-04) — полная инфраструктура, ещё не выполнялся

Черновик для обдумывания, ничего из этого ещё не выполнялось на реальных
серверах. Собран по итогам живой SSH-разведки dev/prod 2026-09-04 (см.
`deploy-investigation.md`) и нескольких раундов уточнений.

## Контекст

Вся работа Epic 1–6 в ветке `stage`. Итог обсуждения: не два набора файлов
на dev/prod, а **один** `docker-compose.server.yml` с параметризацией через
переменные окружения и profiles; **redis один общий** на сервере (не по
инстансу на среду); деплой — через **GitHub Actions**, а не только руками
(это осознанный отказ от более раннего решения «без CI», см.
`manual-deploy-runbook.md`, — сейчас явно решили иначе).

**Переподтверждено по SSH:** dev и prod — один физический сервер
(`efrumos.md`), два Unix-пользователя (`dev_efrumos_md`/`hosting_efrumos_md`),
7.6GB RAM (~6GB свободно). `docker compose` v2.20.2 уже стоит. `node:18.15.0-buster`
уже скачан на обоих — пригодится, если сборку ассетов когда-то понадобится
делать прямо на сервере, но по плану ниже сборку делает раннер GitHub
Actions (у него node из коробки, отдельно доставлять не нужно вообще).

## Архитектура: один compose-файл, параметризованный

`deploy/docker-compose.server.yml` — один файл на обе среды. Путь к коду
(`APP_PATH`) выносится в переменную, `redis` живёт в **отдельном
compose-проекте** (поднимается один раз на сервер, не пересоздаётся при
каждом деплое) и общей **внешней** docker-сети, к которой подключаются и
`queue`/`scheduler` каждой среды, и уже существующие
`php8.2-{dev,hosting}_efrumos_md` контейнеры.

```yaml
# deploy/docker-compose.server.yml
networks:
  efrumos-shared:
    external: true

services:
  redis:
    image: redis:7-alpine
    restart: unless-stopped
    profiles: ["shared"]
    networks: [efrumos-shared]

  mailhog:
    image: mailhog/mailhog
    restart: unless-stopped
    profiles: ["dev"]
    networks: [efrumos-shared]

  queue:
    image: php:8.2-mysql_gd
    restart: unless-stopped
    profiles: ["env"]
    networks: [efrumos-shared, default]
    volumes:
      - ${APP_PATH}:/usr/src/myapp
    working_dir: /usr/src/myapp
    command: php artisan queue:work --sleep=3 --tries=3

  scheduler:
    image: php:8.2-mysql_gd
    restart: unless-stopped
    profiles: ["env"]
    networks: [efrumos-shared, default]
    volumes:
      - ${APP_PATH}:/usr/src/myapp
    working_dir: /usr/src/myapp
    command: php artisan schedule:work
```

Разворачивается тремя вызовами одного и того же файла (сеть — один раз, на
весь сервер; каждая среда — свой compose-проект через `-p`, чтобы `queue`/
`scheduler` не путались между dev и prod):

```bash
# один раз на весь сервер:
docker network create efrumos-shared
docker compose -f deploy/docker-compose.server.yml -p efrumos-shared-svc \
  --profile shared up -d              # redis

# на каждую среду отдельно (APP_PATH из .env деплоя этой среды):
APP_PATH=/var/www/dev.efrumos.md docker compose \
  -f deploy/docker-compose.server.yml -p efrumos-dev \
  --profile env --profile dev up -d   # queue+scheduler+mailhog (dev)

APP_PATH=/var/www/hosting.efrumos.md docker compose \
  -f deploy/docker-compose.server.yml -p efrumos-hosting \
  --profile env up -d                 # queue+scheduler (prod, без mailhog)

# существующие app-контейнеры подключаем к общей сети (без пересоздания):
docker network connect efrumos-shared php8.2-dev_efrumos_md
docker network connect efrumos-shared php8.2-hosting_efrumos_md
```

Изоляция dev/prod на общем redis — через `.env` каждой среды:
`REDIS_PREFIX=dev_efrumos_`/`hosting_efrumos_`, `REDIS_DB=2`/`0`,
`REDIS_CACHE_DB=3`/`1` (Laravel уже поддерживает оба параметра,
`config/database.php:128,137,146`).

**Mailhog веб-интерфейс** — контейнер и сеть поднимаются планом ниже, но
публичный субдомен (`mail.dev.efrumos.md` или как назовёте) для доступа
тестировщиков/разработчиков — отдельный запрос тому, кто ведёт Puppet
(nginx-конфиг на сервере полностью managed, руками правится — затирается
следующим прогоном). Не блокирует остальной план.

## GitHub Actions

Два отдельных workflow — dev автоматический, prod только вручную (кнопка),
никогда по обычному пушу:

**`.github/workflows/deploy-dev.yml`** — триггер: push в `stage`.
1. `actions/checkout` (нужная ветка уже есть — сам триггер).
2. `actions/setup-node` + `npm ci && npm run build` — **на раннере**, не на
   сервере (node там не нужен вообще, экономит шаг из текущего плана).
3. По SSH (`webfactory/ssh-agent` + приватный ключ из GitHub Secret
   `DEPLOY_SSH_KEY`) — вызвать доработанный `bin/server-deploy.sh dev stage
   --ci` (новый флаг `--ci`, чтобы скрипт пропускал интерактивное
   подтверждение `read -r -p` — оно и не нужно, раз это уже осознанный
   ручной запуск workflow на конкретную ветку/среду).
4. `rsync` собранного `public/build` с раннера на сервер (тем же SSH-ключом).
5. Проверка после деплоя: `curl -f https://dev.efrumos.md/ru/` (простой
   healthcheck, job зафейлится, если сайт не отвечает 200).

**`.github/workflows/deploy-prod.yml`** — триггер: **только
`workflow_dispatch`** (ручная кнопка «Run workflow» в GitHub UI), с
обязательным input — веткой, которую катим (по умолчанию `stage`, но явно
вводится, не хардкод). Использует `environment: production` — если на
репозитории/организации доступны Environment protection rules, стоит
отдельно включить обязательное подтверждение ревьюером перед прогоном (это
уже настройка на стороне GitHub, не то, что можно сделать через workflow-файл).
Шаги:
1. Checkout, build ассетов — то же самое, что для dev.
2. **Бэкап перед деплоем** — по SSH: `mysqldump` реальной БД +
   `tar czf backup-pre-$(date +%Y%m%d-%H%M%S).tar.gz` на сервере, до
   каких-либо изменений.
3. `bin/server-deploy.sh prod <ветка-из-input> --ci`.
4. `rsync public/build`.
5. Healthcheck `curl -f https://www.efrumos.md/ru/`.
6. Если healthcheck зафейлился — **не откатываем автоматически** (риск
   сделать хуже вслепую), просто явно фейлим workflow с логами — откат
   делаем руками по плану ниже, глядя на реальную причину.

Секреты, которые нужно завести в GitHub (Settings → Secrets → Actions) —
руками, я не могу сам добавить секреты в репозиторий:
- `DEPLOY_SSH_KEY` — приватный ключ (тот же `~/.ssh/external`, или лучше
  отдельный deploy-key с ограниченным доступом только на этот сервер).
- `DEPLOY_SSH_KNOWN_HOSTS` — вывод `ssh-keyscan efrumos.md`.

## Доработка `bin/server-deploy.sh`

- Обязательный параметр ветки (сейчас клонирует `main` по умолчанию):
  `bin/server-deploy.sh <dev|prod> <branch> [--build-assets] [--ci]`.
- `--ci` — пропускает интерактивный `read -r -p` (для GitHub Actions;
  ручной локальный запуск разработчиком по-прежнему требует явного
  подтверждения набором имени среды).
- `--exclude='public/upfiles'` в `rsync` — обязательно, иначе рискуем
  затереть 1.9GB реальных файлов на проде устаревшим git-снимком (та же
  папка закоммичена в репозиторий, 38303 файлов).
- Ассеты (`public/build`) теперь заливаются самим workflow отдельным rsync
  после сборки на раннере — флаг `--build-assets` в скрипте становится не
  нужен в CI-режиме, но оставляем для ручного локального запуска.

## Часть A — сначала на dev

1. Добавить `deploy/docker-compose.server.yml`, доработать
   `server-deploy.sh` (ветка, `--ci`, исключение `upfiles`).
2. Добавить `.github/workflows/deploy-dev.yml`, `deploy-prod.yml`.
3. Завести секреты в GitHub (руками, вне моих возможностей — нужно сделать
   пользователю).
4. Один раз на сервере: `docker network create efrumos-shared` +
   `--profile shared up -d` (redis).
5. Смёрджить/запушить всё это в `stage` → **автоматически** триггерится
   `deploy-dev.yml`.
6. `.env` на dev создать вручную (как и раньше — DB-креды из
   `~/mariadb.info`, `QUEUE_CONNECTION=redis`, `REDIS_PREFIX=dev_efrumos_`,
   `REDIS_DB=2`, `MAIL_HOST=mailhog`, `INTEGRATION_MOCK_MODE=true`,
   `AMOCRM_ENABLED=false`) — это первый прогон, `.env` ещё не существует,
   workflow специально остановится на этом шаге, если файла нет (как и раньше).
7. Поднять `queue`/`scheduler`/`mailhog` для dev (команда из раздела
   «Архитектура» выше), подключить существующий `php8.2-dev_efrumos_md` к
   `efrumos-shared`.
8. Миграции: `php artisan migrate --force` (пустая БД).
9. Повторно триггернуть `deploy-dev.yml` (или просто новый push в `stage`)
   — теперь уже полный E2E прогон через Actions с существующим `.env`.
10. **Проверка**: реальные HTTP-запросы на `https://dev.efrumos.md` (те же
    пути сессии), `docker compose -p efrumos-dev logs queue scheduler`,
    `storage/logs/laravel.log` на unexpected warning/error.

## Часть B — прод, только после явного апрува dev

Не выполнять до отдельного «давай» после того, как dev проверен и одобрен.
`deploy-prod.yml` запускается вручную (не автоматом), поэтому сам факт
существования workflow ничего не деплоит без явного нажатия кнопки.

1. Бэкап (уже встроен в workflow как шаг 2, но имеет смысл и вручную
   проверить, что дамп реально создался и валиден, прежде чем продолжать).
2. `.env` на прод создать вручную — `REDIS_PREFIX=hosting_efrumos_`,
   `REDIS_DB=0`, реальный `MAIL_*` **не трогаем** (уже настроен и работает),
   `INTEGRATION_MOCK_MODE=true`, `AMOCRM_ENABLED=false`.
3. Поднять `queue`/`scheduler` для prod (без mailhog), подключить
   `php8.2-hosting_efrumos_md` к `efrumos-shared`.
4. Запустить `deploy-prod.yml` вручную из GitHub UI, ветка `stage`.
5. Дождаться healthcheck-шага в самом workflow — если зафейлился, разбираться
   по логам workflow + `storage/logs/laravel.log` на сервере, откатывать
   руками (см. ниже), не давить кнопку повторно вслепую.
6. **Проверка** — те же реальные HTTP-пути на `www.efrumos.md`.
7. **План отката**: восстановить БД из дампа шага 1, вернуть `~` из
   бэкапа/`git checkout` на предыдущий снимок, `docker restart
   php8.2-hosting_efrumos_md`. `queue`/`scheduler` prod — просто `docker
   compose -p efrumos-hosting stop`, `redis` общий — не трогаем.

## Явно вне рамок (отдельные решения/зависимости)

- **Puppet-субдомен для mailhog** — запрос отдельно администратору Puppet.
- **GitHub Environment protection rules** (обязательный ревьюер перед
  `deploy-prod.yml`) — настройка в GitHub UI, не в коде; рекомендую включить,
  но это отдельное действие вне репозитория.
- Второй, «бедный» `Dockerfile` под воспроизводимую локальную сборку под
  реальный набор расширений прода — отдельная задача, не блокирует.

## Проверка

- Реальные HTTP-запросы на живой домен по ключевым путям сессии
  (рекомендации/оттенки/поиск/ближайший магазин/варианты/чекаут).
- `docker compose -p efrumos-{dev,hosting} logs queue scheduler` —
  подтверждение реальных прогонов, не просто «контейнер поднялся».
- `storage/logs/laravel.log` на сервере — unexpected warning/error (другой
  PHP 8.2 vs локальный 8.1, другой набор расширений).
- `php artisan migrate:status` — всё применено, ничего не «pending».
- Healthcheck-шаг самого workflow (`curl -f`) — зелёный статус в GitHub
  Actions как финальное подтверждение.
