<?php

namespace App\View\Components;

use App\Models\Basket;
use App\Models\FrontUser;
use App\Models\GoodsItemId;
use App\Models\GoodsSubjectId;
use App\Models\MenuId;
use App\Models\Wish;
use App\Models\WishId;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class Header extends Component
{
    public $cookie;

    /**
     * Create a new component instance.
     *
     * @return void
     */

    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $view = 'front.components.header';

        $lang_list = LANGS_FRONT;

        $header_top_banner = getItemByAliasWithImage('header-top-banner', 'BannerId');

        $top_header_menu = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'header-menu')
            ->with('children')
            ->first();

        $cookie_basket = Cookie::get('basket');
        $header_total_price = 0;
        $basket_count = 0;
        $header_basket_items = [];
        $goods_items_ids = [];
        if (!empty($cookie_basket)) {
            $basket_count = Basket::where('basket_id', $cookie_basket)->sum('items_count');

            $header_basket_items = Basket::where('basket_id', $cookie_basket)
                ->with('goodsItemId.itemByLang')
                ->with('goodsItemId.getSubjectId')
                ->with('oImage')
                ->orderBy('created_at', 'desc')
                ->get();

            if (!empty($header_basket_items) && count($header_basket_items)) {
                foreach ($header_basket_items as $one_item) {
                    $header_goods_price = $one_item->goodsItemId ? $one_item->goodsItemId->price : 0;
                    $header_total_price += $one_item->items_count * $header_goods_price;
                }

                //For GA4
                $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($header_basket_items,1);
                //For FB Pixel
                $goods_items_ids = json_encode($header_basket_items->pluck('goods_one_c_code')->toArray());
            }


        }

        if (!Session::get('session-front-user')) {
            if (Cookie::get('front-user-remember')) {
                $user = FrontUser::where('id', Cookie::get('front-user-remember'))->first();
                if ($user)
                    Session::put('session-front-user', $user->id);
            }
        }

        $user = app('global_user');
        $wish_count = 0;
        if ($user) {
            $wish_id = WishId::where('front_user_id', $user->id)->value('id');

            if ($wish_id)
                $wish_count = Wish::where('wish_id', $wish_id)->count();

        }

        $recovery_user = null;
        $hash = request()->input('h');

        if ($hash) {
            $recovery_user = FrontUser::where('recovery_hash', $hash)->first();
        }

        //For search
        $popular_search = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'search-popular')
            ->has('itemByLang')
            ->with('itemByLang', 'children')
            ->first();

        $show_in_search_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('show_in_search', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->orderBy('updated_at', 'desc')
            ->limit(2)
            ->get();
        //End search

        return view($view, get_defined_vars());
    }
}
