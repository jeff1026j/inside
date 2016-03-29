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

function get91productListClosed($position,$count){ //500

    $url = "https://api.91mai.com/scm/v1/Salepage/GetSKUList";
    $input = json_encode(
                array(
                    "createdDateTimeStart" => "2013-10-01T10:03",
                    "createdDateTimeEnd" => "2017-10-01T10:03",
                    "position" => $position,
                    "count" => $count,
                    "isClosed" => true
                )
            );  
    // $input2 = json_encode(
    //          array(
    //              "createdDateTimeStart" => "2013-10-01T10:03",
    //              "createdDateTimeEnd" => "2016-10-01T10:03",
    //              "position" => 499,
    //              "count" => 500,
    //              "isClosed" => ture
    //              )
    //      );

    $result = json_decode(call91api($url,$input));
     // echo "123: <br>";
      // print_r($result);
     // echo '<br><br><br><br><br>';
    
    if ($result->Status != "Success") {
        $result = null; 
    }

    return $result->Data;

}

function getSKUCountClosed(){ //500

    $url = "https://api.91mai.com/scm/v1/SalePage/GetSkuCount";
    $input = json_encode(
                array(
                    "createdDateTimeStart" => "2013-10-01T10:03",
                    "createdDateTimeEnd" => "2017-10-01T10:03",
                    "isClosed" => true
                )
            );  
    // $input2 = json_encode(
    //          array(
    //              "createdDateTimeStart" => "2013-10-01T10:03",
    //              "createdDateTimeEnd" => "2016-10-01T10:03",
    //              "position" => 499,
    //              "count" => 500,
    //              "isClosed" => ture
    //              )
    //      );

    $result = json_decode(call91api($url,$input));
     // echo "123: <br>";
     // print_r($result);
     // echo '<br><br><br><br><br>';
    
    if ($result->Status != "Success") {
        $result = null; 
    }

    return $result->Data;

}



$skucount = getSKUCountClosed();
 //569



//print_r(call91api($url,$input));
$products = array();

if ($skucount->SKUCount) {
    for ($i=0; $i < $skucount->SKUCount; $i+=500) { 
        $products_temp = get91productListClosed($i,500);
        
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

    if (!is_null($stock) && !is_null($product_id_warehouse) && $stock > 0 ) {
        
            # products not in databases
            //get data from app
            $data = getProductMain($product_id_app);
            if ($data) {
                echo $data->Title . "<br>" . $data->Id . "<br><br>";
            }

        
    }
}
echo "newnew";
echo strtotime("now");
?>
