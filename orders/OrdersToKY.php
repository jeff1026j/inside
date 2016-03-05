<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');
require_once (__ROOT__ . '/src/Klaviyo.php');

function getOrdersforDaysKY($days){
	global $mysqli;
	$data = array();
	$tempOrderid = "";
	$tempOrder = array();

	  //count all orders and customers
	$sql = 'SELECT O1.phone,O1.order_id, O1.email,U.email as appemail, O1.username, O1.first_order_time,O1.last_order_interval,O1.product_name,O1.total_payment,O1.order_time, O1.appmemberid  FROM Orders O1 LEFT JOIN user U on U.appmemid = O1.appmemberid where order_time > DATE_SUB(curdate(), INTERVAL ? DAY) order by O1.order_id limit 40;';
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('d',$days);
    $stmt->execute();

	$stmt->bind_result($phone,$order_id, $email, $appemail, $username, $first_order_time, $last_order_interval, $product_name,$total_payment,$order_time,$appmemberid);
	
	while($stmt->fetch()){
		if ($tempOrderid == $order_id) { //Same Order
			$tempOrder['items'][] = $product_name;
		
		}else{ //New Order

			$data[] = $tempOrder;
			$tempOrderid = $order_id;	
			$tempOrder = array('phone'=>$phone,'order_id'=>$order_id,'email'=>$email,'appemail'=>$appemail,'username'=>$username,'first_order_time'=>$first_order_time,'last_order_interval'=>$last_order_interval,'total_payment'=>$total_payment,'order_time'=>$order_time,'appmemberid'=>$appmemberid,'items'=>array($product_name));
		}
		
	}
    $stmt->close();	
	return $data;
}  

$metric = "test1";
$tracker = new Klaviyo(kykey);

$orders = getOrdersforDaysKY(3);

foreach ($orders as $value) {
	$email = $value['email'] == "temp@no.email" ? $value['appemail']:$value['email'];
	$id = $value['phone'];
	$name = $value['username'];
	$phone = $value['phone'];
	$firstordertime = $value['first_order_time'];
	// $last_order_interval = $value['last_order_interval'];
	$items = $value['items'];
	$order_time = $value['order_time'];
	$amount = $value['total_payment'];
	$timestamp = strtotime($order_time);
	
	if ($email != "temp@no.email" && $email) {
		$tracker->track(
		    $metric,
		    array('$email' => $email,'$id' => $id , '$last_name' => $name,'$phone_number'=>$phone ,'First Order Time'=>$firstordertime,'Last Order Time' => $order_time),
		    array('Items' => $items,'Order Time'=>$order_time,'Order Amount'=>$amount),
		    $timestamp
		);	
	}
	
	
}

// Unique ID 0963100683
// Email Chris77621@gmail.com
// Last Name 陳家翔
// Phone 0963100683
// First Order Time January 07, 2015 at 08:00 AM
// Last Order Time January 07, 2015 at 09:00 AM

// Item SKU: ABC123
// Order Amount: 700
// Order Time: January 07, 2015 at 09:00 AM
// 


// echo json_encode();
?>