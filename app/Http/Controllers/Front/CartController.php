<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\DeliveryState;
use App\Models\GoodsItemId;
use App\Models\GoodsPromo;
use App\Models\GoodsPromoItems;
use App\Services\FacebookAds\FacebookPixelConversion;
use App\Services\GA4\GoogleEcommerce;
use App\Traits\CartTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    //use CartTrait;

    //Функция для промо discount X Cant =% DISCOUNT
    private function BasketPromoPrice($basket_id, $current_date, $discount_tip_price)
    {
        $goods_discount_list = GoodsPromo::select(
            'goods_promo.id AS goods_promo_id',
            'goods_promo.one_c_id',
            'goods_promo.cant_pentru_disc',
            'goods_promo.discount_procent',
            'goods_promo.discount_summa',
            DB::raw('SUM(basket.items_count) as items_total'))
            ->join('goods_promo_items', 'goods_promo_items.goods_promo_id', '=', 'goods_promo.id')
            ->join('basket', 'basket.goods_one_c_code', '=', 'goods_promo_items.one_c_id')
            ->whereRaw('goods_promo_items.one_c_id IN(SELECT goods_one_c_code FROM basket WHERE basket_id=' . $basket_id . ')')
            ->where('promo_type', 5)
            ->where('data_start', '<=', $current_date)
            ->where('data_end', '>=', $current_date)
            ->where('tip_price', $discount_tip_price)
            ->where('basket_id', $basket_id)
            ->groupBy('goods_promo.one_c_id')
            ->orderBy('goods_promo.cant_pentru_disc', 'DESC')
            ->get();

        $discount_found = 0;
        if (count($goods_discount_list)) {
            foreach ($goods_discount_list as $goods_discount) {
                if ($goods_discount->items_total >= $goods_discount->cant_pentru_disc) {//если у нас количество больше какого-то - попадаем сюда
                    $discount_procent = $goods_discount->discount_procent;
                    $discount_summa = $goods_discount->discount_summa;
                    $discount_found = 1;
                    break;//останавливаем цикл как только нашли нужное значение
                }
            }
            if (!$discount_found) {
                $discount_procent = 0;
                $discount_summa = 0;
            }
            Basket::where('basket_id', $basket_id)
                ->whereRaw('`goods_one_c_code` IN(SELECT `one_c_id` FROM `goods_promo_items` WHERE `goods_promo_id`=' . $goods_discount->goods_promo_id . ')')
                ->update([
                    'promo_one_c_id' => $goods_discount->one_c_id,
                    'discount_procent' => $discount_procent,
                    'discount_summa' => $discount_summa
                ]);
        }

        //Удаляем все старые скидки
        $promo_bad_list = GoodsPromo::whereIn('promo_type', array(1, 2, 3, 4, 5))
            ->where('data_start', '>', $current_date)//либо будущие
            ->orWhere('data_end', '<', $current_date)//либо старые
            ->pluck('one_c_id')
            ->toArray();
        if (!empty($promo_bad_list)) {
            Basket::where('basket_id', $basket_id)
                ->whereIn('promo_one_c_id', $promo_bad_list)
                ->update([
                    'promo_one_c_id' => null,
                    'discount_procent' => null,
                    'discount_summa' => null,
                    'has_cadou' => 0
                ]);
        }
        //Удаляем все несуществующие скидки
        $promo_list = GoodsPromo::whereIn('promo_type', [1, 2, 3, 4, 5])
            ->pluck('one_c_id')
            ->toArray();
        $promo_list_from_basket = Basket::where('basket_id', $basket_id)
            ->where('promo_one_c_id', '>', 0)
            ->pluck('promo_one_c_id')
            ->toArray();

        if (!empty($promo_bad_list) && !empty($promo_list_from_basket)) {
            $promo_list_diff = array_diff($promo_list_from_basket, $promo_list);
            if (!empty($promo_list_diff)) {
                Basket::where('basket_id', $basket_id)
                    ->whereIn('promo_one_c_id', $promo_list_diff)
                    ->update([
                        'promo_one_c_id' => null,
                        'discount_procent' => null,
                        'discount_summa' => null,
                        'has_cadou' => 0
                    ]);
            }
        }
    }

    public function index(Request $request)
    {
        if ($request->routeIs('checkout')) {
            $view = 'front.pages.checkout.checkout';
            Session::put('if_page_checkout', 1);

            $districts = DeliveryState::where('active', 1)
                ->where('country_id', 140) //140 Moldova
                ->orderBy('name', 'asc')
                ->get();

            $month_list = showSettingBodyByAlias('month-list') ? explode(';', showSettingBodyByAlias('month-list')) : [];

            // магазины самовывоза (п.2 ТЗ): все активные, наличие товара не проверяется,
            // сгруппированы по городам — Кишинёв первым, дальше по алфавиту
            $pickup_shops = \App\Models\ShopsId::where('active', 1)
                ->has('itemByLang')
                ->with('itemByLang')
                ->orderBy('position', 'asc')
                ->get()
                ->groupBy(function ($one_shop) {
                    $name = $one_shop->itemByLang->name ?? '';

                    return trim(explode(',', $name)[0], " :\t");
                })
                ->sortBy(function ($group, $city) {
                    return (mb_stripos($city, 'Кишин') === false && mb_stripos($city, 'Chi') === false ? '1' : '0') . mb_strtolower($city);
                });

        } else
            $view = 'front.pages.product-action.basket';

        $cookie_basket = $request->cookie('basket');
        $lang_id = LANG_ID;

        $basket = [];
        $total_price = 0;
        $total_price_items = 0;
        $pina_livrare = config('custom.front.until_free_delivery');
        $costul_livrarei = config('custom.front.delivery_price_chisinau');
        $discount_goods_price = 0;
        $basket_promo = [];
        $goods_objects_fb = [];


        //Тип цены для промо 1 + cadou
        $tip_price = 'b2c';

        //Тип цены для промо discount X Cant =% DISCOUNT
        $discount_tip_price = 'b2c';

        $current_date = date('Y-m-d');
        $current_date_time = date('Y-m-d H:i:s');

        if (!is_null($cookie_basket)) {

            $basket_id = BasketId::where('id', $cookie_basket)->first();

            if (!is_null($basket_id)) {
                $basket = Basket::where('basket_id', $basket_id->id)
                    ->get();

                $deleted_items = [];
                $deleted_item_name = [];

                $basket_items_count = $basket->sum('items_count');

                //Для промо discount X Cant =% DISCOUNT
                $this->BasketPromoPrice($basket_id->id, $current_date_time, $discount_tip_price);

                if (!empty($basket)) {

                    foreach ($basket as $key => $one_item) {

                        $goods_item[$one_item->id] = GoodsItemId::where('active', 1)
                            ->where('deleted', 0)
                            ->where('goods_item_id.id', $one_item->goods_item_id)
                            ->has('itemByLang')
                            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang', 'checkIfWishItemExist')
                            ->first();

                        $goods_price_collect = getGoodsPrice($goods_item[$one_item->id]);

                        if (empty($goods_item[$one_item->id]) || $goods_item[$one_item->id]->active == 0 || $goods_item[$one_item->id]->deleted == 1 || $goods_item[$one_item->id]->in_stoc == 0) {
                            $deleted_items[$key] = Basket::where('id', $one_item->id)->first();
                            Basket::where('id', $one_item->id)->delete();
                        } else {
                            Basket::where('id', $one_item->id)
                                ->update(['goods_price' => $goods_price_collect->price]);
                        }

                        //Для промо 1 + cadou
                        $goods_promo[$goods_item[$one_item->id]->id] = GoodsPromoItems::where('goods_item_id', $goods_item[$one_item->id]->id)
                            ->join('goods_promo', 'goods_promo.id', '=', 'goods_promo_items.goods_promo_id')
                            ->whereIn('promo_type', array(3, 4))
                            ->where('is_produs', 1)
                            ->where('tip_price', $tip_price)
                            ->where('data_start', '<=', $current_date)
                            ->where('data_end', '>=', $current_date)
                            ->first();

                        if ($goods_promo[$goods_item[$one_item->id]->id]) {
                            $goods_promo_cadou_list = GoodsPromoItems::where('goods_promo_id', $goods_promo[$goods_item[$one_item->id]->id]->goods_promo_id)
                                ->where('is_cadou', 1)
                                ->with('getGoodsItemId', 'getGoodsItemId.itemByLang', 'getGoodsItemId.oImage', 'getGoodsItemId.getBrand.itemByLang')
                                ->get();

                            //dd($goods_promo_cadou_list)

                            $goods_promo_cadou = $goods_promo_cadou_list->first();

                            if ($goods_promo[$goods_item[$one_item->id]->id]->promo_type == 3) {
                                $one_item->has_cadou = 1;
                                $one_item->promo_one_c_id = $goods_promo_cadou->getPromoId->one_c_id;
                                //$one_item->related_one_c_id = $goods_promo_cadou->one_c_id;
                                $one_item->update();
                            }
                        }
                        //Конец промо 1 + cadou

                        if ($one_item->promo_one_c_id > 0)
                            $basket_promo[$one_item->id] = GoodsPromo::where('one_c_id', $one_item->promo_one_c_id)
                                ->where('promo_type', 5)
                                ->where('tip_price', $discount_tip_price)
                                ->where('data_start', '<=', $current_date)
                                ->where('data_end', '>=', $current_date)
                                ->first();

                        $goods_price_collect = getGoodsPrice($goods_item[$one_item->id]);
                        //$item_price = getGoodsPrice($goods_item[$one_item->id]);

                        $total_price_items += $goods_price_collect->price * $one_item->items_count;

                        $total_price += $goods_price_collect->price * $one_item->items_count;

                        $total_item_price[$one_item->id] = $total_price_items;

                        $total_price_items = 0;

                        //Для промо discount X Cant =% DISCOUNT

                        if ($one_item->promo_one_c_id > 0) {
                            if ($one_item->discount_procent > 0 || $one_item->discount_summa > 0) {
                                if ($one_item->discount_procent > 0) {
//                            $discount_goods_price_one = round($item_price * $one_item->discount_procent / 100);
                                    $one_item_discount_price = round($goods_price_collect->price * (100 - $one_item->discount_procent) / 100);
                                    $discount_goods_price_one = $goods_price_collect->price - $one_item_discount_price;
                                    $discount_goods_price += $discount_goods_price_one * $one_item->items_count;
                                } elseif ($one_item->discount_summa > 0) {
                                    $discount_goods_price += $one_item->discount_summa;
                                }
                            }
                        }
                        //Конец промо discount X Cant =% DISCOUNT

                        //For FB Pixel Api conversions
                        $goods_objects_fb[] = [
                            'id' => $one_item->goods_one_c_code,
                            'quantity' => $one_item->items_count,
                            'item_price' => priceFormatForGA4($goods_price_collect->price)
                        ];
                    }

                    $pina_livrare = $pina_livrare - $total_price + $discount_goods_price;//23.04.2020 was added + $discount_goods_price

                    if ($pina_livrare <= 0) {
                        $pina_livrare = 0;
                        $costul_livrarei = 0;
                    }
                }

                $basket = Basket::where('basket_id', $basket_id->id)
                    ->get();

                //For GA4
                $goods_objects =[];
                $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($basket,1);
                //For FB Pixel Api Conversions
                $goods_items_ids = [];
                $goods_items_ids = json_encode($basket->pluck('goods_one_c_code')->toArray());

                $goods_collect = collect();
                $goods_collect->num_items = $basket_items_count;
                $goods_collect->content_ids = $goods_items_ids;
                $goods_collect->contents = $goods_objects_fb;
                $goods_collect->value = priceFormatForGA4($discount_goods_price && $discount_goods_price > 0 ? $total_price + $costul_livrarei - $discount_goods_price : $total_price + $costul_livrarei);
                FacebookPixelConversion::pixelEvent('InitiateCheckout', $goods_collect);


                if ($basket->isEmpty())
                    $basket = [];
            }
        }

        $meta = collect([]);
        $meta->meta_static = ShowLabelById(150) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }

    public function ajaxAddToCart(Request $request)
    {
        $goods_item_id = $request->input('goods_item_id');
        $number_count = $request->input('number');
        $cookie_basket = Cookie::get('basket');

        $front_count = !is_null($number_count) && $number_count > 0 ? $number_count : 1;

        $goods_item = GoodsItemId::where('id', $goods_item_id)
            ->where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang')
            ->first();

        if (is_null($goods_item))
            return response()->json([
                'status' => false,
                'text' => 'Product not found'
            ]);

        $goods_price = getGoodsPrice($goods_item);

        if ($goods_price == null)
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(264)
            ]);

        // товар мог закончиться между загрузкой страницы и кликом (п.3 ТЗ):
        // отказ отдаём явно, добавление остальных позиций комплекта продолжается
        if ($goods_item->in_stoc == 0 || $goods_item->products_count <= 0)
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(272)
            ]);

        $basket = null;
        $basket_id = null;

        $basket_id = BasketId::updateOrCreate(['id' => $cookie_basket], [
            'user_ip' => $request->ip()
        ]);

        if ($basket_id) {
            $basket = Basket::where('goods_item_id', $goods_item->id)
                ->where('basket_id', $basket_id->id)
                ->first();

            $if_has_basket_id = $basket ? $basket->id : 0;

            Basket::updateOrCreate(['id' => $if_has_basket_id], [
                'basket_id' => $basket_id->id,
                'goods_item_id' => $goods_item->id,
                'items_count' => $basket ? $basket->items_count + $front_count : $front_count,
                'goods_price' => $goods_price->price,
                'goods_name' => $goods_item->itemByLang->name,
                'goods_one_c_code' => $goods_item->one_c_code
            ]);
            Cookie::queue('basket', $basket_id->id, config('custom.front.cookie_user_remember_time'));
        } else {
            return response()->json([
                'status' => false,
                'text' => 'Basket not found'
            ]);
        }

        $count_all_goods = Basket::where('basket_id', $basket_id->id)->sum('items_count');

        $all_basket_items = Basket::where('basket_id', $basket_id->id)->get();

        $total_price = 0;

        if (!empty($all_basket_items) && count($all_basket_items)) {
            foreach ($all_basket_items as $one_item) {
                $goods_price_collect = getGoodsPrice($one_item->goodsItemId);
                $total_price += $goods_price_collect->price * $one_item->items_count;
            }
        }

        /*$view_ajax = 'front.ajax.header-basket-items';
        $header_basket_items_view = view($view_ajax, ['header_basket_items' => $header_basket_items, 'total_price' => $total_price, 'count_all_goods' => $count_all_goods])->render();*/

        $modal_add_to_basket = 'front.pages.ajax.goods-add-to-basket';

        $bestseller_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('popular_element', 1)
            ->where('id', '!=', $goods_item->id)
            ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
            ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
            ->limit(config('custom.front.products_in_slider'))
            ->get();

        $render_modal_add_to_basket = view($modal_add_to_basket, ['goods_item' => $goods_item, 'total_price' => $total_price, 'count_all_goods' => $count_all_goods, 'bestseller_goods' => $bestseller_goods])->render();

        $modal_show_basket = 'front.templates.header-basket-items';

        //For GA4
        $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($all_basket_items,1);
        //For FB Pixel
        $goods_items_ids = json_encode($all_basket_items->pluck('goods_one_c_code')->toArray());

        $render_modal_show_basket = view($modal_show_basket, ['goods_item' => $goods_item, 'header_total_price' => $total_price, 'basket_count' => $count_all_goods, 'header_basket_items' => $all_basket_items, 'goods_objects' => $goods_objects, 'goods_items_ids' => $goods_items_ids])->render();

        //For GA4
        $goods_object = GoogleEcommerce::oneGoodsCollectionToObjects($goods_item);
        //For FB Pixel
        $goods_collect = collect();
        $goods_collect->goods_price = $goods_price->price;
        $goods_collect->goods_item = $goods_item;
        FacebookPixelConversion::pixelEvent('AddToCart', $goods_collect);

        return response()->json([
            'status' => true,
            'basket_count' => $count_all_goods,
            'total_price' => $total_price,
            //'header_basket_items_view' => $header_basket_items_view,
            'modal_add_to_basket' => $render_modal_add_to_basket,
            'modal_show_basket' => $render_modal_show_basket,
            'message' => ShowLabelById(47),
            //For GA4
            'goods_object' => json_decode($goods_object)
            //'type' => 'to_cart'
        ]);
    }

    public function ajaxDiffSumItemCart(Request $request)
    {
        $goods_item_id = intval($request->input('goods_item_id'));
        $cookie_basket = $request->cookie('basket');
        $page = $request->input('page');
        $number = $request->input('number');
        $cadou_id = $request->input('cadou_id');

        $item_count = !is_null($number) && $number > 0 ? $number : 1;

        $cadou_min = 0;
        $discount_text = '';
        $item_discount_price = 0;
        $show_discount = 0;
        $basket_one_item_price = 0;
        $pina_livrare = config('custom.front.until_free_delivery');
        $costul_livrarei = config('custom.front.delivery_price_chisinau');

        if (!empty($page) && $page == 'cart' || $page == 'header-cart') {

            $basket = Basket::where('goods_item_id', $goods_item_id)
                ->where('basket_id', $cookie_basket)
                ->first();

            if (is_null($basket) || is_null($cookie_basket)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empty basket'
                ]);
            }

            Basket::where('goods_item_id', $goods_item_id)
                ->where('basket_id', $cookie_basket)
                ->update(['items_count' => $item_count]);

            $basket_one_item = Basket::where('goods_item_id', $goods_item_id)
                ->where('basket_id', $cookie_basket)
                ->first();

            $basket_one_item_price = getGoodsPrice($basket_one_item->goodsItemId);

            if ($cadou_id > 0) {
                $promo = GoodsPromo::where('one_c_id', $cadou_id)->first();
                if ($promo) {
                    if ($promo->cant_pentru_disc > $number) {
                        Basket::where('goods_item_id', $goods_item_id)
                            ->where('basket_id', $cookie_basket)
                            ->update(['related_one_c_id' => null]);
                    }
                    $cadou_min = $promo->cant_pentru_disc;
                }
            }


            //Тип цены для промо discount X Cant =% DISCOUNT
            $discount_tip_price = 'b2c';
            $current_date = Carbon::now()->format('Y-m-d');
            $current_date_time = Carbon::now()->format('Y-m-d H:i:s');

            //Для промо discount X Cant =% DISCOUNT
            $this->BasketPromoPrice($cookie_basket, $current_date_time, $discount_tip_price);

            $basket_one_item = Basket::where('goods_item_id', $goods_item_id)
                ->where('basket_id', $cookie_basket)
                ->first();

            if ($basket_one_item->promo_one_c_id > 0) {
                if ($basket_one_item->discount_procent > 0 || $basket_one_item->discount_summa > 0) {
                    if ($basket_one_item->discount_procent > 0) {
                        $item_discount_price = round($basket_one_item_price->price * (100 - $basket_one_item->discount_procent) / 100);
                        $show_discount = 1;
                    } elseif ($basket_one_item->discount_summa > 0) {
                        $item_discount_price = round($basket_one_item_price->price - $basket_one_item->discount_summa);
                        $show_discount = 1;
                    }
                }
            }

            $basket_one_item_real_price = $item_discount_price > 0 ? $item_discount_price : $basket_one_item_price->price;

            $total_item_price = $basket_one_item->items_count * $basket_one_item_real_price;
        }

        $count_all_goods = Basket::where('basket_id', $cookie_basket)->sum('items_count');
        //$count_all_goods = Basket::where('basket_id', $cookie_basket)->count('id');

        $all_basket_items = Basket::where('basket_id', $cookie_basket)->get();

        $total_price = 0;
        $discount_goods_price = 0;
        $total_discount_price = 0;

        $card = 0;
        if (!$all_basket_items->isEmpty()) {
            foreach ($all_basket_items as $one_item) {

                /*if (!is_null($item) && $item->is_card == 1)
                    $card++;*/

                $goods_price_collect = getGoodsPrice($one_item->goodsItemId);

                $total_price += $goods_price_collect->price * $one_item->items_count;

                //Для промо discount X Cant =% DISCOUNT
                if ($one_item->promo_one_c_id > 0) {
                    if ($one_item->discount_procent > 0 || $one_item->discount_summa > 0) {
                        if ($one_item->discount_procent > 0) {
//                            $discount_goods_price_one = round($item_price * $one_item->discount_procent / 100);
                            $one_item_discount_price = round($goods_price_collect->price * (100 - $one_item->discount_procent) / 100);
                            $discount_goods_price_one = $goods_price_collect->price - $one_item_discount_price;
                            $discount_goods_price += $discount_goods_price_one * $one_item->items_count;
                        } elseif ($one_item->discount_summa > 0) {
                            $discount_goods_price += $one_item->discount_summa;
                        }
                    }

                    if ($one_item->goods_item_id == $goods_item_id) {
                        $basket_promo = GoodsPromo::where('one_c_id', $one_item->promo_one_c_id)->first();
                        if ($basket_promo) {
                            $discount_text = str_replace(['{items_count}', '{conditions}'], [$basket_promo->cant_pentru_disc, $one_item->discount_summa > 0 ? $one_item->discount_summa . ' ' . ShowLabelById(3) : $basket_promo->discount_procent . '%'], showSettingBodyByAlias('promo-text-cant-discount'));
                            /* 380 <p class="basket__element__pink">Temperatura dicteaza reducerea!</p><p>Oferta valabila chiar de la <span class="basket__element__offer-count">{items_count}</span> produs in cos! Azi reducerea este de <span class="basket__element__blue">{conditions}</span> </p>*/
                        }
                    }
                }
            }
        }

        $basket_total_price = $total_price;

        $pina_livrare = $pina_livrare - $basket_total_price + $discount_goods_price;//23.04.2020 was added + $discount_goods_price

        if ($pina_livrare <= 0) {
            $pina_livrare = 0;
            $costul_livrarei = 0;
        }

        return response()->json([
            'status' => true,
            'page' => $page,
            'basket_count' => $count_all_goods,
            'basket_count_item' => $basket_one_item->items_count,
            'item_price' => round($basket_one_item_real_price),
            'item_real_price' => round($basket_one_item_price->price_default),
            'show_discount' => $show_discount,
            'total_price' => $basket_total_price + $costul_livrarei - $discount_goods_price,
            'total_item_price' => $total_item_price,
            'sub_total' => $basket_total_price,
            'pina_livrare' => $pina_livrare,
            'costul_livrarei' => $costul_livrarei,
            'cadou_min' => $cadou_min,
            'discount_text' => $discount_text,
            'discount_goods_price' => round($discount_goods_price),
        ]);
    }

    public function ajaxDestroyItemCart(Request $request)
    {
        $goods_item_id = $request->input('goods_item_id');
        $cookie_basket = $request->cookie('basket');

        $basket = Basket::where('goods_item_id', $goods_item_id)
            ->where('basket_id', $cookie_basket)
            ->first();

        if (is_null($basket) || is_null($cookie_basket))
            return response()->json([
                'status' => false,
                'message' => 'Basket item is empty'
            ]);

        Basket::where('goods_item_id', $goods_item_id)
            ->where('basket_id', $cookie_basket)
            ->delete();

        $count_all_goods = Basket::where('basket_id', $cookie_basket)->count('id');

        $basket_item_after_delete = Basket::where('basket_id', $cookie_basket)
            ->count();

        if ($basket_item_after_delete < 1) {
            BasketId::where('id', $cookie_basket)->delete();

            if (!is_null(Cookie::get('basket'))) {
                Cookie::queue(Cookie::forget('basket'));
            }
        }

        $all_basket_items = Basket::where('basket_id', $cookie_basket)->get();

        $total_price = 0;
        $pina_livrare = config('custom.front.until_free_delivery');
        $costul_livrarei = config('custom.front.delivery_price_chisinau');
        $discount_goods_price = 0;

        if (!$all_basket_items->isEmpty()) {
            foreach ($all_basket_items as $one_item) {

                $goods_price_collect = getGoodsPrice($one_item->goodsItemId);
                $total_price += $goods_price_collect->price * $one_item->items_count;

                if ($one_item->promo_one_c_id > 0 && ($one_item->discount_procent > 0 || $one_item->discount_summa > 0)) {
                    $procent = $one_item->discount_procent / 100;
                    $discount_goods_price += $one_item->discount_procent > 0 ? $goods_price_collect->price * $procent * $one_item->items_count : $one_item->discount_summa;
                }
            }
        }

        $pina_livrare = $pina_livrare - $total_price + $discount_goods_price;//23.04.2020 was added + $discount_goods_price

        if ($pina_livrare <= 0) {
            $pina_livrare = 0;
            $costul_livrarei = 0;
        }

        //For GA4
        $goods_object = null;
        if ($basket->goodsItemId)
            $goods_object = GoogleEcommerce::oneGoodsCollectionToObjects($basket->goodsItemId);

        return response()->json([
            'status' => true,
            'basket_count' => $count_all_goods,
            'discount_goods_price' => round($discount_goods_price),
            'costul_livrarei' => $costul_livrarei,
            'pina_livrare' => $pina_livrare,
            'sub_total' => $total_price,
            'total_price' => $total_price + $costul_livrarei - $discount_goods_price,
            'message' => ShowLabelById(48),
            //For GA4
            'goods_object' => json_decode($goods_object)
        ]);
    }

    public function ajaxDestroyAllItemsCart()
    {
        $cookie_basket = Cookie::get('basket');

        BasketId::where('id', $cookie_basket)->delete();

        Cookie::queue(Cookie::forget('basket'));

        return response()->json([
            'status' => true
        ]);
    }

    public function ajaxCheckPromoCode(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['promocod' => 'required',]
        );

        if ($validator->fails()){
            return response()->json([
                'status' => false,
				//08.08.2025 - no messages, just false
                //'messages' => $validator->messages(),
            ]);
		}


        $user = app('global_user');
        $tip_price = 'b2c';
        $field_price = 'price';
        $field_price_promo = 'price_promo';

        $promocod = $request->input('promocod');
        $current_date = time();
        $cookie_basket = Cookie::get('basket');
        $cant_pentru_disc = 0;

        $goods_promo = GoodsPromo::where('promocod', $promocod)
            ->where('tip_price', '>=', $tip_price)
            ->first();

        if (!$goods_promo || !$cookie_basket)
            return response()->json([
                'status' => false,
                'message' => 'Scuze! Acest cod nu a fost găsit',
            ]);

        $data_start = strtotime($goods_promo->data_start);
        // $data_end = strtotime(date('Y-m-d', strtotime($goods_promo->data_end)).' 23:59:59');//до конца дня
        $data_end = strtotime($goods_promo->data_end);//31.03.2021 - оставили просто дату конца, т.к. на мд сайте идет внос конкретного времени

        if ($data_start > $current_date || $data_end < $current_date)//делаем проверку по дате здесь
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(266) . ' ' . date('d.m', $data_start) . '&ndash;' . date('d.m', $data_end),
            ]);

        $cant_pentru_disc = $goods_promo->cant_pentru_disc;

        $basket_items = Basket::where('basket_id', $cookie_basket)
            ->join('goods_item_id', 'goods_item_id.id', '=', 'basket.goods_item_id')
            ->where(function ($query) use ($field_price_promo) {
                $query->where($field_price_promo, '=', 0)
                    ->orWhereNull($field_price_promo);
            })
            ->when($cant_pentru_disc, function ($query, $cant_pentru_disc) {
                return $query->where('items_count', '>=', $cant_pentru_disc);
            })
            ->pluck('goods_one_c_code')
            ->toArray();

        $promo_items = GoodsPromoItems::where('goods_promo_id', $goods_promo->id)
            ->pluck('one_c_id')
            ->toArray();

        if (!empty($basket_items) && !empty($promo_items)) {
            $final_items = array_intersect($promo_items, $basket_items);
            if (!empty($final_items)) {//финально имеем список товаров, к которым применим код
                if ($goods_promo->discount_procent > 0 || $goods_promo->discount_summa > 0) {//если процент скидки или сумма скидки
                    Basket::where('basket_id', $cookie_basket)
                        ->whereIn('goods_one_c_code', $final_items)
                        ->update([
                            'promo_one_c_id' => $goods_promo->one_c_id,
                            'discount_procent' => $goods_promo->discount_procent,
                            'discount_summa' => $goods_promo->discount_summa
                        ]);
                } elseif ($goods_promo->cant_cadou > 0) {//если подарок
                    $goods_cadou = GoodsPromoItems::where('goods_promo_id', $goods_promo->id)
                        ->where('is_cadou', 1)
                        ->first();

                    if ($goods_cadou) {
                        Basket::where('basket_id', $cookie_basket)
                            ->whereIn('goods_one_c_code', $final_items)
                            ->update([
                                'promo_one_c_id' => $goods_promo->one_c_id,
                                'related_one_c_id' => $goods_cadou->one_c_id,
                                'has_cadou' => 1
                            ]);
                    }
                }
            } else
                return response()->json([
                    'status' => false,
                    'message' => ShowLabelById(267),
                ]);
        }

        $link = $request->input('link') == 'checkout' ? 'checkout' : 'cart';

        return response()->json([
            'status' => true,
            'message' => ShowLabelById(268),
            'redirect' => url(LANG, $link)
        ]);

    }

    public function ajaxSelectPromoGift(Request $request)
    {
        $cookie_basket = Cookie::get('basket');
        $goods_id_related = $request->input('goods_id_related');
        $goods_promo_id = $request->input('goods_promo_id');
        $basket_id = $request->input('basket_id');

        $goods_promo = GoodsPromo::where('id', $goods_promo_id)->first();

        $current_basket = Basket::where('id', $basket_id)
            ->where('basket_id', $cookie_basket)
            ->first();

        if ($current_basket && $current_basket->items_count >= $goods_promo->cant_pentru_disc) {
            Basket::where('id', $basket_id)->update([
                'related_one_c_id' => $goods_id_related,
                'promo_one_c_id' => $goods_promo_id,
                'has_cadou' => 1
            ]);

            return response()->json([
                'status' => true,
                'message' => ShowLabelById(269)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => ShowLabelById(270)
        ]);
    }

    public function ajaxChangeDeliveryMethod(Request $request)
    {
        $user = app('global_user');
        $delivery_method = $request->input('delivery_method');
        $total_price = $request->input('total_price');

        if ($delivery_method == 'pickup'){
            $costul_livrarei = 0;
        }else{
            if (($user && $user->district_id > 0 && $user->district_id == 2182) || !$user){ //2182 Chisinau
                $costul_livrarei = config('custom.front.delivery_price_chisinau');
            }else{
                $costul_livrarei = config('custom.front.delivery_price_moldova');
            }            
            $pina_livrare = config('custom.front.until_free_delivery');
            $pina_livrare = $pina_livrare - $total_price;

            if ($pina_livrare <= 0) {
                $pina_livrare = 0;
                $costul_livrarei = 0;
            }
        }

        return response()->json([
            'status' => true,
            'costul_livrarei' => $costul_livrarei,
            'total_price' => $total_price + $costul_livrarei
        ]);
    }

    public function ajaxSelectDistrict(Request $request)
    {
        //$cookie_basket = Cookie::get('basket');
        $district_id = intval($request->input('district_id'));
        $total_price = intval($request->input('total_price'));

        $district = DeliveryState::where('active', 1)
            ->where('id', $district_id)
            ->first();

        if (!$district)
            return response()->json([
                'status' => false,
                'text' => 'District not found'
            ]);

        if ($district->code == 'CU')
            $costul_livrarei = config('custom.front.delivery_price_chisinau');
        else
            $costul_livrarei = config('custom.front.delivery_price_moldova');

        if ($district->is_transnistria == 1)
            $show_currency_message = 1;
        else
            $show_currency_message = 0;

        return response()->json([
            'status' => true,
            'costul_livrarei' => $costul_livrarei,
            'total_price' => $total_price + $costul_livrarei,
            'show_currency_message' => $show_currency_message,
        ]);
    }

}

