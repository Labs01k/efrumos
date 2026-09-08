-- Параметр «Применение» (Mod de utilizare) для страницы товара.
--
-- Ответ заказчика от 07.09.2026: вкладка нужна для всего каталога, тексты
-- заполняют они сами. Поля под неё в CMS не было — заводим.
--
-- Тип textarea: текст свободный и свой на каждом языке, хранится в
-- goods_parametr_item_simple.parametr_value (varchar(1024) на язык).
-- Справочник значений тут не нужен, в отличие от остальных параметров.
--
-- id задан явно (50), чтобы config('custom.front.usage_parametr_id') совпадал
-- на всех окружениях; на момент патча max(id) = 49.

INSERT INTO goods_parametr_id
    (id, goods_subject_id, measure_type, goods_measure_id, parametr_type, alias,
     position, active, deleted, show_in_list, start_open, display_in_line,
     display_on_list_page, created_at, updated_at)
SELECT 50, 1, 'no_measure', NULL, 'textarea', 'mod-de-utilizare',
       8, 1, 0, 0, 1, 0, 0, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM goods_parametr_id WHERE id = 50 OR alias = 'mod-de-utilizare'
);

-- Текст применения не влезает в исходные varchar(1024): подключение Laravel идёт
-- с strict => false, поэтому длинное описание молча обрезалось бы при сохранении.
ALTER TABLE goods_parametr_item_simple MODIFY COLUMN parametr_value TEXT NULL;

-- Названия для админки: на витрине заголовок вкладки берётся из переводов
-- (variables.product_tab_usage), эти нужны редактору в карточке товара.
INSERT INTO goods_parametr (goods_parametr_id, lang_id, name, created_at, updated_at)
SELECT 50, 2, 'Mod de utilizare', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM goods_parametr WHERE goods_parametr_id = 50 AND lang_id = 2
);

INSERT INTO goods_parametr (goods_parametr_id, lang_id, name, created_at, updated_at)
SELECT 50, 3, 'Применение', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM goods_parametr WHERE goods_parametr_id = 50 AND lang_id = 3
);
