<?php
/**
update product salesqty from UITOX
**/  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/deconfig.php');
require_once (__ROOT__ . '/uitoxapi/uitox_functions.php');
require_once (__ROOT__ . '/app/app_functions.php');

// echo getCurrentTime();
// echo getUITOXproductSaleqty('201411AG200001614');
// $url = "https://api.91mai.com/scm/v1/SalePage/GetMain";
// $input = json_encode(array( "id" => 1687302));
$skucount = getSKUCount();
 //569

function updateStoreqty($storage_id, $qty){
	global $mysqli;

	$sql = "UPDATE product set quantity = ? where storage_id = ?";
	$stmt = $mysqli->prepare($sql); 
	$stmt->bind_param('ss',$qty,$storage_id);

	$stmt->execute(); 
	$stmt->close();  
}


//print_r(call91api($url,$input));
$products = array();

if ($skucount->SKUCount) {
	for ($i=0; $i < $skucount->SKUCount; $i+=500) { 
		$products_temp = get91productList($i,500);
		
		if (count($products) > 0) {
			$products = array_merge($products_temp,$products);
		}else{
			$products = $products_temp;
		}
	}	
}

// // echo "products: ".count($products);
// // print_r($products);

foreach ($products as $value) {

	$product_id_app = $value->Id;
	$product_id_warehouse = $value->OuterId;
	$stock = getAppStock($product_id_app);
	// echo "app id: $product_id_app ; code : $product_id_warehouse stock; $stock <br/>";

	if (!is_null($stock) && !is_null($product_id_warehouse)) {
		updateStoreqty($product_id_warehouse,$stock);
	}
}
// echo "newnew";
?>
