<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\GoodsItemId;
use App\Models\GoodsPhoto;
use Illuminate\Http\Request;

class GFGoodsXmlController extends Controller
{
    public function generateGFGoodsXML(Request $request)
    {
        //$all_b2c = false;
        $all_b2c_estel = false;
        $all_b2c_abril_et_nature = false;
        $all_b2c_cotril = false;
        $all_b2c_runail = false;
        $xml_file_name = '';
        switch ($request->input('list')) {
            case 'all_b2c':
                //$all_b2c = true;
                $xml_file_name = 'all_b2c.xml';
                break;
            case 'all_b2c_estel':
                $all_b2c_estel = true;
                $xml_file_name = 'all_b2c_estel.xml';
                break;
            case 'all_b2c_abril_et_nature':
                $all_b2c_abril_et_nature = true;
                $xml_file_name = 'all_b2c_abril_et_nature.xml';
                break;
            case 'all_b2c_cotril':
                $all_b2c_cotril = true;
                $xml_file_name = 'all_b2c_cotril.xml';
                break;
            case 'all_b2c_runail':
                $all_b2c_runail = true;
                $xml_file_name = 'all_b2c_runail.xml';
                break;
            default:
                $xml_file_name = 'gf_goods.xml';
                break;
        }

        $shop_name = "Efrumos Moldova Magazin Online";
        $shop_link = env('APP_URL');

        $goods = GoodsItemId::where('goods_item_id.active', 1)
            ->where('goods_item_id.deleted', 0)
            ->where('goods_brand_id.active', 1)
            ->where('goods_brand_id.deleted', 0)
            ->join('goods_item', 'goods_item.goods_item_id', '=', 'goods_item_id.id')
            ->join('goods_brand_id', 'goods_brand_id.id', '=', 'goods_item_id.brand_id')
            ->where('lang_id', LANG_ID)
            ->when($all_b2c_estel, function ($query) {
                $query->where(function ($q) {
                    $q->whereNotIn('goods_brand_id.p_id', [320, 340])
                        ->whereNotIn('goods_brand_id.id', [320, 340]); //320 - Abril et Nature, 340 - Cotril
                });
            })
            ->when($all_b2c_abril_et_nature, function ($query) {
                $query->where(function ($q) {
                    $q->where('goods_brand_id.id', 320)
                        ->orWhere('goods_brand_id.p_id', 320); //320 - Abril et Nature,
                });
            })
            ->when($all_b2c_cotril, function ($query) {
                $query->where(function ($q) {
                    $q->where('goods_brand_id.id', 340)
                        ->orWhere('goods_brand_id.p_id', 340); //340 - Cotril,
                });
            })
            ->when($all_b2c_runail, function ($query) {
                $query->where(function ($q) {
                    $q->where('goods_brand_id.id', 390)
                        ->orWhere('goods_brand_id.p_id', 390); //340 - Cotril,
                });
            })
            ->where('b2b_type', 'all')
            ->select('*', 'goods_item.body', 'goods_brand_id.p_id', 'goods_brand_id.id', 'goods_item_id.id as id', 'goods_item_id.alias as alias')
            ->orderBy('price', 'asc')
            ->get();


        $feed_goods = [];

        if (!empty($goods) && count($goods)) {

            foreach ($goods as $one_goods) {
                $gf_goods = [];
                $images = [];

                $photos = GoodsPhoto::where('active', 1)
                    ->where('goods_item_id', $one_goods->id)
                    ->orderBy('position', 'asc')
                    ->get();

                if ($photos) {
                    foreach ($photos as $key => $one_photo) {
                        if ($key == 0)
                            continue;

                        $images[] = asset('upfiles/goods-items/' . $one_photo->img);
                    }
                }

                $goods_price_collect = getGoodsPrice($one_goods);

                $gf_goods['g:id'] = $one_goods->one_c_code;
                $gf_goods['g:title'] = $one_goods->name;
                $gf_goods['g:description'] = strip_tags($one_goods->body);
                $gf_goods['g:link'] = route('catalog-product', ['product', $one_goods->alias]);
                $gf_goods['g:image_link'] = $one_goods->oImage && $one_goods->oImage->img ? env('APP_URL') . '/upfiles/goods-items/' . $one_goods->oImage->img : '';
                $gf_goods['g:availability'] = $one_goods->in_stoc == 1 ? 'in stock' : 'out of stock';
                $gf_goods['g:condition'] = 'new';
                $gf_goods['g:price'] = priceFormatForGA4($goods_price_collect->price_default);
                if ($one_goods->price_promo > 0) {
                    $gf_goods['g:sale_price'] = priceFormatForGA4($goods_price_collect->price_promo);
                }
                $gf_goods['g:product_type'] = IfHasName($one_goods->goods_type_id, LANG_ID, 'goods_type');
                $gf_goods['g:google_product_category'] = str_replace('/', ' &gt; ', $one_goods->subject_nav_name);
                $gf_goods['g:brand'] = $one_goods->brand_nav_name;
                $gf_goods['g:status'] = $one_goods->active == 1 ? 'active' : 'archived';
                $gf_goods['g:custom_label_0'] = $one_goods->b2b_type == 'all' ? 'all' : 'b2b';
                $gf_goods['g:custom_label_1'] = $one_goods->subject_nav_name;
                if ($images && count($images) > 0) {
                    $images_to_string = implode(',', $images);
                    $gf_goods['g:additional_image_link'] = $images_to_string;
                }

                $feed_goods[] = $gf_goods;
            }

            //Generate XML
            $doc = new \DOMDocument('1.0', 'UTF-8');

            $xmlRoot = $doc->createElement("rss");
            $xmlRoot = $doc->appendChild($xmlRoot);

            $xmlRoot->setAttribute('version', '2.0');
            $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', "http://base.google.com/ns/1.0");

            $channelNode = $xmlRoot->appendChild($doc->createElement('channel'));

            $channelNode->appendChild($doc->createElement('title', $shop_name));

            $channelNode->appendChild($doc->createElement('link', $shop_link));

            foreach ($feed_goods as $product) {
                $itemNode = $channelNode->appendChild($doc->createElement('item'));

                foreach ($product as $key => $value) {
                    if ($value != "") {

                        if (is_array($product[$key])) {

                            $subItemNode = $itemNode->appendChild($doc->createElement($key));
                            foreach ($product[$key] as $key2 => $value2) {
                                $subItemNode->appendChild($doc->createElement($key2))->appendChild($doc->createTextNode($value2));
                            }
                        } else {
                            $itemNode->appendChild($doc->createElement($key))->appendChild($doc->createTextNode($value));
                        }

                    } else {
                        $itemNode->appendChild($doc->createElement($key));
                    }
                }
            }

            $doc->formatOutput = true;
            //echo $doc->saveXML();
            $xml_string = $doc->saveXML();

            $create_xml_file = fopen("xml/" . $xml_file_name, "w");
            fwrite($create_xml_file, $xml_string);
            fclose($create_xml_file);

            echo 'Feed goods generated successfully in xml!';
            // return response()->download('xml/'.$xml_file_name);
        }
    }


}

