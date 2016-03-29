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
 
$sql="select distinct o2.email, 
                u.email as appemail, 
                o2.phone,
                o2.username, 
                o2.max_order_time, 
                o2.previouspur 
      from   (select newestOrders.email, 
                     newestOrders.username, 
                     newestOrders.max_order_time, 
                     O2.phone,
                     GROUP_CONCAT(O2.product_name separator ', ') as previouspur,
                     O2.appmemberid
              from   (select O1.email, 
                             O1.".cohortkey.", 
                             MAX(O1.order_time) as max_order_time, 
                             O1.username 
                      from   Orders as O1 
                      where  O1.order_time < ? 
                      group  by O1.".cohortkey.") as newestOrders, 
                     Orders O2 
              where  newestOrders.max_order_time > ? 
                     and newestOrders.max_order_time < ? 
                     and O2.".cohortkey." = newestOrders.".cohortkey." 
              group  by newestOrders.".cohortkey.") o2 
             left join user u 
                    on u.phone = o2.phone;";
		

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('sss',$currentDate,$maxDate,$minDate);

$stmt->execute();
$stmt->bind_result($email,$appemail,$phone, $username, $max_order_time,$product_name);
//
$json = array();
while($stmt->fetch()){
      $email = $email == "temp@no.email" && !is_null($appemail) ? $appemail : $email;
      $email = $email == "" || is_null($email) ? "temp@no.email" : $email;
      $email = $appemail != "" && !is_null($appemail) ? $appemail : $email;
      $json[] = ['user_name'=>$username, 'email'=>$email,'max_order_time'=>$max_order_time, 'product'=>$product_name];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

