<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;
use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use App\Models\GoodsShopRest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportFrom1C extends Controller
{

    public $baseApiUrl;

    public function __construct()
    {
        $this->baseApiUrl = config('services.onec.wsdl_url');
    }

    public function fullExchange()
    {
        $this->getExchange(true);

        /*$logData = [
            'title' => 'Exchange:fullExchange()',
            'content' => "full exchange 1c data  ",
        ];

        $this->_logAny($logData);*/

    }

    public function onlyChangedExchange()
    {
        $this->getExchange(false);

        /*$logData = [
            'title' => 'Exchange:onlyChangedExchange()',
            'content' => "only changes in 1C exchange",
        ];

        $this->_logAny($logData);*/

    }

    public function fullExchangeDownload()
    {
        return $this->_getProductsArray(true, 'download');
    }

    public function fullExchangeUpdate()
    {
        return $this->getExchange(true, 'update');
    }

    public function _getProductsArray($type = false, $json_action = null)
    {
        //FullExchange update after download file
        if($type && $json_action == 'update'){

            //not working code 04.02.2026 - because bad json from 1C
            // $json_file = file_get_contents(public_path('upfiles/1c-files/big_full_exchange.json'));
            // $responseArrayData = json_decode($json_file, true);

            $path = public_path('upfiles/1c-files/big_full_exchange.json');
            $json_file = file_get_contents($path);

            // 1. УДАЛЕНИЕ BOM (Частая проблема 1С)
            // Удаляем невидимый маркер в начале файла, если он есть
            $bom = pack('H*','EFBBBF');
            $json_file = preg_replace("/^$bom/", '', $json_file);

            // 2. ПОПЫТКА СТАНДАРТНОЙ РАСШИФРОВКИ
            $responseArrayData = json_decode($json_file, true);

            // 3. ЕСЛИ ФАЙЛ БИТЫЙ ИЛИ ОБОРВАН (Rescue Mode)
            if ($responseArrayData === null) {
                // Если json_decode не справился, скорее всего файл оборван.
                // Мы можем извлечь все целые товары с помощью регулярного выражения.
                // Паттерн ищет блоки, начинающиеся с {"GUID" и заканчивающиеся закрытием массива остатков ]}
                // Это работает для структуры вашего файла.
                
                preg_match_all('/\{"GUID":.*?"Rests":\s*\[.*?\]\}/s', $json_file, $matches);
                
                if (!empty($matches[0])) {
                    $responseArrayData = [];
                    // Собираем валидные куски обратно в массив
                    foreach ($matches[0] as $itemJson) {
                        $decodedItem = json_decode($itemJson, true);
                        if ($decodedItem) {
                            $responseArrayData['data'][] = $decodedItem;
                        }
                    }
                    // Эмулируем структуру, как если бы файл был целый
                    if (!isset($responseArrayData['data'])) {
                         $responseArrayData = null; // Ничего не нашли
                    }
                }
            }

            if (!empty($responseArrayData) && isset($responseArrayData['data'])){
                return $responseArrayData['data'];
			}else{
				return false;
			}
        }

        ini_set('default_socket_timeout', 50000);
        $connectParams = [
            "ПространствоИмен" => "http://localhost/",
            "ИмяСервиса" => "WS_sites",
            "ИмяТочкиПодключения" => "WS_sitesSoap",
            'trace' => true,
            'keep_alive' => true,
            'connection_timeout' => 50000,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        ];

        $soapclient = new \SoapClient($this->baseApiUrl, $connectParams);

        // ['FullExchange'=>true]   COMPLET or FALSE incomplet
        $response = $soapclient->GetSKU(['FullExchange' => $type, 'SiteType' => 'EF']);

        if (!empty($response->return)) {
			
			$json_code = preg_replace("/\r|\n|\r\n/", "", $response->return);

            //FullExchange download big file
            if ($type && $json_action == 'download') {
                return file_put_contents(public_path('upfiles/1c-files/big_full_exchange.json'),$json_code);
            }

            $responseArrayData = json_decode($json_code, true);
            if (!empty($responseArrayData) && isset($responseArrayData['data']))
                return $responseArrayData['data'];
        } else {
            dd('#58 RESPONSE SOAP: ');
        }
        return [];
    }

    public function getExchange($type = false, $json_action = null)
    {
        // var_dump('CONNECT SOAP WSDL');
        $data = $this->_getProductsArray($type, $json_action);

        if (!empty($data) && count($data)) {

            foreach ($data as $item) {

                $goods_item_id = GoodsItemId::where('one_c_code', $item['Code1C'])->first();

                $total_rest_sum = 0;
                if (isset($item['Rests']) && !empty($item['Rests']) && count($item['Rests'])) {
                    foreach ($item['Rests'] as $one_store_rest) {
                        if ($one_store_rest['StoreId'] == config('custom.main_store_rest_id')) {
                            $total_rest_sum = $one_store_rest['Rest'];
                        }
                    }
                    //$total_rest_sum = collect($item['Rests'])->sum('Rest'); // for multiple stores
                }

                if (!is_null($goods_item_id)) {

                    $data = [
                        'one_c_code_guid' => $item['GUID'],
                        'articol' => $item['Articul'],
                        'barcode' => $item['BarCode'],
                        'price' => $item['MainPrice'],
                        'price_promo' => $item['Price'] < $item['MainPrice'] ? $item['Price'] : null,
                        'price_promo_date_end' => isset($item['DiscountAvailableTo']) ? Carbon::parse($item['DiscountAvailableTo'])->format('Y-m-d H:i:s') : null,
                        'products_count' => $total_rest_sum,
                        'in_stoc' => $total_rest_sum > 0 ? 1 : 0,
                        'gramaj' => $item['Vol'],
                    ];

                    $goods_item_id->update($data);

                    /*$data_ro = [
                        'name' => $item['DescriptionRom'],
                    ];

                    $data_ru = [
                        'name' => $item['DescriptionRu'],
                    ];

                    GoodsItem::where('lang_id', 2)->where('goods_item_id', $goods_item_id->id)->update($data_ro);
                    GoodsItem::where('lang_id', 3)->where('goods_item_id', $goods_item_id->id)->update($data_ru);*/

                } else {

                    $goods_alias = GoodsItemId::where('alias', Str::slug($item['DescriptionRom']))->first();

                    if (!is_null($goods_alias)) {
                        $alias = Str::slug($item['DescriptionRom']) . '-' . $item['Code1C'];
                    } else {
                        $alias = Str::slug($item['DescriptionRom']);
                    }

                    $position = GetMaxPosition('goods_item_id');

                    $data_id = [
                        'goods_subject_id' => 73248, //Produse nou
                        'alias' => $alias,
                        'one_c_code' => $item['Code1C'],
                        'one_c_code_guid' => $item['GUID'],
                        'articol' => $item['Articul'],
                        'barcode' => $item['BarCode'],
                        'price' => $item['MainPrice'],
                        'price_promo' => $item['Price'] < $item['MainPrice'] ? $item['Price'] : null,
                        'price_promo_date_end' => isset($item['DiscountAvailableTo']) ? Carbon::parse($item['DiscountAvailableTo'])->format('Y-m-d H:i:s') : null,
                        'products_count' => $total_rest_sum,
                        'in_stoc' => $total_rest_sum > 0 ? 1 : 0,
                        'gramaj' => $item['Vol'],
                        'position' => $position + 1,
                        'active' => 0,
                        'deleted' => 0,
                    ];

                    $goods_item_id = GoodsItemId::create($data_id);

                    if (!is_null($goods_item_id)) {

                        $data_ro = [
                            'goods_item_id' => $goods_item_id->id,
                            'lang_id' => 2,
                            'name' => $item['DescriptionRom'],
                        ];

                        $data_ru = [
                            'goods_item_id' => $goods_item_id->id,
                            'lang_id' => 3,
                            'name' => $item['DescriptionRu'],
                        ];

                        GoodsItem::create($data_ro);
                        GoodsItem::create($data_ru);
                    }
                }

                // п.5 ТЗ: разрез остатков по складам. Пока 1С шлёт только главный
                // склад — как только начнёт слать магазины, они попадут сюда сами.
                if (!is_null($goods_item_id)) {
                    $this->syncShopRests($goods_item_id->id, $item['Rests'] ?? []);
                }
            }
        } else {
            var_dump('NO PRODUCTS FOUND');
        }
    }

    /**
     * Сохраняет все строки Rests обмена (склад 1С + остаток) и удаляет строки
     * складов, которых в свежем обмене больше нет. StoreName пишется как есть —
     * по нему админ сопоставляет склад с магазином в разделе «Магазины».
     */
    public function syncShopRests(int $goods_item_id, array $rests): void
    {
        $store_guids = [];

        foreach ($rests as $one_store_rest) {
            if (empty($one_store_rest['StoreId'])) {
                continue;
            }

            $store_guids[] = $one_store_rest['StoreId'];

            GoodsShopRest::updateOrCreate([
                'goods_item_id' => $goods_item_id,
                'store_guid' => $one_store_rest['StoreId'],
            ], [
                'store_name' => $one_store_rest['StoreName'] ?? null,
                'qty' => $one_store_rest['Rest'] ?? 0,
            ]);
        }

        GoodsShopRest::where('goods_item_id', $goods_item_id)
            ->whereNotIn('store_guid', $store_guids)
            ->delete();
    }

}
