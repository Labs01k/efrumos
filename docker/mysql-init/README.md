Drop a database dump here before the first `docker compose up` and it will be
imported automatically into the `db` container (MariaDB only runs files from
this folder on the very first start — once the `efrumos_db_data` volume
exists, files here are ignored).

Supported: `*.sql`, `*.sql.gz`, `*.sh`.

## Using the production dump for local schema/data

The prod DB schema is legacy (only 4 Laravel migrations exist — the rest of
the tables were never migrated, they only exist as SQL). So the practical way
to get a working local DB is to seed it from an existing dump rather than
`artisan migrate`.

1. Copy a dump here, e.g. `hosting_efrumos_md.sql.gz`.
2. `docker compose up -d db` (first start only — wipe the `efrumos_db_data`
   volume with `docker compose down -v` if you need to re-import).

## Before sharing this dump with anyone else

A production dump contains real customer data (names, phones, addresses,
orders). Keep it local to your machine and do not commit it or hand it to
someone else as-is. If you need to pass a copy to another developer, ask
first and strip/anonymize the personal columns (e.g. `GoodsItemReviews`,
`OrdersUsers`, `Orders*` tables) before sharing.
