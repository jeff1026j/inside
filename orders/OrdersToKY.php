<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');
require_once (__ROOT__ . '/src/Klaviyo.php');


function kylistupdatebatch($json_input,$list_id){

	$toURL = "https://a.klaviyo.com/api/v1/list/".$list_id."/members/batch";
	$post = array(
		"api_key"=>"pk_c7748135f279a53d0564775e3b528f7c05",
		"batch"=> $json_input,
		"confirm_optin"=> "false"
	);
	$ch = curl_init();
	$options = array(
		CURLOPT_URL=>$toURL,
		CURLOPT_HEADER=>0,
		CURLOPT_VERBOSE=>0,
		CURLOPT_RETURNTRANSFER=>true,
		CURLOPT_USERAGENT=>"Mozilla/4.0 (compatible;)",
		CURLOPT_POST=>true,
		CURLOPT_POSTFIELDS=>http_build_query($post),
	);
	curl_setopt_array($ch, $options);
	// CURLOPT_RETURNTRANSFER=true 會傳回網頁回應,
	// false 時只回傳成功與否
	$result = curl_exec($ch); 
	curl_close($ch);
	// echo $result;

}

function phpCurl($url){
	// 建立CURL連線
	$ch = curl_init();

	// 設定擷取的URL網址
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);

	// 執行
	curl_exec($ch);

	// 關閉CURL連線
	curl_close($ch);
}

function getOrdersforDaysKY($days){
	global $mysqli;
	$data = array();
	$tempOrderid = "";
	$tempOrder = array();

	  //count all orders and customers
	$sql = 'SELECT O1.phone,O1.order_id, O1.email,U.email as appemail, O1.username, O1.first_order_time,O1.last_order_interval,O1.product_name,O1.total_payment,O1.order_time, O1.appmemberid  FROM Orders O1 LEFT JOIN user U on U.phone = O1.phone where order_time > DATE_SUB(curdate(), INTERVAL ? DAY) order by O1.order_id;';
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

function ordersProcess(){
	$ch = curl_init();
	$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:"inside.morningshop.tw";
	
    curl_setopt($ch, CURLOPT_URL, 'http://'.$host.'/orders/ordersProcess.php');
    // $options = array(
    //     CURLOPT_RETURNTRANSFER => true, 
    //     CURLOPT_POST           => true,   // return web page
    // ); 
    curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
    // curl_setopt($ch, $options);
    
    $result = curl_exec($ch);
    curl_close($ch);	
}

$id = "0911111111";
$email = "xxdfdf@gmail.xxx";
$last_name = "xxx";
$name = $last_name;
$phone = $id; 
$firstordertime = "2016-03-22";
$order_time = $firstordertime;
$appmemberid="1233445";
$items="xx";
$amount=100;
$timestamp = strtotime("now");

$kyformat[] = array('email' => $email,
		"properties"=> 
			array('$first_name' => "　",
				'$last_name' => $last_name,
				'$phone_number' => $id, 
				'$id' =>  $id)
			);

$json = json_encode($kyformat);

kylistupdatebatch($json,'iUppsW');
$metric = "buy";
$tracker = new Klaviyo(kykey);

// echo $tracker->track(
// 		    $metric,
// 		    array('$id' => $id, '$email' => $email , '$last_name' => $name,'$phone_number'=>$phone ,'First Order Time'=>$firstordertime,'Last Order Time' => $order_time,'appmemid' => $appmemberid),
// 		    array('Items' => $items,'Order Time'=>$order_time,'Order Amount'=>$amount),
// 		    $timestamp
// 		);	

$array = array(
	"token" => "c73MkW",
	"event" => "buy",
	"customer_properties" => array(
	  '$id' => $id,
	),
	"time" => 1458972846
);

echo "https://a.klaviyo.com/api/track?data=".base64_encode(json_encode($array));

// ordersProcess();

// $metric = "buy";
// $tracker = new Klaviyo(kykey);

// $orders = getOrdersforDaysKY(11);

// foreach ($orders as $value) {
// 	$email = $value['email'] == "temp@no.email" && !is_null($value['appemail']) ? $value['appemail'] : $value['email'];
// 	$email = $email == "" || is_null($email) ? "temp@no.email" : $email;
//  $email = $appemail != "" && !is_null($appemail) ? $appemail : $email;
// 	$id = $value['phone'];
// 	$name = $value['username'];
// 	$phone = $value['phone'];
// 	$firstordertime = $value['first_order_time'];
// 	// $last_order_interval = $value['last_order_interval'];
// 	$items = $value['items'];
// 	$order_time = $value['order_time'];
// 	$amount = $value['total_payment'];
// 	$timestamp = strtotime("now");
// 	//strtotime($order_time);
// 	$appmemberid = $value['appmemberid'];

// 	if ($email != "temp@no.email" && !is_null($email)) {
// 		echo "email: ".$email."\n";
// 		echo "phone: ".$phone."\n";
// 		echo "id: ".$id."\n";

// 		$tracker->track(
// 		    $metric,
// 		    array('$id' => $id,'$email' => $email , '$last_name' => $name,'$phone_number'=>$phone ,'First Order Time'=>$firstordertime,'Last Order Time' => $order_time,'appmemid' => $appmemberid),
// 		    array('Items' => $items,'Order Time'=>$order_time,'Order Amount'=>$amount),
// 		    $timestamp
// 		);	
// 	}

// 	// echo "email: ".$email.'<br>';
	
// }

//print_r($orders);

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