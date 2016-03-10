<?php
define('__ROOT8__', dirname(dirname(__FILE__)));
require_once (__ROOT8__ . '/app/app_functions.php');

function checkOrdersExisted($vendor_order_no,$vendor_order_no_extra){
	global $mysqli;
	
	$result = false;
  
	$sql2 = 'SELECT count(id) as total, order_id, morningstage FROM Orders where vendor_order_no = ? and vendor_order_no_extra = ?';

	$stmt2 = $mysqli->prepare($sql2); 
	$stmt2->bind_param('ss',$vendor_order_no , $vendor_order_no_extra);

	$stmt2->execute();
	$stmt2->bind_result($total,$order_id,$morningstage);
	$stmt2->fetch();
	$stmt2->close();

	if ($total > 0) {
		$result = true;
	}

	return array('result' => $result,'order_id'=>$order_id,'morningstage'=>$morningstage);
}


function appOrderSaveToDB($order_time,
	$product_name,
	$product_rank,
	$product_quantity,
	$product_price,
	$product_cost,
	$product_id,
	$username,
	$email,
	$phone,
	$vendor_order_no,
	$pay_type,
	$order_amount,
	$shipping_fee,
	$vendor_order_no_extra,
	$morningstage,
	$order_type,
	$order_from,
	$address,
	$city,
	$zipcode,
	$district,
	$order_device,
	$appMemberid,
	$storage_id){

	global $mysqli;

	//if order is ship but not process in 91 app
	// $duplicate_data = checkOrdersExisted($vendor_order_no,$vendor_order_no_extra);

	// // print_r($duplicate_data);

	// if ($duplicate_data['result']){
		
	// 	if($duplicate_data['order_id'] && strcmp($duplicate_data['morningstage'],'ship')==0){
	// 			// echo $duplicate_data['order_id']."<br>";
	// 			// echo $duplicate_data['morningstage']."<br>";
	// 		 	deliveryShipment(1993,$vendor_order_no,array(),8,$duplicate_data['order_id']);
	// 	}

	// 	return null;

	// }

	//get products
	if (is_null($product_cost) || $product_cost == 0) {
		$sql2 = 'SELECT p.cost From product p where p.storage_id = ?';

		$stmt2 = $mysqli->prepare($sql2); 
		$stmt2->bind_param('s',$storage_id);

		$stmt2->execute();
		$stmt2->bind_result($product_cost);
		$stmt2->fetch();
		$product_cost = $product_cost==""||!$product_cost||$product_cost=="NULL"||$product_cost=="0"?((int)$product_price)*0.68:$product_cost;
		$stmt2->close();
	}
	

	
	$sql = "INSERT INTO Orders (order_id, status,order_time,ship_time,arrive_time, product_name, product_rank, product_quantity,product_price,product_cost,product_id,username,email,phone,vendor_order_no,pay_type,order_amount,shipping_fee,vendor_order_no_extra,morningstage,order_type,address,city,zipcode,district,order_from, order_device, appmemberid,storage_id) VALUES (?,?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE morningstage = ?;";
    $stmt = $mysqli->prepare($sql); 
    $stmt->bind_param('ssssssddddssssssddssssssssssss',$vendor_order_no,$b='',$order_time,$c='',$d='', $product_name, $product_rank, $product_quantity,$product_price,$product_cost,$product_id,$username,$email, $phone,$vendor_order_no,$pay_type,$order_amount,$shipping_fee,$vendor_order_no_extra,$morningstage,$order_type,$address,$city,$zipcode,$district,$order_from,$order_device,$appMemberid,$morningstage,$storage_id);

    $stmt->execute(); 

    printf($stmt->error);
    $stmt->close();

}

