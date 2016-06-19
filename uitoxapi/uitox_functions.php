<?php
define('__ROOT2__', dirname(dirname(__FILE__)));
require_once (__ROOT2__ . '/config/config_db.php');
require_once (__ROOT2__ . '/config/conn_db.php');
require_once (__ROOT2__ . '/uitoxapi/uxapi_pub.php');

//=========================================
// 查詢商品可賣量
//=========================================

function getProductSaleQty($product_id){
    $uxapi = new UITOX_API;

    $inputdata = array(array('uitox_product_id' => $product_id));
    $result = array();

    // foreach ($products as $v) {
    //     if ($v) {
    //         $inputdata[]= array('uitox_product_id' => $v);
    //     }
    // }

    $api = '/show_sale/get_item_qty_by_item';
    $api_version = '1.0.0';
    $data = array(
            'count' => count($inputdata),
            'saleable_qty' => $inputdata
    );
    $method = 'POST';

    $saleqty = 0;
    try
    {
        $api_result = $uxapi->call_api($api, $api_version, $data, $method, $proxy); 
        // echo 'xxx: '.$api_result;
        $api_result = json_decode($api_result);
        
        if (strcmp($api_result->status_code,'4001001100')==0) {
            
            $saleable_qty = $api_result->saleable_qty;
            $saleqty = $saleable_qty[0];$saleqty=$saleqty->saleable_qty;
            // //get the max_saleqty
            // $max_sale_p = getUITOXproductInfo('201412AG150000247');
            // $max_sale_qty = $max_sale_p->max_sale_qty;
            
            // if ($max_sale_qty != '') {
            //     $saleqty = min($saleqty,$max_sale_qty);
            // }
            
        }

        // print_r($result);
    }
    catch (Exception $e)
    {
        echo "error msg= " . $e->getMessage();
    }

    return $saleqty;
}

function addNewOrderUITOX($inputdata){
    $uxapi = new UITOX_API;
    
    $result = array();

// sampel 先給你
// https://uxapi.uitoxbeta.com/order/add_delivery_order
// { 
//     "account":"xxxxxxxx", 
//     "password":"xxxxxxxx",  
//     "version":"1.0.0",  
//     "platform_id":"AW000780", 
//     "data": 
//     { 
//         "count":"1",  
//         "order":
//         [ 
//              
//         ]
//     } 
// }

    $api = '/order/add_delivery_order';
    $api_version = '1.0.0';
    $data = array(
            'count' => count($inputdata),
            'order' => $inputdata
    );
    $method = 'POST';

    try
    {
        $api_result = $uxapi->call_api($api, $api_version, $data, $method, $proxy); 
        // echo '88: '.$api_result;
        $api_result = json_decode($api_result);
        
        $status = $api_result->status_code;
        if (strcmp($status,'003001009100')==0) {
            
            //echo "success";
            $result = $api_result->order;
            
            foreach ($result as $value) {
                //update the order status in db
                updateOrderStatus('import',$value);

            }

        }else if (strcmp($status,'3001004225')==0) {
            
            //order id duplicate



        }

    }
    catch (Exception $e)
    {
        echo "error msg= " . $e->getMessage();
    }

    return $result;

}

function updateOrderStatus($status,$data){
    global $mysqli;

    if ($status=="import") {
        // print_r($data);
        if ($data->vendor_order_no) {
            $sql = "UPDATE Orders set order_id = ?, status = ? where vendor_order_no =?";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('sss',$data->uitox_order_no,$status,$data->vendor_order_no);

            $stmt->execute(); 
        }
        
    }else if ($status=="ship") {
        if ($data->vendor_order_no) {
            $sql = "UPDATE Orders set status = ? where vendor_order_no =?";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('ss',$status,$data->vendor_order_no);

            $stmt->execute(); 
        }
    }

}

function parseUITOXweb($uitoxAMid){
    
    if (!$uitoxAMid || $uitoxAMid == 'NULL') {
        return null;
    }

    $saleqty = 0;
    $doc = new DomDocument;

    $url = "http://www.morningshop.tw/item/".$uitoxAMid;


    // We need to validate our document before refering to the id
    @$doc->validateOnParse = true;
    @$content = getUrlContent($url);

    try{

        @$doc->loadHtml($content);
        

    } catch(Exception $e) {
        $doc = null;
    }

    if ($doc) {
        if (preg_match('/var product_data=(.*?);/m', $content, $matches)) {

            $product_data = ($matches[1]);
            $saleIndex = strpos($product_data,"saleqty");
            $saleStr = substr($product_data,$saleIndex,30);
            // select $saleStr;
            $saleqty = substr($saleStr, strpos($saleStr,':') +1 , strpos($saleStr,',') - strpos($saleStr,':')-1);

        }
        
    }


    return $saleqty;
}

function getUITOXproductInfo($product_id){
    $uxapi = new UITOX_API;

    $api = '/show_info/get_item_info_by_id';
    $api_version = '1.0.0';
    
    $inputdata = array( array('uitox_product_id' => $product_id));
    
    $data = array(
            'count' => count($inputdata),
            'item' => $inputdata
    );
    $method = 'POST';

    $result = null;

    try
    {
        $api_result = $uxapi->call_api($api, $api_version, $data, $method, $proxy); 
        // echo 'yyy: '.$api_result;
        $api_result = json_decode($api_result);
        
        if (strcmp($api_result->status_code,'4004001100')==0) {
                        
            $result = $api_result->item[0];

        }

        // print_r($result);
    }
    catch (Exception $e)
    {
        echo "error msg= " . $e->getMessage();
    }

    return $result;
}

function getUITOXproductSaleqty($product_id){
    //get uitoxamid by product_id 
    global $mysqli;
    
    $sql = 'SELECT uitoxAmid, product_id From product where product_id = ?';

    $stmt = $mysqli->prepare($sql); 
    $stmt->bind_param('s',$product_id);

    $stmt->execute();
    $stmt->bind_result($uitoxAmid,$pid);
    //
    $stmt->fetch();
    $uitoxAmid = !$uitoxAmid ? null : $uitoxAmid;
    // echo "product_id: ".$product_id."<br>";
    if (!$pid) {
        // echo "need uitoxAmid: ".$product_id."<br>";
        //parse the uitox data and insert to db
        $item = getUITOXproductInfo($product_id);
        $name = $item->item_name;
        $cost = $item->cost;
        $price = $item->price;

        if (!$name) {
            return null;
        }

        $sql2 = "INSERT IGNORE INTO product (product_id,product_name,price,cost,createtime) VALUES (?,?,?,?,?)";
        $stmt2 = $mysqli->prepare($sql2); 
        $stmt2->bind_param('ssdds',$product_id,$name,$price,$cost,$date = date("Y-m-d H:i:s"));
        $stmt2->execute();
        $stmt2->close(); 

    }    

    return parseUITOXweb($uitoxAmid);

}

// getUITOXproductSaleqty('201512AG100011555');
// getProductSaleQty('201411AG200001620');
//getUITOXproductInfo('201412AG150000247');
// echo '<br><br><br><br><br>xxx';

//201412AG150000247
// $products = array('201504AG170000925','201505AG080001763','201504AG070000540','201411AG260000304','201411AG200001615');
// print_r(getProductSaleQty($products));

// addNewOrderUITOX();

//end file


?>