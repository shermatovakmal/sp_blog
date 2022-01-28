<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Orders
 * @package App\Models
 */
class Orders extends Model
{
    use HasFactory;
    private $baseUrl;
    private $apiKey;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->baseUrl = 'https://superposuda.retailcrm.ru';
        $this->apiKey = 'QlnRWTTWw9lv3kjxy1A8byjUmBQedYqb';
    }


    /**
     * @param $srchParamsArr
     * @return array
     */
    public function getProduct($srchParamsArr){

        $postFields = '';
        if(isset($srchParamsArr['article']) && $srchParamsArr['article'] != ''){
            $postFields .= 'filter%5Barticle%5D='.$srchParamsArr['article'];
        }
        if(isset($srchParamsArr['brend']) && $srchParamsArr['brend'] != ''){
            if($postFields != '') $postFields .= '&';

            $postFields .= 'filter%5Bbrend%5D='.$srchParamsArr['brend'];
        }

        $url = $this->baseUrl . '/api/v5/store/products';
        $response = $this->makeRequest($url, 'GET', $postFields);

        return $response;
    }

    /**
     * @param $addParams
     * @return array
     */
    public function orderCreate($addParams){

        $newOrder = $this->makeEmptyOrder();

        $newOrder->items[0]->offer->id = $addParams['offer']->id;
        $newOrder->items[0]->productName = $addParams['offer']->name;
        $newOrder->lastName     = $addParams['lname'];
        $newOrder->firstName    = $addParams['fname'];
        $newOrder->patronymic   = $addParams['pname'];
        $newOrder->customerComment = $addParams['comments'];

        $postFields = 'order='.json_encode($newOrder );

        $url = $this->baseUrl . '/api/v5/orders/create';
        $response = $this->makeRequest($url, 'POST', $postFields);

        return $response;
    }


    /**
     * @param $url
     * @param $method
     * @param $params
     * @return array
     */
    private function makeRequest($url, $method, $params){
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => array(
                    'X-API-KEY: ' . $this->apiKey,
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $retCode = array(
                'code' => 1,
                'text' => $response
            );
        }catch (\Exception $ex){
            $retCode = array(
                'code' => -1,
                'text' => $ex->getMessage()
            );
        }
        return $retCode;
    }

    private function makeEmptyOrder(){
        $emptyOrderParams = (object) array(
            'status' => 'trouble',
            'orderType' => 'fizik',
            'site' => 'test',
            'orderMethod' => 'test',
            'number' => date("YmdHis"),
            'lastName' => 'Shermatov',
            'firstName' => 'Akmal',
            'patronymic' => 'Akbarovich',
            'customerComment' => '',
            'items' => array());

        $emptyOrderParams->items[0] = (object) array(
            'offer' => (object) array('id' => -1),
            'productName' => 'Test');

        return $emptyOrderParams;
    }
}
