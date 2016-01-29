<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__ . '/src/Klaviyo.php');
require_once (__ROOT__ . '/config/deconfig.php');

$id = isset($_GET['id']) ?$_GET['id']:null;
$metric = isset($_GET['metric']) ?$_GET['metric']:null;
$email = isset($_GET['email']) ?$_GET['email']:null;
$phone = isset($_GET['phone']) ?$_GET['phone']:null;
$name = isset($_GET['name']) ?$_GET['name']:null;
$order_time = isset($_GET['order_time']) ?$_GET['order_time']:null;
$amount = isset($_GET['amount']) ?$_GET['amount']:null;
$firstordertime = isset($_GET['firstordertime']) ?$_GET['firstordertime']:null;
$timestamp = isset($_GET['timestamp']) ?$_GET['timestamp']:null;

// $price = isset($_GET['price']) ?$_GET['price']:null;
// $cost = isset($_GET['cost']) ?$_GET['cost']:null;

echo "id: ".$id."<br>";
echo "metric: ".$metric."<br>";
echo "email: ".$email."<br>";
echo "phone: ".$phone."<br>";
echo "name: ".$name."<br>";
echo "order_time: ".$order_time."<br>";
echo "amount: ".$amount."<br>";
echo "firstordertime: ".$firstordertime."<br>";
echo "timestamp: ".$timestamp."<br>";


if (!$id || !$metric  || !$email || !$phone || !$name || !$order_time || !$firstordertime ) exit("null");

$tracker = new Klaviyo(kykey);
$tracker->track(
    $metric,
    array('$email' => $email,'$id' => $id , '$last_name' => $name,'$phone_number'=>$phone ,'First Order Time'=>$firstordertime,'Last Order Time'=>$order_time),
    array('Item SKU' => 'ABC123','Order Time'=>$order_time,'Order Amount'=>$amount),
    $timestamp
);

?>