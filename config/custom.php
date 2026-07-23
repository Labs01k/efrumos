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
        'js_version' => 58,
        'css_version' => 58,
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
