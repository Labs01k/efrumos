<?php
//Документация пакета https://github.com/diglactic/laravel-breadcrumbs
//Метод parent отвечает за назначение родителя
//Метод push отвечает за размемещение имени и url в шаблоне

//use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

Breadcrumbs::for ('/', function ($trail) {
    $trail->push(ShowLabelById(149), '/');
});

//Вывод крошек для текстовых страниц, которые относятся к parent_menu,
//так же исключаем те страницы которые не относятся к parent_menu,
//что бы избежать ошибку (Breadcrumb name "catalog" has already been registered)

Breadcrumbs::for ((string) Request::segment(2), function ($trail, $parent_menu) {
    $trail->parent('/');
    $trail->push($parent_menu->itemByLang->name, Request::segment(2));
});

Breadcrumbs::for ('cart-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(150), 'cart');
});

Breadcrumbs::for ('shops-list', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(160), 'shops');
});

Breadcrumbs::for ('register-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(167), 'register');
});

//Cabinet
Breadcrumbs::for('cabinet-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(151), 'cabinet');
});

Breadcrumbs::for('cabinet-orders', function ($trail) {
    $trail->parent('cabinet-page');
    $trail->push(ShowLabelById(153), 'orders');
});

Breadcrumbs::for('cabinet-wish', function ($trail) {
    $trail->parent('cabinet-page');
    $trail->push(ShowLabelById(154), 'wish');
});

Breadcrumbs::for('cabinet-password', function ($trail) {
    $trail->parent('cabinet-page');
    $trail->push(ShowLabelById(155), 'password');
});
//End cabinet

Breadcrumbs::for ('checkout-success-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(162), 'checkout-success');
});

Breadcrumbs::for ('checkout-success-register-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(198), 'register-success');
});

//Blog
Breadcrumbs::for('blog-list', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(156), 'blog');
});

Breadcrumbs::for('blog-item', function ($trail, $blog_item) {
    $trail->parent('blog-list');
    $trail->push($blog_item->itemByLang->name, $blog_item->alias);
});
//End blog

//News
Breadcrumbs::for('news-list', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(157), 'news');
});

Breadcrumbs::for('news-item', function ($trail, $news_item) {
    $trail->parent('news-list');
    $trail->push($news_item->itemByLang->name, $news_item->alias);
});
//End news

//Promo
Breadcrumbs::for('promo-list', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(158), 'promo');
});

Breadcrumbs::for('promo-item', function ($trail, $promo_item) {
    $trail->parent('promo-list');
    $trail->push($promo_item->itemByLang->name, $promo_item->alias);
});
//End promo

//Brands
Breadcrumbs::for('brands-list', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(159), 'brands');
});

Breadcrumbs::for('brands-item', function ($trail, $brand_item) {
    $trail->parent('brands-list');

    GetMainParent('goods_brand',$brand_item->p_id,LANG_ID,$p_list);

    $parent_list = [];
    if(!is_null($p_list))
        $parent_list = array_reverse($p_list);

    if ($parent_list) {
        foreach ($parent_list as $key => $one_parent_item) {
            $trail->push($one_parent_item->name, 'brands/' . $one_parent_item->alias);
        }
    }

    $trail->push($brand_item->itemByLang->name, $brand_item->alias);
});
//End brands

//Products
Breadcrumbs::for ('catalog-page', function ($trail) {
    $trail->parent('/');
    $trail->push(ShowLabelById(161), 'catalog');
});

Breadcrumbs::for ('goods-subject', function ($trail, $goods_subject) {
    $trail->parent('catalog-page');

    GetMainParent('goods_subject',$goods_subject->p_id,LANG_ID,$p_list);

    $parent_list = [];
    if(!is_null($p_list))
        $parent_list = array_reverse($p_list);

    if ($parent_list) {
        foreach ($parent_list as $key => $one_parent_item) {
            //dd($one_parent_item);
            if ($one_parent_item->alias != 'category' && $key != 0)
                $trail->push($one_parent_item->name, 'category/' . $one_parent_item->alias);
        }
    }

    $trail->push($goods_subject->itemByLang->name, $goods_subject->alias);
});

Breadcrumbs::for ('goods-item', function ($trail, $goods_subject, $goods_item) {

    $trail->parent('catalog-page');

    GetMainParent('goods_subject',$goods_subject,LANG_ID,$p_list);

    $parent_list = [];
    if(!is_null($p_list))
        $parent_list = array_reverse($p_list);

    if ($parent_list) {
        foreach ($parent_list as $key => $one_parent_item) {
            if ($one_parent_item->alias != 'category' && $key != 0)
                $trail->push($one_parent_item->name, 'category/' . $one_parent_item->alias);
        }
    }

    $trail->push($goods_item->itemByLang->name);

});
//End products
