<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

//compute data from order and update periodically

function updateLastOrderTime(){
	global $mysqli;  

	$sql = "UPDATE Orders o3, (select distinct phone, order_id, timestampdiff(day,(select order_time from Orders o2
        where o2.order_time < o1.order_time and o1.phone = o2.phone order by o2.order_time desc limit 1),order_time) as diff
		from Orders o1) as lastorder 
		SET o3.last_order_interval=lastorder.diff 
		WHERE o3.order_id = lastorder.order_id and o3.last_order_interval is null;
		";
	
	
	$mysqli->query($sql); 

}


function updateFirstOrdertime(){
	global $mysqli;
	$sql = "UPDATE Orders o1,(SELECT phone, Min(order_time) AS cohortDate 
								FROM  Orders GROUP  BY phone) AS cohorts 
			SET o1.first_order_time=cohorts.cohortDate 
			WHERE o1.phone = cohorts.phone and o1.first_order_time is null;";

	$mysqli->query($sql);

}

function updateOrderAmountWeb(){
	global $mysqli;
	$sql="UPDATE 
	        Orders AS r 
	    JOIN
	        ( SELECT   order_id, 
	                   SUM(product_price) AS sum_score
	          FROM     Orders 
	          WHERE    order_from is null
	          GROUP BY order_id
	        ) AS grp
	       ON  
	           grp.order_id = r.order_id 
	SET 
	       r.total_payment = grp.sum_score
	WHERE 
	       r.order_from is null;";

	$mysqli->query($sql);
}

function updateOrderAmountApp(){
	global $mysqli;
	$sql="UPDATE 
	        Orders AS r 
	    JOIN
	        ( SELECT   order_id, 
	                   SUM(order_amount) AS sum_score
	          FROM     Orders 
	          WHERE    order_from is not null
	          GROUP BY order_id
	        ) AS grp
	       ON  
	           grp.order_id = r.order_id 
	SET 
	       r.total_payment = grp.sum_score
	WHERE 
	       r.order_from is not null;";

	$mysqli->query($sql);
}


updateFirstOrdertime();
updateLastOrderTime();
updateOrderAmountWeb();
updateOrderAmountApp();

?>