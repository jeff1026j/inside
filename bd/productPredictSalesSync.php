<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

function getAllProduct(){
	global $mysqli;
	
	$sql = 'SELECT product_id From product';

	$stmt = $mysqli->query($sql); 
	$data = array();
	
	while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
		$data[] = $row['product_id'];
	}

	$stmt->close();

	return $data;
}

function saveAVGsaleDB($product_id,$sale){
	global $mysqli;
	
	if (!$product_id  || $sale === null /* || !$cost */) exit("product_id & sale  is null");

	$sql = "UPDATE product set avg_sale = ? where product_id=?";
	$stmt = $mysqli->prepare($sql); 
	$stmt->bind_param('ds',$sale,$product_id);

	$stmt->execute(); 
	$stmt->close();

}

$products = getAllProduct();

foreach ($products as $value) {
	//get products avg_sale
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/api/getPredictSales.php?productid='.$value);
    // $options = array(
    //     CURLOPT_RETURNTRANSFER => true, 
    //     CURLOPT_POST           => true,   // return web page
    // ); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 啟用POST
	curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
    curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
    // curl_setopt($ch, $options);
    
    $result = curl_exec($ch);
    curl_close($ch);
   	
   	$result = json_decode($result);
	$avg_sale = $result->avg_sale;   	

	//and save to db
	saveAVGsaleDB($value,$avg_sale);
	// echo "$avg_sale";
}