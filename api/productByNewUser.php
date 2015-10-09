<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$firstMonth = (isset($_GET['firstMonth']) && $_GET['firstMonth']!='')?$_GET['firstMonth']:date('Ym');

// echo $cohortDate;

// $currentDate = '2015-03';
 
$sql = 'SELECT product_name, COUNT(*) as productSum
        FROM
         (SELECT Orders.email, Orders.'.cohortkey.', Orders.product_name
          FROM  Orders 
               JOIN (SELECT '.cohortkey.', EXTRACT(YEAR_MONTH from Min(order_time)) AS cohortDate 
                     FROM  Orders 
                     GROUP  BY '.cohortkey.') AS cohorts 
               ON Orders.'.cohortkey.' = cohorts.'.cohortkey.'
          WHERE cohortDate = ? AND cohortDate = EXTRACT(YEAR_MONTH from Orders.order_time) AND Orders.email <> "morning@ouregion.com" AND Orders.email <> "morning@ouregion.com" AND Orders.email <> "jpj0121@hotmail.com" AND Orders.email <> "jake.tzeng@gmail.com" AND Orders.email <> "iqwaynewang@gmail.com"  
          ) as O2
        GROUP BY O2.product_name order by productSum desc limit 50;';

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('s',$firstMonth);

$stmt->execute();
$stmt->bind_result($product_name,$productSum);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['product_name'=>$product_name, 'productSum'=>$productSum];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();
?>

