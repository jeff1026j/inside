<?php
require_once 'config/config_db.php';
require_once 'config/conn_db.php';

//get the last update order set
//get the post data
$sql = "SELECT MAX(order_time) from Orders";
$stmt = $mysqli->prepare($sql); 
$stmt->execute(); 

$stmt->bind_result($newestime);
// then fetch and close the statement
$stmt->fetch();
//echo $postid . $userid . $content . $title . $photo . $url;

$stmt->close();

?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
    <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/inside.css">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.6.0/bootstrap-table.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.6.0/bootstrap-table.min.js"></script>
    <!-- Latest compiled and minified Locales -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.6.0/locale/bootstrap-table-zh-CN.min.js"></script>
    <script type="text/javascript" src="js/bootstrap-datepicker.js"></script>
    <link rel="stylesheet" href="css/datepicker3.css">
    <script type="text/javascript" src="js/table2CSV.js"></script>
    <script type="text/javascript" src="js/validator.js"></script>
    <script type="text/javascript" src="js/bootstrap-table-export.js"></script>
    <script type="text/javascript" src="js/tableExport.js"></script>
    <script type="text/javascript" src="js/jquery.base64.js"></script>

    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    </head>
    <body>
      <div class="container">
        <div id="wrapper">
            <div id="container" class="container">
                <div class="main">
                    <nav class="navbar navbar-default navbar-fixed-top">
                        <div class="container-fluid">
                            <div class="navbar-header">
                                <a class="navbar-brand" href="/">MorningShop Inside</a>
                            </div>
                            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <ul class="nav navbar-nav">
                                <li><a href="/uploadfile.php">匯入</a></li>
                                <li><a href="/returnProductRanking.php">回購強商品排行榜</a></li>
                                <li><a href="/userList.php">會員名單</a></li>
                                <li><a href="/cohort.php">Cohort</a></li>
                                <li><a href="/stock.php">叫貨準則</a></li>
                            </ul>
                            <ul class="nav navbar-nav navbar-right">
                                <p class="navbar-text">Last update: <?=$newestime?></p>
                                
                            </ul>

                        </div>
                    </nav>
                    <div class="row">
                        <div class="formcolumn col-xs-10 col-centered">
