<?php
require_once '../app/app_functions.php';

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
	$order_device){

	global $mysqli;

	//get products
	$sql2 = 'SELECT p.cost,p.product_name From product p where p.product_id = ?';

	$stmt2 = $mysqli->prepare($sql2); 
	$stmt2->bind_param('s',$product_id);

	$stmt2->execute();
	$stmt2->bind_result($product_cost,$product_name);
	$stmt2->fetch();
	$product_cost = $product_cost==""||!$product_cost||$product_cost=="NULL"||$product_cost=="0"?((int)$product_price)*0.68:$product_cost;
	$stmt2->close();

	
	$sql = "INSERT INTO Orders (order_id,status,order_time,ship_time,arrive_time, product_name, product_rank, product_quantity,product_price,product_cost,product_id,username,email,phone,vendor_order_no,pay_type,order_amount,shipping_fee,vendor_order_no_extra,morningstage,order_type,address,city,zipcode,district,order_from, order_device) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($sql); 
    $stmt->bind_param('ssssssddddssssssddsssssssss',$a='',$b='',$order_time,$c='',$d='', $product_name, $product_rank, $product_quantity,$product_price,$product_cost,$product_id,$username,$email, $phone,$vendor_order_no,$pay_type,$order_amount,$shipping_fee,$vendor_order_no_extra,$morningstage,$order_type,$address,$city,$zipcode,$district,$order_from,$order_device);

    $stmt->execute(); 

    printf($stmt->error);
    $stmt->close();

}


$shopId=1993;
$orderDeliverType="Home";
$orderDateType="OrderDateTime";
$startDate = (new DateTime())->modify('-5 days')->format('Y-m-d');
$endDate = (new DateTime())->modify('+1 days')->format('Y-m-d');
$orderStatus="WaitingToShipping";
$position=0;
$count=100;
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
	$product_name = '';
	$product_rank = '';
	$product_quantity = $v->Qty;
	$product_price = $v->Price;
	$product_cost = null;
	$product_id = $v->OuterId;
	$username = $v->OrderReceiverName;
	$email = $v->email;
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
	$morningstage = 'pending';
	$order_type = $v->OrderShippingType;
	$order_from = 'app';
	$order_device = $v->OrderSource;
	$address = $v->OrderReceiverAddress;
	$city = $v->OrderReceiverCity;
	$zipcode = $v->OrderReceiverZipCode;
	$district = $v->OrderReceiverDistrict;
	
	appOrderSaveToDB($order_time,$product_name,$product_rank,$product_quantity,$product_price,$product_cost,$product_id,$username,$email,$phone,$vendor_order_no,$pay_type,$order_amount,$shipping_fee,$vendor_order_no_extra,$morningstage,$order_type,$order_from,$address,$city,$zipcode,$district,$order_device);
}
?>