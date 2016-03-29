<?php
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

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

$sql='select distinct o3.username, 
				o3.email, 
                u.email as appemail, 
                o3.returncustomer, 
                o3.max_order_time, 
                o3.phone,
                o3.previouspur
		from            ( 
		                         select   o2.username, 
		                                  unio.email, 
		                                  unio.returncustomer, 
		                                  unio.max_order_time, 
		                                  o2.phone, 
		                                  group_concat(o2.product_name separator ", ") as previouspur,
		                                  o2.appmemberid
		                         from     ( 
		                                           select   o1.email, 
		                                                    o1.'.cohortkey.', 
		                                                    count(distinct o1.order_id) as returncustomer,
		                                                    max(o1.order_time)          as max_order_time 
		                                           from     Orders                      as o1 
		                                           group by o1.'.cohortkey.' 
		                                           having  ( 
		                                                             '.$sql_number .') 
		                                           order by max_order_time ) unio, 
		                                  Orders o2 
		                         where    unio.'.cohortkey.' = o2.'.cohortkey.' 
		                         and      o2.email <> "morning@ouregion.com" 
		                         and      o2.email <> "jpj0121@hotmail.com" 
		                         and      o2.email <> "jake.tzeng@gmail.com" 
		                         and      o2.email <> "iqwaynewang@gmail.com" 
		                         group by unio.'.cohortkey.' ) o3
		left join       user u 
		on              u.phone = o3.phone;
';

$stmt = $mysqli->prepare($sql); 
//$stmt->bind_param('d',$numberOfReturn);

$stmt->execute();
$stmt->bind_result($username, $email,$appemail,$returnCustomer, $max_order_time, $phone, $product);
//
$json = array();
while($stmt->fetch()){
	$email = $email == "temp@no.email" && !is_null($appemail) ? $appemail : $email;
	$email = $email == "" || is_null($email) ? "temp@no.email" : $email;
	$email = $appemail != "" && !is_null($appemail) ? $appemail : $email;

	$json[] = ['user_name'=>$username, 'email'=>$email, 'returnCustomer'=>$returnCustomer,'max_order_time'=>$max_order_time, 'phone'=>$phone, 'product'=>$product];
}
echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

