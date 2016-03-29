<?php
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$numberOfReturn = isset($_GET['numberOfReturn'])?$_GET['numberOfReturn']:1;

$sql = "SELECT USERPRODUCT.product_name, SUM(USERPRODUCT.productRanking) AS finalScore 
FROM
    ( SELECT O3.email, O3.".cohortkey.", O3.product_name,O3.storage_id,count(*) as productRanking 
      FROM Orders O3, (SELECT O1.email, O1.".cohortkey." , COUNT(DISTINCT O1.order_id) as returnCustomer 
                       FROM Orders as O1 GROUP By O1.".cohortkey." HAVING returnCustomer > 1) O2 
      WHERE O3.".cohortkey." = O2.".cohortkey."
      Group by O3.".cohortkey.", O3.storage_id HAVING productRanking > ? ) AS USERPRODUCT 
GROUP BY USERPRODUCT.storage_id order by finalScore desc;";

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

