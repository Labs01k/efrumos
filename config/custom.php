<?php

return [

    'back' => [
        # For pagination
        'menu_items_per_page' => 100,
        'goods_subjects_per_page' => 100,
        'goods_items_per_page' => 100,
        'goods_images_per_page' => 100,
        'goods_parameters_per_page' => 100,
        'gallery_subjects_per_page' => 100,
        'gallery_subjects_items_per_page' => 100,
        'labels_per_page' => 200,
        'info_line_per_page' => 100,
        'info_items_per_page' => 100,
        'brands_items_per_page' => 100,
        'modules_items_per_page' => 100,
        'user_group_items_per_page' => 100,
        'slider_items_per_page' => 100,
        'settings_items_per_page' => 100,
        'cities_items_per_page' => 100,
        'shops_items_per_page' => 100,
        'front_users_per_page' => 100,
        'orders_items_per_page' => 100,
        'feedforms_items_per_page' => 100,
        'banners_items_per_page' => 100,
        'social_items_per_page' => 100,
        'goods_promo_items_per_page' => 100,
        'subscribers_items_per_page' => 100,
        'goods_types_items_per_page' => 100,
        'goods_reviews_items_per_page' => 100,
        'goods_tags_items_per_page' => 100,
        # For shops
        'default_latitude' => 47.02465276374675,
        'default_longitude' => 28.83242893218994,
        # Style Versions
        'js_version' => 13,
        'css_version' => 13
    ],

    'front' => [
        'products_per_page' => 20,
        'products_in_slider' => 20,
        'blog_items_per_page' => 9,
        'news_items_per_page' => 12,
        'info_items_in_slider' => 20,
        'promo_items_per_page' => 10,
        'cookie_user_remember_time' => 45000,
        'price_range_max_price' => 10000,
        # Delivery prices
        'until_free_delivery' => 600,
        'delivery_price_chisinau' => 50,
        'delivery_price_moldova' => 100,
        # Style Versions
        # Нижние блоки страницы товара (баннеры, «вы смотрели», преимущества, видео).
        # В новом макете их нет — скрыты флагом, разметка сохранена.
        # Отзывы и блог флагом не затронуты и показываются всегда.
        'show_extra_blocks_on_product_page' => false,

        # Блок «Рекомендованные продукты» на странице товара. В новом макете его нет,
        # поэтому скрыт; чтобы вернуть — поставить true, разметка осталась на месте.
        'show_bestsellers_on_product_page' => false,

        # Параметр «Состав» — выносится на страницу товара отдельной вкладкой.
        'composition_parametr_id' => 3,

        # Параметр «Применение». Источника данных пока нет: поле в CMS не заведено,
        # поэтому вкладка не показывается. Когда появится — вписать сюда id параметра.
        'usage_parametr_id' => null,

        # Типы товара, которые считаются красками для волос: у них на странице
        # показывается палитра оттенков (п.6 ТЗ). Значения из справочника goods_type.
        'dye_goods_type_ids' => [2, 85, 199, 243, 170, 122, 188, 184],

        # Параметр «назначение» (Pentru) — участвует в подборе похожих товаров (п.4 ТЗ)
        'purpose_parametr_id' => 1,

        # Онлайн-оплата картой в оформлении заказа (п.1 ТЗ). Пока банк не выдал
        # ключи мерчанта, выбор этого способа приводит покупателя на страницу
        # эквайера с отказом — поэтому опция показывается только по флагу.
        # Включить: ONLINE_PAYMENT_ENABLED=true в .env (после проверки оплаты).
        'online_payment_enabled' => env('ONLINE_PAYMENT_ENABLED', false),

        # Ключ Google Maps JavaScript API для страницы магазинов (п.2 ТЗ).
        # Пока ключа нет, карта рендерится на Leaflet/OSM — адаптер в shops-page.js.
        'google_maps_key' => env('GOOGLE_MAPS_KEY'),

        # Наличие по магазинам (п.5 ТЗ) — аварийный выключатель блока.
        # Блок и так скрыт, пока 1С не шлёт склады магазинов и в админке
        # не привязаны store_guid; false выключает его принудительно.
        'stock_by_shops_enabled' => true,

        # поднимать при каждом изменении front-assets, иначе вернувшиеся
        # посетители получают старые css/js из кеша браузера
        'js_version' => 59,
        'css_version' => 59,
        'svg_version' => 1,
    ],

    'sorting' => [
        'sort_promo_goods_slider' => ['price', 'asc'],
        'sort_new_goods_slider' => ['updated_at', 'desc'],
        'sort_bestseller_goods_slider' => ['updated_at', 'desc'],
        'sort_similar_goods_slider' => ['updated_at', 'desc'],
        'sort_info_items_slider' => ['add_date', 'desc'],
    ],

    'allowed_cookies'=>[
        'cookie-functional',
        'cookie-advertisement',
    ],

    'main_store_rest_id' => '1e1203a2-64db-11e4-a118-bcee7b8b6616',

    'disallowed_countries_code'=>[
        'RU', 'DE', 'PL', 'BY', 'AE'
    ],

];
