<?php
define('__ROOT3__', dirname(dirname(__FILE__)));
require_once (__ROOT3__ . '/config/config_db.php');
require_once (__ROOT3__ . '/config/conn_db.php');
require_once (__ROOT3__ . '/config/deconfig.php');

function curlPost($url,$postData){
	
	$ch = curl_init();
	$options = array(
	  CURLOPT_URL=>$url,
	  CURLOPT_HEADER=>0,
	  CURLOPT_VERBOSE=>0,
	  CURLOPT_RETURNTRANSFER=>true,
	  CURLOPT_USERAGENT=>"Mozilla/4.0 (compatible;)",
	  CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Connection: Keep-Alive'
							),
	  CURLOPT_POST=>true,
	  CURLOPT_POSTFIELDS=>$postData,
	);
	curl_setopt_array($ch, $options);
	// CURLOPT_RETURNTRANSFER=true 會傳回網頁回應,
	// false 時只回傳成功與否
	$result = curl_exec($ch); 
	curl_close($ch);
	
	return $result;
}

function getUrlContent($url){
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$updateUrlContent = curl_exec($curl);
	curl_close($curl);

	return $updateUrlContent;
}


function getCurrentTime(){

	return curlPost("https://api.91mai.com/scm/Utils/GetCurrentTime",null);
}
function call91api($url,$input){


	$timeStamp = getCurrentTime();

	$content = "ts=".$timeStamp."&data=".$input."&sk=".appSaltKey;
	$signature = hash_hmac('sha512',strtolower($content) , appKey);
	$api_url = sprintf("%s?ts=%s&t=%s&s=%s",$url,$timeStamp,appToken,$signature);


	$postData = '{\'data\':\''.$input.'\'}';

	$result = curlPost($api_url,$postData);

	return $result;
}

function getAppStock($id){
	$url = "https://api.91mai.com/scm/v1/Salepage/GetStock";
	$input = json_encode(
				array(
					"id"=>$id
				)
			);	

	$result = json_decode(call91api($url,$input));
	// echo "yyy: <br>";
	// print_r($result);
	// echo "<br><br><br><br><br>";
	if ($result->Status != "Success") {
		$result = null;	
	}
	$result = $result->Data[0]; 
	return $result->SellingQty;

}

function get91productList(){ //500

	$url = "https://api.91mai.com/scm/v1/Salepage/GetSKUList";
	$input = json_encode(
				array(
					"createdDateTimeStart" => "2013-10-01T10:03",
					"createdDateTimeEnd" => "2016-10-01T10:03",
					"position" => 0,
					"count" => 500,
					"isClosed" => false
				)
			);	
	// $input2 = json_encode(
	// 			array(
	// 				"createdDateTimeStart" => "2013-10-01T10:03",
	// 				"createdDateTimeEnd" => "2016-10-01T10:03",
	// 				"position" => 499,
	// 				"count" => 500,
	// 				"isClosed" => ture
	// 				)
	// 		);

	$result = json_decode(call91api($url,$input));
	// echo "123: <br>";
	// print_r($result);
	// echo '<br><br><br><br><br>';
	
	if ($result->Status != "Success") {
		$result = null;	
	}

	return $result->Data;

}

function updateAppProductSaleQty($product_id_app,$saleqty,$skuid,$product_id_uitox){
	
	$appStock = getAppStock($product_id_app);

	// echo "appStock: $appStock <br>";
	if (!$appStock) {
		return null;
	}
	// echo "string2";
	//	$changevalue = (int)$saleqty - (int)$appStock;

	$changevalue = 0 - (int)$appStock;	

	$changevalue = (string) ($changevalue > 0 ? "+".$changevalue : $changevalue);

	// echo "product_id_uitox: ".$product_id_uitox."<br>";
	// echo "product_id_app: ".$product_id_app."<br>";
	// echo "saleqty: ".$saleqty."<br>";
	// echo "appStock: ".$appStock."<br>";
	// echo "changevalue: ".$changevalue."<br>";
	
	if ($changevalue == "0") {
		return null;
	}

	$url = "https://api.91mai.com/scm/v1/Salepage/UpdateStock";

	$input = json_encode(
				array(
					"id" => (int) $product_id_app,
					"skuid" => (int) $skuid,
					"outerid" => null,
					"changevalue" => $changevalue
				)
			);	
	// $input2 = json_encode(
	// 			array(
	// 				"createdDateTimeStart" => "2013-10-01T10:03",
	// 				"createdDateTimeEnd" => "2016-10-01T10:03",
	// 				"position" => 499,
	// 				"count" => 500,
	// 				"isClosed" => ture
	// 				)
	// 		);
	$result = json_decode(call91api($url,$input));
	
	// print_r($result);

	if ($result->Status != "Success") {
		$result = null;	
	}

	return $result->Data;

}

function getAppOrders($shopId,$orderDeliverType,$orderDateType,$startDate,$endDate,$orderStatus,$position,$count,$ShippingOrderStatus){
	$orders = array();
	$url = "https://api.91mai.com/scm/v1/Order/GetOrderList";
	$input = json_encode(
				array(
					"shopId" => $shopId,
					"orderDeliverType" => $orderDeliverType,
					"orderDateType" => $orderDateType,
					"startDate" => $startDate,
					"endDate" => $endDate,
					"orderStatus" => $orderStatus,
					"position" => $position,
					"count" => $count,
					"ShippingOrderStatus" => $ShippingOrderStatus
				)
			);	

	$result = json_decode(call91api($url,$input));

	if ($result->Status != "Success") {
		$result = null;	
		return $result;
	}



	foreach ($result->Data->DataList as $value) {
		$orders[] = getAppOrderSpec($shopId,$value->TMCode,$value->TSCode);
	}

	return $orders;
}

function getAppOrderSpec($shopId,$TMCode,$TSCode){

	$url = "https://api.91mai.com/scm/v1/Order/GetOrder";
	$input = json_encode(
				array(
					"shopId" => $shopId,
					"TMCode" => $TMCode,
					"TSCode" => $TSCode
				)
			);	

	$result = json_decode(call91api($url,$input));

	if ($result->Status != "Success") {
		$result = null;	
		return $result;
	}

	return $result->Data[0];

}

function deliveryShipment($shopId,$TMCode,$TSCodeList,$forwarderDef,$ShippingOrderCode){

	// $ShippingOrderCode = $TMCode;

	$url = "https://api.91mai.com/scm/v1/Order/DeliveryShipment";
	$input = json_encode(
				array(
					"Shopid" => $shopId,
					"TMCode" => $TMCode,
					"TSCodelist" => $TSCodeList, 
					"ForwarderDef" => $forwarderDef,
					"ShippingOrderCode" => $ShippingOrderCode
				)
			);	

	$result = json_decode(call91api($url,$input));

	// print_r($result);
	if ($result->Status != "Success") {
		$result = null;	
		return $result;
	}
	// echo "string2";
	return $result->Data[0];

}
// deliveryShipment(1993,"TM151205120077",array(),8,"");

?>