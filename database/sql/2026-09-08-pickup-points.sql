-- Пункты выдачи заказов (ответ заказчика от 07.09.2026: самовывоз возможен
-- только из одной точки — Str. V. Alecsandri, 139/3, Кишинёв).
--
-- Признак отдельный от `active`: активность управляет показом магазина на
-- странице «Магазины», а выдача заказов может идти из точки, которой в
-- витрине нет (склад, офис). Применять после основных патчей схемы.

ALTER TABLE shops_id
    ADD COLUMN IF NOT EXISTS pickup_point TINYINT(1) NOT NULL DEFAULT 0;

-- Точка выдачи из ответа заказчика: Vasile Alecsandri, 139/3
UPDATE shops_id si
JOIN shops s ON s.shops_id = si.id
SET si.pickup_point = 1
WHERE s.address LIKE '%lecsandri%139/3%';
