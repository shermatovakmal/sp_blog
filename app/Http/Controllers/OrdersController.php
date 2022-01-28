<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;

class OrdersController extends Controller
{
    public function create(){
        return view('orders/create');
    }

    public function store(Request $request)
    {
        $orders = new Orders();
        $filter = array(
            'article' => $request->article
        );

        try{
            $productResp = $orders->getProduct($filter);
            if(!isset($productResp['code']) || (isset($productResp['code']) && $productResp['code'] != 1) || !isset($productResp['text']))
                throw new \Exception('Invalid getProduct response');

            $productArr = json_decode($productResp['text']);
            if($productArr->success == 1 && isset($productArr->products) && count($productArr->products) > 0){
                $newProductArr = array(
                    'offer' => $productArr->products[0]->offers[0],
                    'fname' => $request->f_name,
                    'lname' => $request->l_name,
                    'pname' => $request->p_name,
                    'comments' => $request->comments
                );
                $orderCreateResp = $orders->orderCreate($newProductArr);
                if(!isset($orderCreateResp['code']) || (isset($orderCreateResp['code']) && $orderCreateResp['code'] != 1))
                    throw new \Exception('Invalid create Order response');

                $resp = json_decode($orderCreateResp['text']);

                if($resp->success){
                    $status = 'Success';
                    $msg = 'Номер заказа: '.$resp->id;
                }else{
                    throw new \Exception($resp->errorMsg);
                }
            }
        }catch (\Exception $ex){
            $status = 'Failed';
            $msg = $ex->getMessage();
        }

        return redirect('orders')->with('status', $msg);
    }
}
