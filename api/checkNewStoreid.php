<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');


$product_id = isset($_GET['productid'])?$_GET['productid']:null;
 

function getNewStoreid($product_id){
	
	global $mysqli;

	if ($product_id) {
	    $sql = "select storage_id from product where product_id = ?;";

		$stmt = $mysqli->prepare($sql); 
		$stmt->bind_param('s',$product_id);

		$stmt->execute();
		$stmt->bind_result($newStoreid);
		
		$stmt->fetch();
		$stmt->close();
		

		return $newStoreid;
	}	
}

echo '<root>';
echo '<storageid>'.getNewStoreid($product_id).'</storageid>';
echo '</root>';

?>
