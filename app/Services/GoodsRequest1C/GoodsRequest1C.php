<?php

namespace App\Services\GoodsRequest1C;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodsRequest1C
{
    /**
     * @param $type
     * @return array|mixed
     * @throws \SoapFault
     */
    public function _getProductsArray($goods_guid_array): mixed
    {
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

        $soapclient = new \SoapClient(config('services.onec.wsdl_url'), $connectParams);

        $response = $soapclient->GetSKUArray(['SKUArray' => $goods_guid_array, 'SiteType' => 'EF']);

        if (!empty($response->return)) {
            $responseArrayData = json_decode($response->return, true);
            // dd($responseArrayData['data']);

            if (!empty($responseArrayData) && isset($responseArrayData['data'])) return $responseArrayData['data'];
        } else {
            dd('#58 RESPONSE SOAP: ');
        }
        return [];
    }

    /**
     * @param $goods_one_c_code_array
     * @param $goods_action
     * @return void
     * @throws \SoapFault
     */
    public function actionRequestGoodsFrom1C($goods_guid_array)
    {
        return $this->_getProductsArray($goods_guid_array);
    }


}
