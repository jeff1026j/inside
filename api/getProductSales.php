<?php
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$day = isset($_GET['day'])?$_GET['day']:1;
// $get_values = array();
// echo 'test:' . $_GET['numberOfReturn'];

$sql='select Orders.product_name, Orders.storage_id, sum(product_quantity) as amount, sum(product_price) as revenue,quantity,avg_sale from Orders,product where Orders.storage_id=product.storage_id and product.storage_id <> "" and product.storage_id is not null and order_time > DATE_SUB(curdate(), INTERVAL ? DAY) group by Orders.storage_id order by amount desc;';

if ($stmt = $mysqli->prepare($sql)) {
	$stmt->bind_param('d',$day);

	$stmt->execute();
	$stmt->bind_result($product_name, $product_id,$amount, $revenue,$quantity,$avg_sale);
	//
	$json = array();
	while($stmt->fetch()){
	      $json[] = ['product_name'=>$product_name, 'product_id'=>$product_id, 'amount'=>$amount,'revenue'=>$revenue, 'quantity'=>$quantity,'avg_sale'=>$avg_sale];
	}    
    
}
else {
    printf("Errormessage: %s\n", $mysqli->error);
}

echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

