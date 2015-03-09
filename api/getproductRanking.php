<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

$numberOfReturn = isset($_GET['numberOfReturn'])?$_GET['numberOfReturn']:1;

$sql = "SELECT USERPRODUCT.product_name, SUM(USERPRODUCT.productRanking) AS finalScore FROM( SELECT O3.email, O3.product_name,count(*) as productRanking  from Orders as O3  WHERE O3.email IN ( SELECT O2.email FROM (SELECT O1.email, COUNT(DISTINCT O1.order_id) as returnCustomer FROM Orders as O1 GROUP By O1.email) as O2 WHERE O2.returnCustomer > ? )  Group by O3.email, O3.product_name HAVING productRanking > 1 ) AS USERPRODUCT GROUP BY USERPRODUCT.product_name order by finalScore desc;";

$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('d',$numberOfReturn);

$stmt->execute();
$stmt->bind_result($productName, $productScore);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['product_name'=>$productName, 'finalScore'=>$productScore];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

