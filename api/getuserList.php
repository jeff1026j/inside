<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

$numberOfReturn = isset($_GET['numberOfReturn'])?$_GET['numberOfReturn']:1;

$sql_number = '';

if($numberOfReturn < 6 && $numberOfReturn > 0){
    $sql_number = " = $numberOfReturn";
}else if($numberOfReturn == 9999){
    $sql_number = " > 5";   
}else if($numberOfReturn == 0){
    $sql_number = " > 0";
}


$sql="SELECT O1.username,O1.email, COUNT(DISTINCT O1.order_id) as returnCustomer, MAX(O1.order_time) as max_order_time, O1.phone FROM Orders as O1 GROUP By O1.email HAVING(returnCustomer $sql_number ) order by max_order_time;";

$stmt = $mysqli->prepare($sql); 
//$stmt->bind_param('d',$numberOfReturn);

$stmt->execute();
$stmt->bind_result($username, $email,$returnCustomer, $max_order_time, $phone);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['user_name'=>$username, 'email'=>$email, 'returnCustomer'=>$returnCustomer,'max_order_time'=>$max_order_time, 'phone'=>$phone];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