function importOrdersfrom91app($position,$count){
	
	$shopId=1993;
	$orderDeliverType="";
	$orderDateType="OrderDateTime";
	$startDate = (new DateTime())->modify('-1 days')->format('Y-m-d');
	$endDate = (new DateTime())->modify('+1 days')->format('Y-m-d');
	$orderStatus="";
	// $position=$position;
	// $count=$count;
	$ShippingOrderStatus="";
	$orders = array();

	$orders = getAppOrders($shopId,$orderDeliverType,$orderDateType,$startDate,$endDate,$orderStatus,$position,$count,$ShippingOrderStatus);

	foreach ($orders as $v) {
		// $inputdata = array(
	    //     array(
	    //             "vendor_order_no" => "test",
	    //             "pay_type" => "DS",
	    //             "order_amount" => "180", 
	    //             "buyer_email" => "jeffreywu@ouregion.com",
	    //             "delivery" => array('r_name' => '小霸王' ,
	    //                                 'r_mobile_country_code' => '886',
	    //                                 'r_mobile' => '0920935711',
	    //                                 'r_country' => '台灣',
	    //                                 'r_state' => '台灣' ,
	    //                                 'r_city' => '台北',
	    //                                 'r_postcode' => '110',
	    //                                 'r_address1' => '基隆路一段180號7樓'

	    //                 ),
	    //             'product' => array(
	    //                             array(  'uitox_product_id' => '201411AG200001615', 
	    //                                     'price' => '100',
	    //                                     'cost' => '70',
	    //                                     'sale_qty' => '1'

	    //                                 )
	    //                             ),
	    //             'shipping_fee' => '80',
	    //             'invoice_request' => '4'

	    //         )
	    //     );

		$order_time = $v->OrderDateTime;
		$product_id = getProductIdbyOuterId($v->OuterId);
		$product_info = getProductMain($product_id);
		$product_name = $product_info->Title;
		$product_rank = '';
		$product_quantity = $v->Qty;
		$product_price = $v->Price;
		$product_cost = $product_info->Cost;
		$storage_id = $v->OuterId;
		$username = $v->OrderReceiverName;
		$email = @$v->email;
		$email = !$email?'temp@no.email':$email;
		$phone = $v->OrderReceiverMobile;
		$vendor_order_no = $v->TMCode;
		$pay_type = $v->OrderPayType;
		$order_amount = $v->TotalPayment;
		$shipping_fee = $v->TMShippingFee;
		$vendor_order_no_extra = $v->TSCode;
		// $invoice_print_detail = '$v->'
		// $invoice_donate_unit = $v->
		// $invoice_device_no = $v->
		// $invoice_number = $v->
		// $invoice_date = '';
		// $invoice_time = '';
		// $buyer_identifier = $v->
		$morningstage = $v->OrderStatus;
		$order_type = $v->OrderShippingType;
		$order_from = 'app';
		$order_device = $v->OrderSource;
		$address = $v->OrderReceiverAddress;
		$city = $v->OrderReceiverCity;
		$zipcode = $v->OrderReceiverZipCode;
		$district = $v->OrderReceiverDistrict;
		$appMemberid = $v->MemberCode;

		appOrderSaveToDB($order_time,$product_name,$product_rank,$product_quantity,$product_price,$product_cost,$product_id,$username,$email,$phone,$vendor_order_no,$pay_type,$order_amount,$shipping_fee,$vendor_order_no_extra,$morningstage,$order_type,$order_from,$address,$city,$zipcode,$district,$order_device,$appMemberid,$storage_id);
	}
}

$shopId=1993;
$orderDeliverType="";
$orderDateType="OrderDateTime";
$startDate = (new DateTime())->modify('-1 days')->format('Y-m-d');
$endDate = (new DateTime())->modify('+1 days')->format('Y-m-d');
$orderStatus="";
$position=0;
$count=100;
$ShippingOrderStatus="";
$ordersCount = getAppOrdersCount($shopId,$orderDeliverType,$orderDateType,$startDate,$endDate,$orderStatus,$position,$count,$ShippingOrderStatus);


if ($ordersCount) {
	for ($i=0; $i < $ordersCount; $i+=100) { 
		$products_temp = importOrdersfrom91app($i,100);
		
	}	
}


//clear the cache first
if (!isset($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = "www.monringshop.tw";
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/ppp.php');
curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( "url"=>'http://'.$_SERVER['HTTP_HOST'].'/')));
curl_exec($ch);
curl_close($ch);

?>