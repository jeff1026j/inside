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
		
		$exclude_products = array("201509AG240006030","201502AG100000107","201512AG170013507","1931669","1747407","1878702","201512AG100008233","201512AG100011555","201507AG020002210","201507AG020002130","201507AG020002222","201509AG180005002","2000820");
		
		if (!in_array($product_id,$exclude_products)) {
			$margin = 100*( 1 - $cost/$price);
			$json[] = ['product_id'=>$product_id,'product_name'=>$product_name, 'storage_id'=>$storage_id,'tradebuy'=>$tradebuy,'price'=>$price,'cost'=>$cost,'margin'=>floor($margin)."%", 'avg_sale'=>$avg_sale,'sale_day' => floor(($quantity/$avg_sale)*30) ,'quantity'=>$quantity, 'seller'=>$seller];	
		}
		
	}    
    
}
else {
    printf("Errormessage: %s\n", $mysqli->error);
}

echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

