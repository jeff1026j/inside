<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$cohortDate = (isset($_GET['cohortDate']) && $_GET['cohortDate']!='')?$_GET['cohortDate']:date('Ym');
  
// echo $cohortDate;

// $currentDate = '2015-03';
 
$sql=" select distinct o2.email, 
                u.email as appemail, 
                o2.phone, 
                o2.username, 
                o2.max_order_time,
                o2.previous_purchase
from   ((select Orders.email, 
                Orders.".cohortkey.", 
                Orders.username, 
                MAX(Orders.order_time) as max_order_time, 
                GROUP_CONCAT(Orders.product_name separator ', ')  as previous_purchase,
                Orders.appmemberid
         from   Orders 
                join (select ".cohortkey.", 
                             EXTRACT(year_month from Min(order_time)) as 
                             cohortDate 
                      from   Orders 
                      group  by ".cohortkey." 
                      having cohortdate = ?) as cohorts 
                  on Orders.".cohortkey." = cohorts.".cohortkey." 
         where  Orders.email <> 'morning@ouregion.com' 
                and Orders.email <> 'morning@ouregion.com' 
                and Orders.email <> 'jpj0121@hotmail.com' 
                and Orders.email <> 'jake.tzeng@gmail.com' 
                and Orders.email <> 'iqwaynewang@gmail.com' 
         group  by Orders.email)) o2 
       left join user u 
              on u.phone = o2.phone; 			
;
";
    

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('s',$cohortDate);

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

