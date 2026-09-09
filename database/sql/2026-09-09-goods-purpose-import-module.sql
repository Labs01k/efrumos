-- Раздел CMS «Импорт назначения» — массовое заполнение чекбоксов
-- «Назначение» (goods_parametr_id=5, «Beneficii»/«Решение») по CSV-файлу
-- (артикул + список значений), см. вопрос заказчика 3.4 (part1, 07.09.2026):
-- «можно как-то через импорт файла заполнить эту информацию?».
--
-- Подраздел модуля «Товары» (p_id=7), по образцу «Палитра оттенков»
-- (modules_id id=23, alias=shades) — права наследуются от родителя (goods),
-- отдельных прав заводить не нужно (RoleManager::routeResponder проверяет
-- AdminUserActionPermision по modules_id родителя, не подраздела).
--
-- id заданы явно (44 для modules_id, 111-113 для modules), чтобы совпадали
-- на всех окружениях; на момент патча max(modules_id.id)=43, max(modules.id)=110.

INSERT INTO modules_id
    (id, p_id, alias, level, position, controller, models, view, active, deleted, root, created_at, updated_at)
SELECT 44, 7, 'goods-purpose-import', 2, 25, 'GoodsPurposeImportController', NULL, NULL, 1, 0, 0, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM modules_id WHERE id = 44 OR alias = 'goods-purpose-import'
);

INSERT INTO modules (id, modules_id, lang_id, name, body, created_at, updated_at)
SELECT 111, 44, 1, 'Purpose import', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE id = 111);

INSERT INTO modules (id, modules_id, lang_id, name, body, created_at, updated_at)
SELECT 112, 44, 2, 'Import destinație', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE id = 112);

INSERT INTO modules (id, modules_id, lang_id, name, body, created_at, updated_at)
SELECT 113, 44, 3, 'Импорт назначения', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM modules WHERE id = 113);
