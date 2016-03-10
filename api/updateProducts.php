<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

//$data = isset($_POST['data']) ?$_POST['data']:null;
// $data = json_decode($data,true);

//$input = $_POST['changeData'];
// echo $data["product_id"];

$product_id = $_POST['product_id'];
// $cost = $_POST['cost'];
// $quantity = $_POST['quantity'];
// $seller = $_POST['seller'];
// $tradebuy = $_POST['tradebuy'];
// $storage_id = $_POST['storage_id'];
$field = $_POST['field'];
print_r($_POST);

if (is_null($product_id) || ((strcasecmp($field, "cost") !== 0) && (strcasecmp($field, "price") !== 0) && (strcasecmp($field, "storage_id") !== 0) && (strcasecmp($field, "seller") !== 0) && (strcasecmp($field, "tradebuy") !== 0) )) exit("product_id is null or field is not");

$sql = "UPDATE product set ".$field." = ? where product_id = ?";
$stmt = $mysqli->prepare($sql); 
$dtype = $field == "cost" || $field == "price" ? 'ds' : 'ss';
$stmt->bind_param($dtype,$_POST[$field],trim($product_id));

$stmt->execute(); 
$stmt->close();  

echo "success";
?>