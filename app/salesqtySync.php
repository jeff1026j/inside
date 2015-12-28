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

//print_r(call91api($url,$input));
$products = get91productList();

// echo "products: ".count($products);

// print_r($products);
foreach ($products as $value) {

	
	$product_id_app = $value->Id;
	$product_id_uitox = $value->OuterId;
	$skuid =  $value->SkuId;
	$saleqty = getUITOXproductSaleqty($product_id_uitox);
	// echo "saleqty: ".$saleqty."<br>";
	if ($saleqty !== null) {

		$saleqty = max(($saleqty - 80),0);
		updateAppProductSaleQty($product_id_app,$saleqty,$skuid,$product_id_uitox);

	}

}
// echo "newnew";
?>
