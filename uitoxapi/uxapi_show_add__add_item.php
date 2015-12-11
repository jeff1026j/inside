<?php
require_once 'uxapi_pub.php';

//=========================================
// 新增商品
//=========================================
$uxapi = new UITOX_API;
$uxapi->set_test_env();


$api = '/show_add/add_item';
$api_version = '1.0.0';
$data = array(
			'count' => '1',
			'add_new_product' => array(
                                    array(
                                            'item_name'         => 'morningshop測試商品',
                                            'item_color'        => '白',
                                            'item_size'         => 'Free',
                                            'item_spec_seq'     => '201505250003',
                                            'insurance_val'     => '100',
                                            'own_product_id'    => '201505250003',
                                            'own_custom_no'     => '201505250003',
                                            'uitox_supplier_id' => 'AJ5064',
                                            'max_sale_qty'      => '30'
                                           ),

                                 )
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

//end file