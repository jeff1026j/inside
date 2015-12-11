<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

$json = array(); 
$sql=" SELECT * FROM product where first_stock_time is not null and first_stock_time <> '0000-00-00 00:00:00';";

$result = $mysqli->query($sql);

while ($row = $result->fetch_assoc()) {
    $json[] = $row;
}

echo json_encode($json);

$stmt->close();
$mysqli->close();

?>

