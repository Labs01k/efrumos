<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: sans-serif; font-size: 14px; color: #1a2233;">
    <p>Заказ <strong>#{{ $ordersId }}</strong> не удалось синхронизировать с 1С/Bitrix24 после всех попыток.</p>
    <p>Последняя ошибка:</p>
    <pre style="background: #f4f4f4; padding: 12px; border-radius: 4px;">{{ $error }}</pre>
    <p>Проверьте таблицу <code>integration_id_mappings</code> для заказа #{{ $ordersId }} и повторите синхронизацию вручную при необходимости.</p>
</body>
</html>
