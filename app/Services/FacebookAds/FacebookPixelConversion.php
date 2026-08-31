<?php

namespace App\Services\FacebookAds;

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

class FacebookPixelConversion
{
     public static function pixelEvent($fb_pixel_event, $goods_collect): ?\FacebookAds\Object\ServerSide\EventResponse
     {
         $user = app('global_user');

         $access_token = config('services.facebook_pixel.facebook_pixel_access_token');
         $pixel_id =  config('services.facebook_pixel.facebook_pixel_id');

         // Без ключей пикселя (локальная разработка, тестовый стенд) событие не отправляем:
         // иначе SDK падает на пустом pixel_id и роняет страницу товара целиком.
         if (empty($pixel_id) || empty($access_token)) {
             return null;
         }

         $api = Api::init(null, null, $access_token);
         $api->setLogger(new CurlLogger());

         $check_number = $user ? str_replace([' ','(',')',':','+','-'],'', $user->phone) : null;
         $hash_number = $check_number ? hash('sha256', $check_number) : null;

         $check_email = $user ? trim(strtolower($user->email)) : null;
         $hash_email =  $check_email ? hash('sha256', $check_email) : null;

         $custom_data = null;
         $user_data = (new UserData())
             ->setEmail($hash_email)
             ->setPhone($hash_number)
             // It is recommended to send Client IP and User Agent for Conversions API Events.
             ->setClientIpAddress($_SERVER['REMOTE_ADDR'])
             ->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

         //->setFbc('fb.1.1554763741205.AbCdEfGhIjKlMnOpQrStUvWxYz1234567890')
         //->setFbp('fb.1.1558571054389.1098115397');

         /*$content = (new Content())
             ->setProductId($goods_item->one_c_code)
             ->setQuantity($goods_item->products_count)
             ->setItemPrice($item_price)
             ->setDescription($goods_item->body)
             ->setBrand($goods_item->brand_nav_name)
             ->setCategory($goods_item->subject_nav_name);*/
         //->setDeliveryCategory(DeliveryCategory::HOME_DELIVERY);

         $event = null;
         $event_type = null;
         $event_source_url = null;
         $event_id = null;

         switch ($fb_pixel_event) {
             case 'ViewContent':
                 $goods_item = $goods_collect->goods_item;
                 $custom_data = (new CustomData())
                     //->setContents(array($content))
                     ->setContentName($goods_item->itemByLang->name)
                     ->setContentCategory($goods_item->itemByLang->subject_nav_name)
                     ->setContentIds($goods_item->one_c_code)
                     ->setNumItems($goods_item->products_count)
                     ->setContentType('product')
                     ->setCurrency('MDL')
                     ->setValue($goods_collect->goods_price);

                 //$event_id = $goods_item_event_id;
                 $event_type = 'ViewContent';
                 $event_source_url = route('catalog-product', ['product', $goods_item->alias]);

                 break;

             case 'AddToCart':

                 $goods_item = $goods_collect->goods_item;
                 $custom_data = (new CustomData())
                     //->setContents(array($content))
                     ->setContentName($goods_item->itemByLang->name)
                     ->setContentCategory($goods_item->itemByLang->subject_nav_name)
                     ->setContentIds($goods_item->one_c_code)
                     ->setContentType('product')
                     ->setCurrency('MDL')
                     ->setValue($goods_collect->goods_price);

                 //$event_id = $goods_item_event_id;
                 $event_type = 'AddToCart';
                 $event_source_url = route('catalog-product', ['product', $goods_item->alias]);

                 break;

             case 'AddToWishlist':

                 $goods_item = $goods_collect->goods_item;
                 $custom_data = (new CustomData())
                     //->setContents(array($content))
                     ->setContentName($goods_item->itemByLang->name)
                     ->setContentCategory($goods_item->itemByLang->subject_nav_name)
                     ->setContentIds($goods_item->one_c_code)
                     ->setContentType('product')
                     ->setCurrency('MDL')
                     ->setValue($goods_collect->goods_price);

                 //$event_id = $goods_item_event_id;
                 $event_type = 'AddToWishlist';
                 $event_source_url = route('catalog-product', ['product', $goods_item->alias]);

                 break;

             case 'Search':
                 $search_string = $goods_collect->search_string;
                 $content_category = $goods_collect->content_category;
                 $content_items_ids = $goods_collect->content_ids;
                 $custom_data = (new CustomData())
                     //->setContents(array($content))
                     ->setSearchString($search_string)
                     ->setContentCategory($content_category)
                     ->setContentIds($content_items_ids);

                 //$event_id = $goods_item_event_id;
                 $event_type = 'Search';
                 $event_source_url = route('catalog-product').'?s';

                 break;

             case 'InitiateCheckout':
                 $basket_items_ids = $goods_collect->content_ids;
                 /*$basket_items = $goods_collect->contents;*/
                 $basket_items_count = $goods_collect->num_items;
                 $basket_price_value = $goods_collect->value;
                 $basket_items = collect($goods_collect->contents)->map(function($item){
                    $itemContent =  (new Content())
                        ->setProductId($item['id'])
                        ->setQuantity($item['quantity'])
                        ->setItemPrice($item['item_price']);
                    return $itemContent;
                 })->toArray();

                 $custom_data = (new CustomData())
                     ->setContentIds($basket_items_ids)
                     ->setNumItems($basket_items_count)
                     ->setContents($basket_items)
                     ->setContentType('product')
                     ->setCurrency('MDL')
                     ->setValue($basket_price_value);

                 $event_type = 'InitiateCheckout';
                 $event_source_url = route('checkout');

                 break;

             case 'Purchase':
                 $basket_items_ids = $goods_collect->content_ids;
                 /*$basket_items = $goods_collect->contents;*/
                 $basket_items_count = $goods_collect->num_items;
                 $basket_price_value = $goods_collect->value;
                 $basket_items = collect($goods_collect->contents)->map(function($item){
                     $itemContent =  (new Content())
                         ->setProductId($item['id'])
                         ->setQuantity($item['quantity'])
                         ->setItemPrice($item['item_price']);
                     return $itemContent;
                 })->toArray();

                 $custom_data = (new CustomData())
                     ->setContentIds($basket_items_ids)
                     ->setNumItems($basket_items_count)
                     ->setContents($basket_items)
                     ->setContentType('product')
                     ->setCurrency('MDL')
                     ->setValue($basket_price_value);

                 $event_type = 'Purchase';
                 $event_source_url = 'checkout-success';

                 break;

             default:
                 break;
         }

         $event = (new Event())
             ->setEventName($event_type)
             //->setEventId($event_id) //добавлено
             ->setEventTime(time())
             ->setEventSourceUrl($event_source_url)
             ->setUserData($user_data)
             ->setCustomData($custom_data)
             ->setActionSource(ActionSource::WEBSITE);

         $events = array();
         array_push($events, $event);

         $request = (new EventRequest($pixel_id))
             ->setTestEventCode(config('services.facebook_pixel.facebook_test_event_code'))
             ->setEvents($events);

         $response = $request->execute();

         return $response;
     }

}
