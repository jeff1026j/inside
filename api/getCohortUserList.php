<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$cohortDate = (isset($_GET['cohortDate']) && $_GET['cohortDate']!='')?$_GET['cohortDate']:date('Ym');
  
// echo $cohortDate;

// $currentDate = '2015-03';
 
$sql=" SELECT Orders.email, Orders.".cohortkey.",Orders.username, MAX(Orders.order_time) as max_order_time, GROUP_CONCAT(Orders.product_name SEPARATOR ', ')
	 FROM  Orders 
	     JOIN (SELECT ".cohortkey.", EXTRACT(YEAR_MONTH from Min(order_time)) AS cohortDate 
	       FROM  Orders 
	       GROUP  BY ".cohortkey."
	       HAVING cohortDate = ?
	       ) AS cohorts 
	     ON Orders.".cohortkey." = cohorts.".cohortkey."
	 WHERE Orders.email <> 'morning@ouregion.com' AND Orders.email <> 'morning@ouregion.com' AND Orders.email <> 'jpj0121@hotmail.com' AND Orders.email <> 'jake.tzeng@gmail.com' AND Orders.email <> 'iqwaynewang@gmail.com'    
	 GROUP BY Orders.email;
";
    

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('s',$cohortDate);

$stmt->execute();
$stmt->bind_result($email,$phone, $username, $max_order_time,$product_name);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['user_name'=>$username, 'email'=>$email,'max_order_time'=>$max_order_time, 'product'=>$product_name];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

