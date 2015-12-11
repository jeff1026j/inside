<?php
define('__ROOT1__', dirname(dirname(__FILE__)));
require_once (__ROOT1__ . '/config/config_db.php');
require_once (__ROOT1__ . '/config/conn_db.php');
require_once (__ROOT1__ . '/config/deconfig.php');
require_once (__ROOT1__ . '/uitoxapi/uitox_functions.php');
require_once (__ROOT1__ . '/app/app_functions.php');

function payType($type){
	switch ($type) {
		case "CreditCardOnce":
			$type = "D";
			break;
		
		default:
			# code...
			break;
	}

	return $type;
}

//get return times and users from first month
$sql = 'SELECT * FROM Orders WHERE order_from= "app" and morningstage = "pending" and order_type = 1 group by vendor_order_no';

$stmt = $mysqli->query($sql); 
$orders = array();


while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
	// print_r($row);

		// {
//                 "vendor_order_no":"1511000001",
//                 "need_print_invoice":"N",
//                 "need_print_da_invoice":"",
//                 "shipping_type":"D",  
//                 "order_amount":"500",
//                 "order_src":"TW001",     


	$vendor_order_no =  $row['vendor_order_no'];
	$pay_type =  $row['pay_type'];
	$order_amount =  $row['order_amount'];
	$email =  $row['email'];
	$r_name =  $row['username'];
	$r_mobile_country_code =  '886';
	$r_mobile =  $row['phone'];
	$r_country =  '台灣';
	$r_state =  '台灣';
	$r_city =  $row['city'];
	$r_postcode =  $row['zipcode'];

	//記得處理地址長度超過情況
	$r_address1 =  $row['address'];
	$r_address2 =  '';
	$r_address3 =  '';

	$shipping_fee =  $row['shipping_fee'];
	$invoice_request = '4';

	$products = array();

	//get products
	$sql2 = 'SELECT O.product_id, O.product_price,O.order_amount, p.cost, O.product_quantity From Orders O, product p where p.product_id = O.product_id and vendor_order_no = ?';

	$stmt2 = $mysqli->prepare($sql2); 
	$stmt2->bind_param('s',$vendor_order_no);

	$stmt2->execute();
	$stmt2->bind_result($product_id,$product_price,$order_amount,$product_cost,$product_quantity);	
	
	$total_order_price = 0;
	$fake_order_price = 0;
	
	while($stmt2->fetch()){
		$total_order_price += $order_amount;
		$price = floor($order_amount/$product_quantity);
		$fake_order_price += $price * $product_quantity;
		$product_cost = $product_cost==""||!$product_cost||$product_cost=="NULL"||$product_cost=="0"?((int)$product_price)*0.68:$product_cost;
		//                     {
//                         "sno":"1",
//                         "uitox_product_id":"201504AG020000050",
//                         "price":"",
//                         "cost":"",
//                         "sale_qty":"1"
//                     }
		$products[] =  array('uitox_product_id'=>$product_id,'price'=>$price,'cost'=>$product_cost,'sale_qty'=>$product_quantity);		
	
	}

	// $shipping_fee += ($total_order_price - $fake_order_price);

	$total_order_price_final = $fake_order_price;
//                 "product_type":"U",
//                 "product_uitox":       
//                 [

//                 ],
//                 "delivery":   
//                 {
//                     "r_name":"張三",
//                     "r_mobile_country_code":"",
//                     "r_mobile":"0910123456",
//                     "r_localphone_country_code":"",
//                     "r_localphone":"",
//                     "r_country":"",
//                     "r_state":"",
//                     "r_city":"",
//                     "r_district":"",
//                     "r_postcode":"",
//                     "r_address1":"台北市忠孝東路四段563號8樓",
//                     "r_address2":"",
//                     "r_address3":""     
//                 }
//             }
	$orders[] = array(
	            "vendor_order_no" => $vendor_order_no,
	            "shipping_type" => payType($pay_type),
	            "buyer_name" => $r_name ,
                "buyer_email" => $email,
                "need_print_invoice" => "N",
				"need_print_da_invoice" => "",

	            "order_amount" => $total_order_price_final, 
	            "buyer_email" => $email,
	            "delivery" => array('r_name' => $r_name ,
	                                'r_mobile_country_code' => $r_mobile_country_code,
	                                'r_mobile' => $r_mobile,
	                                'r_country' => $r_country,
	                                'r_state' => $r_country ,
	                                'r_city' => $r_city,
	                                'r_postcode' => $r_postcode,
	                                'r_address1' => $r_address1

	                ),
	            "product_type"=>"U",
	            'product_uitox' => $products
	            // 'shipping_fee' => $shipping_fee,
	            // 'invoice_request' => $invoice_request

	    );
}

// echo json_encode($orders);

// get all the orders -> import to uitox
$import_orders = addNewOrderUITOX($orders);
// print_r($import_orders);

foreach ($import_orders as $value) {
	// print_r($value);
	// echo "<br>";
	updateOrderStatus('ship',$value);
 	deliveryShipment(1993,$value->vendor_order_no,array(),8,$value->uitox_order_no);
	
}

?>