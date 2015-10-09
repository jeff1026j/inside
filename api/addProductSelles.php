<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$product_id = isset($_GET['product_id']) ?$_GET['product_id']:null;
$seller = isset($_GET['seller']) ?$_GET['seller']:null;

if (!$seller || !$product_id) exit("product_id & seller is null");

$sql = "INSERT IGNORE INTO productSeller (product_id,seller) VALUES (?,?)";
$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('ss',$product_id,$seller);

$stmt->execute(); 
$stmt->close();

?>