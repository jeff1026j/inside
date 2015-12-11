<?php
require_once 'uxapi_pub.php';

//=========================================
// 新增商品
//=========================================

function getProductSaleQty($products){
    $uxapi = new UITOX_API;
    $uxapi->set_test_env();

    $inputdata = array();
    foreach ($products as $v) {
        if ($v) {
            $inputdata[]= array('uitox_product_id' => $v);
        }
    }

    $api = '/show_sale/get_item_qty_by_item';
    $api_version = '1.0.0';
    $data = array(
            'count' => count($inputdata),
            'saleable_qty' => $inputdata
    );
    $method = 'POST';


    try
    {
        $api_result = $uxapi->call_api($api, $api_version, $data, $method, $proxy); 
        print_r($api_result);
    }
    catch (Exception $e)
    {
        echo "error msg= " . $e->getMessage();
    }
}
$products = array('201504AG170000925','201505AG080001763','201504AG070000540');
getProductSaleQty($products);
//end file