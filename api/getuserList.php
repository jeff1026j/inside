<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

$numberOfReturn = isset($_GET['numberOfReturn'])?json_decode($_GET['numberOfReturn']):["2","3","4","5","9999"];
// $get_values = array();
// echo 'test:' . $_GET['numberOfReturn'];

$sql_number = '';

for ($i = 0; $i < count($numberOfReturn); $i++) {
	if ($i>0) {
		$sql_number = $sql_number.' or ';
	}
	$value = $numberOfReturn[$i];

	if($value < 6 && $value > 0){
	    $sql_number = $sql_number."returnCustomer = $value";
	}else if($value == 9999){
	    $sql_number = $sql_number."returnCustomer > 5";   
	}else if($value == 0){
	    $sql_number = $sql_number."returnCustomer > 0";
	}    

}

$sql='SELECT O2.username,uniO.email, uniO.returnCustomer, uniO.max_order_time, O2.phone, GROUP_CONCAT(O2.product_name SEPARATOR ", ")
FROM (	SELECT O1.email, COUNT(DISTINCT O1.order_id) as returnCustomer, MAX(O1.order_time) as max_order_time
		FROM Orders as O1 
		GROUP By O1.email HAVING('.$sql_number .') 
		order by max_order_time
		) uniO, Orders O2
WHERE uniO.email = O2.email AND O2.email <> "morning@ouregion.com" AND O2.email <> "jpj0121@hotmail.com" AND O2.email <> "jake.tzeng@gmail.com" AND O2.email <> "iqwaynewang@gmail.com"
GROUP BY uniO.email;';

$stmt = $mysqli->prepare($sql); 
//$stmt->bind_param('d',$numberOfReturn);

$stmt->execute();
$stmt->bind_result($username, $email,$returnCustomer, $max_order_time, $phone, $product);
//
$json = array();
while($stmt->fetch()){
      $json[] = ['user_name'=>$username, 'email'=>$email, 'returnCustomer'=>$returnCustomer,'max_order_time'=>$max_order_time, 'phone'=>$phone, 'product'=>$product];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

