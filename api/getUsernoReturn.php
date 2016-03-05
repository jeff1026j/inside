<?php
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$currentDate = (isset($_GET['currentDate']) && $_GET['currentDate']!='')?$_GET['currentDate']:date('Y-m-d');
$maxInterval = isset($_GET['maxInterval'])?$_GET['maxInterval']:0;
$minInterval = isset($_GET['minInterval'])?$_GET['minInterval']:0;

// $currentDate = '2015-03-24';
if (!$currentDate || $maxInterval==0 || $minInterval==0) {
	exit("input error");
}
// $maxInterval = 100;  
// $minInterval = 30;
$maxDate = (DateTime::createFromFormat('Y-m-d', $currentDate));
$maxDate = $maxDate->modify( '- '.$maxInterval.' days')->format('Y-m-d');
$minDate = (DateTime::createFromFormat('Y-m-d', $currentDate));
$minDate = $minDate->modify( '- '.$minInterval.' days')->format('Y-m-d');

// echo "maxDate: ".$maxDate.";   minDate: ".$minDate.";   cd: ".$currentDate;
 
$sql="SELECT newestOrders.email,newestOrders.username, newestOrders.max_order_time, GROUP_CONCAT(O2.product_name SEPARATOR ', ')
      FROM (SELECT O1.email, O1.".cohortkey.", MAX(O1.order_time) as max_order_time, O1.username
            FROM Orders as O1   
            WHERE O1.order_time < ?
            GROUP By O1.".cohortkey.") as newestOrders, Orders O2
      WHERE newestOrders.max_order_time > ? 
      AND newestOrders.max_order_time < ?
	AND O2.".cohortkey." = newestOrders.".cohortkey."
	GROUP BY newestOrders.".cohortkey."
      ;
";
		

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('sss',$currentDate,$maxDate,$minDate);

$stmt->execute();
$stmt->bind_result($email, $username, $max_order_time,$product_name);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['user_name'=>$username, 'email'=>$email,'max_order_time'=>$max_order_time, 'product'=>$product_name];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

