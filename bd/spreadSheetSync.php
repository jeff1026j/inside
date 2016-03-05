<?php
header('Content-Type: application/xml');

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
  
$product_id = isset($_GET['productid'])?$_GET['productid']:null;
$amid = isset($_GET['amid'])?$_GET['amid']:null;
$cost = isset($_GET['cost'])?$_GET['cost']:null;
$seller = isset($_GET['seller'])?$_GET['seller']:null;
$name = isset($_GET['name'])?$_GET['name']:null;

$avg_sale = 0; 
$reproduct_id = '';
//check if there's product_id
if ($product_id) {
	$sql = "SELECT product_id, avg_sale from product where product_id = ?;";

	$stmt = $mysqli->prepare($sql); 
	$stmt->bind_param('s',$product_id);

	$stmt->execute();
	$stmt->bind_result($reproduct_id,$avg_sale);
	
	
	$stmt->fetch();
	$stmt->close();

}


if ((!$reproduct_id || $avg_sale === null) && $product_id && $name && $amid) {

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/api/getPredictSales.php?productid='.$product_id);
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
	
	$sql = "INSERT IGNORE INTO product (product_id,product_name,uitoxAmid,cost,createtime,seller,avg_sale) VALUES (?,?,?,?,?,?,?) on duplicate key update avg_sale=?";

	$stmt = $mysqli->prepare($sql); 
	$stmt->bind_param('sssdssdd',$product_id,$name,$amid,$cost,$date = date("Y-m-d H:i:s"),$seller,$avg_sale,$avg_sale);

	$stmt->execute();
	$stmt->close();	


}
//if no -> insert product and compute the avg sale then update





echo '<root>';
echo '<avg_sale>'.$avg_sale.'</avg_sale>';
echo '</root>';
$mysqli->close();

?>
