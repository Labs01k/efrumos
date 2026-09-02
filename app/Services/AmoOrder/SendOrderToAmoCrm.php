<?php

namespace App\Services\AmoOrder;

use App\Models\Basket;
use App\Models\GoodsPromo;

class SendOrderToAmoCrm
{
    public function sendOrderToAmoCrm($order_new, $orders_data, $orders_users, $user_info, $user_district = null){

        $basket = Basket::where('basket_id', $order_new->basket_id)->get();

        $products = [];
        $find_promocode = '';
        if (!empty($basket) && count($basket)) {
            foreach ($basket as $key => $one_basket_item) {

                if ($one_basket_item->promo_one_c_id > 0) {
                    $find_promocode = GoodsPromo::where('id', $one_basket_item->promo_one_c_id)
                        ->where('promo_type', 4)
                        ->value('promocod');
                }

                $products[$key] = [
                    'sku' => $one_basket_item->goods_one_c_code,
                    'name' => $one_basket_item->goods_name,
                    'price' => $one_basket_item->goods_price,
                    'quantity' => $one_basket_item->items_count,
                    'discount' => '',
                    'discount_type' => '',
                ];
            }
        }

        if($order_new->fast_order == 1){
            $order_type = 'Comanda noua (Rapidă)';
            $tags_type = 'site, orders, efrumos, comanda rapida efrumos.md';
        }else{
            $order_type = 'Comanda noua (Simplă)';
            $tags_type = 'site, orders, efrumos';
        }
        // было присваивание (=) — самовывоз всегда уходил в CRM как доставка
        if ($order_new->delivery_method == 'delivery') {
            $delivery_type = 'Livrare la domiciliu';
            $delivery_address_info = $user_district ? $user_district->name .' '. $user_info->city .' '. $user_info->address : '';
        }else {
            $delivery_type = 'Ridicare personală din magazinul Efrumos';
            // выбранный магазин самовывоза уходит в CRM адресом
            $pickup_shop = $order_new->pickupShop;
            $delivery_address_info = $pickup_shop && $pickup_shop->itemByLang
                ? trim(($pickup_shop->itemByLang->name ?? '') . ', ' . ($pickup_shop->itemByLang->address ?? ''), ', ')
                : '';
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://platon.progression.md/v4/app/sale.order.create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);

        $order_details_array = [
            //'token' => "6c39884c-8a46-40b4-9be8-d095054c2cd2",
            'token' => "2mi21q84zeah0b9m", //new - 21.05.24
            'account_id' => 29770027,
            'lead_name' => $order_type,
            'pipeline_id' => 4766755,
            'status_id' => 43499836,
            'responsible_user_id' => 7558222,
            'price' => 4320,
            'PromoCode' => $find_promocode ?: '',
            'tags' => $tags_type,
            'contact' => [
                'name' => $user_info->last_name .' '. $user_info->name,
                'phone' => $user_info->phone,
                'email' => $user_info->email,
                'fields' => [
                    47777 => "Ro"
                ]
            ],
            'order' => [
                'order_id' => $order_new->id,
                'sum' => $orders_data->total_price,
                'vat' => 2,
                'vat_included' => 1
            ],
            'fields' => [
                '47753' => $delivery_type,
                '47757' => $order_new->id,
                '47759' => $delivery_address_info,
                '47765' => "Cash la livrare",
                '47767' => $orders_users->descr ?: ""
            ],
            'rows' => $products,
            'stats' => [
                'FBCLID' => "",
                'FROM' => "",
                'GCLID' => "fdtdfats6rf67d5ffadf",
                'GCLIENTID' => "",
                'OPENSTAT_AD' => "",
                'OPENSTAT_CAMPAIGN' => "",
                'OPENSTAT_SERVICE' => "",
                'OPENSTAT_SOURCE' => "",
                'POSITION' => "",
                'REFERRER' => "",
                'ROISTAT' => "",
                'UTM_CAMPAIGN' => "google",
                'UTM_CONTENT' => "",
                'UTM_MEDIUM' => "mediu",
                'UTM_REFERRER' => "",
                'UTM_SOURCE' => "cpc",
                'UTM_TERM' => "",
                'WEB' => "",
                'YCLID' => "",
                '_YM_COUNTER' => "",
                '_YM_UID' => ""
            ]
        ];

        $order_details_json = json_encode($order_details_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $order_details_json);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        //$info = curl_getinfo($ch);
        //dd($info);
        // file_put_contents('test.txt', $response);
        curl_close($ch);
    }

}
