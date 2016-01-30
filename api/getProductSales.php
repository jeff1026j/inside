<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$day = isset($_GET['day'])?$_GET['day']:3;
// $get_values = array();
// echo 'test:' . $_GET['numberOfReturn'];

$sql='select Orders.product_name, Orders.product_id, sum(product_quantity) as amount, sum(product_price) as revenue,quantity from Orders,product where Orders.product_id=product.product_id and order_time > DATE_SUB(curdate(), INTERVAL ? DAY) group by Orders.product_id order by amount desc;';

if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('d',$day);

	$stmt->execute();
	$stmt->bind_result($product_name, $product_id,$amount, $revenue,$quantity);
	//
	$json = array();
	while($stmt->fetch()){
	      $json[] = ['product_name'=>$product_name, 'product_id'=>$product_id, 'amount'=>$amount,'revenue'=>$revenue, 'quantity'=>$quantity];
	}    
    
}
else {
    printf("Errormessage: %s\n", $mysqli->error);
}

echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

