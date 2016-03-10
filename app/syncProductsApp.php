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
$product_id_app = 1931669;
$product_id_warehouse = "0287872360-07";

function saveProducts($data,$outerid){
	global $mysqli;

	$product_id             = $data->Id; 
    $product_name           = $data->Title;
    $color                  = null;
    $price                  = $data->Price;
    $cost                   = $data->Cost;
    //$seller                 = ??? 
    $size                   = null;
    $insurance              = $data->Cost;
    $in_tax                 = null;
    $out_tax                = null;
    $cat                    = null;
    $pre_order              = null;
    $cvs                    = null;
    $big_volume             = null;
    $length                 = null;
    $width                  = null;
    $height                 = null;
    $weight                 = null;
    $quantity               = null;
    $order_patern           = null;
    $ean                    = null;
    $isbn                   = null;
    $vendor                 = $outerid;
    $vendor_custom_numeber   = $outerid;
    $vendor_custom          = $outerid;
    $first_stock_time       = $data->SellingStartDateTime;
    $createtime             = $data->SellingStartDateTime;

//          echo "row number: # $row \n <br/><br/>";
//          echo "order_id: $order_id, status: $status, order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time, product_name: $product_name, product_rank: $product_rank, product_quantity: $product_quantity, product_price: $product_price, product_cost: $product_cost, product_id: $product_id, username: $username <br/><br/>";
//            echo   "order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time <br/><br/>";
    //insert into db
    
    $sql = "INSERT IGNORE INTO product (product_id,product_name,color,size,insurance,in_tax,out_tax,cat,pre_order,cvs,big_volume,length,width,height,weight,quantity,order_patern,ean,isbn,vendor,vendor_custom_numeber,vendor_custom,first_stock_time,createtime,price,cost,storage_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($sql); 
    $stmt->bind_param('ssssdssssssdddddssssssssdds',$product_id,$product_name,$color,$size,$insurance,$in_tax,$out_tax,$cat,$pre_order,$cvs,$big_volume,$length,$width,$height,$weight,$quantity,$order_patern,$ean,$isbn,$vendor,$vendor_custom_numeber,$vendor_custom,$first_stock_time,$createtime,$price,$cost,$outerid);

    $stmt->execute(); 
    $stmt->close();

}

$data = getProductMain($product_id_app);
if ($data) {
	saveProducts($data,$product_id_warehouse);
}
?>