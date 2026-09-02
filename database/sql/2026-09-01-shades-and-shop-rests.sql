-- Изменения структуры под п.5 (остатки по магазинам) и п.6 (CMS палитры оттенков).
-- Проект живёт на дампах без миграций, поэтому изменения оформлены сырым SQL:
-- применить на проде один раз при деплое (безопасно повторять — есть IF NOT EXISTS/WHERE).

-- Фото оттенка (п.6): отдельная картинка локона на товар-оттенок, грузится в CMS.
ALTER TABLE goods_item_id
    ADD COLUMN IF NOT EXISTS shade_img VARCHAR(255) NULL DEFAULT NULL;

-- Остатки по складам 1С (п.5): сайт сохраняет ВСЕ строки Rests из обмена,
-- а не только главный склад. Пока 1С шлёт один склад — тут одна строка на товар.
CREATE TABLE IF NOT EXISTS goods_shop_rests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    goods_item_id INT UNSIGNED NOT NULL,
    store_guid VARCHAR(64) NOT NULL,
    store_name VARCHAR(255) NULL DEFAULT NULL,
    qty DECIMAL(10, 2) NOT NULL DEFAULT 0,
    created_at DATETIME NULL DEFAULT NULL,
    updated_at DATETIME NULL DEFAULT NULL,
    UNIQUE KEY uq_item_store (goods_item_id, store_guid),
    KEY idx_store (store_guid)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Привязка магазина CMS к складу 1С: заполняется в админке магазинов,
-- когда 1С начнёт слать склады магазинов (StoreId из Rests).
ALTER TABLE shops_id
    ADD COLUMN IF NOT EXISTS store_guid VARCHAR(64) NULL DEFAULT NULL;

-- CMS-раздел «Палитра оттенков»: оживляем спящий подраздел модуля «Товары»
-- (id=23, был alias=color, controller не существовал). Права уже есть —
-- подразделы проверяются по правам родительского модуля goods (id=7).
UPDATE modules_id
SET alias = 'shades', controller = 'ShadePaletteController', active = 1, deleted = 0
WHERE id = 23;

UPDATE modules SET name = 'Paleta de nuanțe' WHERE modules_id = 23 AND lang_id = 2;
UPDATE modules SET name = 'Shade palette' WHERE modules_id = 23 AND lang_id = 1;
INSERT INTO modules (modules_id, lang_id, name, body, created_at, updated_at)
SELECT 23, 3, 'Палитра оттенков', '', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE modules_id = 23 AND lang_id = 3);
