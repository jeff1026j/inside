<?php
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

// $get_values = array();
// echo 'test:' . $_GET['numberOfReturn'];

$sql='select product_id,product_name,storage_id,tradebuy,price,cost,avg_sale, quantity, seller from product order by avg_sale desc;';

if ($stmt = $mysqli->prepare($sql)) {

	$stmt->execute();
	$stmt->bind_result($product_id,$product_name, $storage_id,$tradebuy,$price,$cost,$avg_sale ,$quantity,$seller);
	
	$json = array();
	while($stmt->fetch()){
	      $json[] = ['product_id'=>$product_id,'product_name'=>$product_name, 'storage_id'=>$storage_id,'tradebuy'=>$tradebuy,'price'=>$price,'cost'=>$cost, 'avg_sale'=>$avg_sale,'quantity'=>$quantity, 'seller'=>$seller];
	}    
    
}
else {
    printf("Errormessage: %s\n", $mysqli->error);
}

echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

