<?php

namespace App\Http\Controllers\Exchange;

use App\Http\Controllers\Controller;

class ImportFrom1COld extends Controller
{

    // TEST SERVER 1C
    public $baseApiUrl = 'http://agent.solvex.md/test_db/ws/ws_ef.1cws?wsdl';

    //public $baseApiUrl = 'http://agent.solvex.md/svx/ws/ws_ef.1cws?wsdl';

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

    public function _getProductsArray($type = false)
    {
        ini_set('default_socket_timeout', 5000);
        $connectParams = [
            "ПространствоИмен" => "http://localhost/",
            "ИмяСервиса" => "WS_sites",
            "ИмяТочкиПодключения" => "WS_sitesSoap",
            'trace' => true,
            'keep_alive' => true,
            'connection_timeout' => 5000,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        ];

        $soapclient = new \SoapClient($this->baseApiUrl, $connectParams);

        // ['FullExchange'=>true]   COMPLET or FALSE incomplet
        $params = ['FullExchange' => $type];
        $response = $soapclient->GetSKU($params);

        if (!empty($response->return)) {
            $responseArrayData = json_decode($response->return, true);

            return $responseArrayData['data'];
        } else {
            var_dump('#58 RESPONSE SOAP: ');
            die;
        }
        return [];
    }

    public function getExchange($type = false)
    {
        var_dump('CONNECT SOAP WSDL');

        $data = $this->_getProductsArray($type);

        if (!empty($data)) {

            $countAll = count($data);
            $currentStep = 0;

            foreach ($data as $item) {

                $discountPercent = 0;
                if (!empty($item['Discount']) && (!empty($item['MainPrice']))) {

                    $discountPercent = round(100 - ($item['Price'] * 100 / $item['MainPrice']), 4);
                }

                $dataExchange = [
                    'guid' => $item['GUID'],
                    'description_ro' => $item['DescriptionRom'],
                    'description_ru' => $item['DescriptionRu'],
                    'articul' => $item['Articul'],
                    'volume' => $item['Vol'],
                    'main_price' => $item['MainPrice'],
                    'final_price' => $item['Price'],
                    'qty' => $item['Rest'],
                    'barcode' => $item['BarCode'],
                    'discount' => $item['Discount'],
                    'discount_prc' => $discountPercent,

                ];


                $tempItem = TmpExchangeModel::where('cod_1c', $item['Code1C'])->first();

                if (empty($tempItem)) {
                    $dataExchange['cod_1c'] = $item['Code1C'];
                    TmpExchangeModel::create($dataExchange);
                } else {

                    TmpExchangeModel::where('cod_1c', $item['Code1C'])->update($dataExchange);
                }


                $catalogItem = CatalogModel::where('cod_1c', $item['Code1C'])->first();

                if (empty($catalogItem)) {


                    $alias = AnyAlias::createTableAliasByString($item['DescriptionRom'], 'ut_catalog', 'alias');

                    $dataCatalogInsert = [
                        'price' => $item['MainPrice'],
                        'qty' => $item['Rest'],
                        'name' => $item['DescriptionRom'],
                        'alias' => $alias,
                        'name_ro' => $item['DescriptionRom'],
                        'name_ru' => $item['DescriptionRu'],
                        'sku' => $item['Code1C'],
                        'cod_1c' => $item['Code1C'],
                        'articol' => $item['Articul'],
                        'barcode' => $item['BarCode'],
                        'discount' => $discountPercent,
                        'status' => '2',
                    ];


                    CatalogModel::create($dataCatalogInsert);
                    var_dump('INSERT: ' . $item['Code1C']);
                } else {
                    $dataCatalogUpdate = [];

                    if ($catalogItem->price != $item['MainPrice']) {
                        $dataCatalogUpdate['price'] = (float)$item['MainPrice'];
                    }
                    $qty = (int)$catalogItem->qty;
                    if ($qty != (int)$item['Rest']) {
                        $dataCatalogUpdate['qty'] = $item['Rest'];
                    }

                    if ($catalogItem->discount != $discountPercent) {
                        $dataCatalogUpdate['discount'] = (float)$discountPercent;
                    }

                    // if discount less than 0
                    if ($discountPercent < 0) {
                        if ($catalogItem->price != $item['Price']) {
                            $dataCatalogUpdate['price'] = (float)$item['Price'];
                        }

                        if ($catalogItem->discount != 0) {
                            $dataCatalogUpdate['discount'] = 0;
                        }

                    }


                    if (!empty($dataCatalogUpdate)) {

                        CatalogModel::where('cod_1c', (string)$item['Code1C'])->update($dataCatalogUpdate);
                    }
                }

                $currentStep++;

                echo $this->progressBar($currentStep, $countAll, $item['Code1C'] . '     ');
            }
        } else {
            var_dump('NO PRODUCTS FOUND');
        }
    }


}
