<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');


$product_id = isset($_GET['productid'])?$_GET['productid']:null;



function getProductSales($product_id){
	
	global $mysqli;

	if ($product_id) {
	    $sql = "SELECT AVG(sum_qty)*30 FROM
	    (
		    SELECT sum_qty, @r:=@r+1 AS rownum
		    FROM
		        (SELECT @r:=0) x,
	    	    (SELECT MONTH(order_time), DAY(order_time),SUM(product_quantity) as sum_qty
		         FROM Orders O2, 
		              (Select MAX(O1.order_time) as max_time from Orders O1 where product_id=?) as max_o
	    	     WHERE product_id = ? and O2.order_time >  DATE(max_o.max_time) - INTERVAL 6 WEEK
		         GROUP BY MONTH(order_time), DAY(order_time)
		         ) y
		         ORDER BY sum_qty
	    ) z
	    WHERE rownum > @r * .05
	      AND rownum <= @r * .95
	    ;
	    ";

		$stmt = $mysqli->prepare($sql); 
		$stmt->bind_param('ss',$product_id,$product_id);

		$stmt->execute();
		$stmt->bind_result($avg_sale);
		//
		
		$stmt->fetch();
		$avg_sale = !$avg_sale?0:$avg_sale;
		
		$stmt->close();
		$mysqli->close();
		
		return floor($avg_sale);
	}	
}


echo json_encode(array('avg_sale' => getProductSales($product_id)));

?>
