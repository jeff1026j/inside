<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$product_id = isset($_GET['product_id']) ?$_GET['product_id']:null;
$seller = isset($_GET['seller']) ?$_GET['seller']:null; 
$uitoxAmid = isset($_GET['uitoxAmid']) ?$_GET['uitoxAmid']:null;
// $price = isset($_GET['price']) ?$_GET['price']:null;
// $cost = isset($_GET['cost']) ?$_GET['cost']:null;

if (!$seller || !$product_id  || !$uitoxAmid /* || !$cost */) exit("product_id & seller is null");

$sql = "UPDATE product set seller = ?, uitoxAmid = ? where product_id=?";
$stmt = $mysqli->prepare($sql); 
$stmt->bind_param('sss',trim($seller),trim($uitoxAmid),trim($product_id));

$stmt->execute(); 
$stmt->close();  

?>