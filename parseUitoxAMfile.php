<?php
require_once 'header.php';

function parseSpreadSheet($fileName){
	global $mysqli;
	
    $del = detectDelimiter($fileName);
    //parse data~
    if (($handle = fopen($fileName, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000000, $del)) !== FALSE) {
            $num = count($data);
            $am_id              = $data[13]; 
            $cost               = $data[6]; 
            $price              = $data[5]; 
            $product_name       = $data[2];
  //          echo "row number: # $row \n <br/><br/>";
  //          echo "order_id: $order_id, status: $status, order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time, product_name: $product_name, product_rank: $product_rank, product_quantity: $product_quantity, product_price: $product_price, product_cost: $product_cost, product_id: $product_id, username: $username <br/><br/>";
//            echo   "order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time <br/><br/>";
            //insert into db
            
            $sql = "UPDATE product set price = ?, cost = ?, product_name = ? where uitoxAmid = ?";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('ssss',$price,$cost, $product_name ,$am_id);

            $stmt->execute(); 
            $stmt->close();
            // echo $data[1]."<br>";
            // echo $order_time."<br>";

        }
        fclose($handle);
        $mysqli->close();
    }
    unlink($fileName);

	
}
//file
$rawfilename = isset($_FILES['csvfile']['tmp_name'])?$_FILES['csvfile']['tmp_name']:null;
//check csv file first

parseSpreadSheet($rawfilename);

require_once 'footer.php';
    //clear the cache first
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/ppp.php');
curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( "url"=>'http://'.$_SERVER['HTTP_HOST'].'/')));
curl_exec($ch);
curl_close($ch);

header("Location: /");

?>