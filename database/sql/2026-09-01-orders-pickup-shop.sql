-- Выбор магазина самовывоза при оформлении заказа (п.2 ТЗ, тикет борда).
-- Разовый выбор на заказ, в профиле не хранится. Применить на проде при деплое.

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS pickup_shop_id INT UNSIGNED NULL DEFAULT NULL AFTER delivery_method;
