<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');


function returnInterval($orders){
      //compute return order interval
      $interval = 0;  
      $distinctKey = '';
      $counter = 0;
      $mainCounter = 0;
      $temp_interval = 0;
      $temp_order_price = 0;
      $order_price = 0;
      $arraycount = 1; 
      $modeDay = 3; // day period to group return Interval
      $modeCount = array();
      $data = $orders;

      foreach ($data as $value) {

          if ($distinctKey == $value[cohortkey]) {
              //same user, different order

              //compute time difference to pre one
              if(abs($value['order_time'] - $order_time) > 86400*2){
                //if order_time difference is bigger than 2 days
                  $temp_interval += abs($value['order_time'] - $order_time);
                  $temp_order_price += $value['order_price'];
                  $counter ++; 
              }
              $order_time = $value['order_time'];

              //only for the last order
              if ($arraycount == count($data)) {
                  @$modeCount[floor($temp_interval/$counter/$modeDay/86400)]++;
                  $interval += ($temp_interval/$counter);
                  // $order_price += ($temp_order_price/$temp_interval);
                  $temp_order_price = 0;
                  $temp_interval = 0;
                  $mainCounter++;
              }

          }else{
            //different user
              $distinctKey = $value[cohortkey];
              $order_time = $value['order_time'];
              
              if ($counter > 0) {
                  @$modeCount[floor($temp_interval/$counter/$modeDay/86400)]++;
                  $interval += ($temp_interval/$counter);
                  // if ($temp_interval > 86400*30*5 && $counter > 3 ) {
                    // $order_price += ($temp_order_price/$temp_interval);  
                    $mainCounter++;
                  // }
                  $temp_order_price = 0;
                  $temp_interval = 0;
                  
              }
              $counter = 0;
          }

          $arraycount ++;

      }    

      $interval = $interval/$mainCounter;
      // $order_price = $order_price/$mainCounter;
      

      return array($interval,$modeCount,$modeDay,$order_price);
}

function getAllReturnOrders($startime,$endTime,$numberOfReturn){
      global $mysqli;
      //compute the endTime
      $endTimeSqlO1 = $endTime?'where O1.order_time <\''.$endTime.'\'':'';
      $endTimeSqlO3 = $endTime?'And O3.order_time <\''.$endTime.'\'':'';

      $endTimeSqlO1 = $startime? $endTimeSqlO1.' and O1.order_time > \''.$startime.'\'':$endTimeSqlO1;
      $endTimeSqlO3 = $startime? $endTimeSqlO3.' and O3.order_time > \''.$startime.'\'':$endTimeSqlO3;

      //if null fetch all returns, or specific to one return group
      $returnQuery = $numberOfReturn && $numberOfReturn != 1? "= $numberOfReturn": ">1";

      // echo "endTimeSqlO1:".$endTimeSqlO1."<br>";
      // echo "endTimeSqlO3:".$endTimeSqlO3."<br>";
      // echo $endTimeSqlO1.'<br/>';
      // echo $endTimeSqlO3;

      //get all return oders and users
      $sql = 'SELECT O3.email, O3.'.cohortkey.' , O3.order_id, UNIX_TIMESTAMP(O3.order_time) as order_time, SUM(product_price) as order_price            
              FROM (SELECT O1.email,O1.'.cohortkey.', COUNT(DISTINCT O1.order_id) as returnCustomer                    
                    FROM Orders as O1  '.$endTimeSqlO1.'
                    GROUP By O1.'.cohortkey.' HAVING returnCustomer  '.$returnQuery.' ) as O2, Orders O3             
              WHERE O3.'.cohortkey.' = O2.'.cohortkey.' AND O2.email <> "morning@ouregion.com" AND O2.email <> "jpj0121@hotmail.com" AND O2.email <> "jake.tzeng@gmail.com" AND O2.email <> "iqwaynewang@gmail.com"    '.$endTimeSqlO3.'
              GROUP BY O3.order_id 
              ORDER BY O3.'.cohortkey.', O3.order_time;';
      

      $stmt = $mysqli->query($sql); 
      $data = array();
      $returnCustomers = array();
      while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
        $data[] = $row;
        $returnCustomers[] = $row[cohortkey];
      }

      $stmt->close();
      
      return array($data, $returnCustomers);
}
  $date = isset($_GET['endtime'])&&isValidDateTimeString($_GET['endtime'],'Y-m-d')?$_GET['endtime']:null;
  $endTime = (new DateTime($date))->modify('+1 day')->format('Y-m-d');$endTime.' 00:00:00';
?>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
    <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    </head>
  <body>
    回購時間：<br>
    從開站至今<br>
    <?php
      list($data, $returnCustomers) = getAllReturnOrders(null,$endTime,null);
      list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);

    ?>
    全站： <?=round($interval/86400,1)?><br>

    <?php 
      for ($i=2; $i < 6; $i++) { 
          list($data, $returnCustomers) = getAllReturnOrders(null,$endTime,$i);
          list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);
          ?>
          只購買 <?=$i?> 次： <?=round($interval/86400,1)?><br>

          <?php
      }

    ?>
    <br>
    回購時間：<br>
    近半年<br>
    <?php
      $startime = (new DateTime($date))->modify('-6 months')->format('Y-m-d');$endTime.' 00:00:00';
      list($data, $returnCustomers) = getAllReturnOrders($startime,$endTime,null);
      list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);
    ?>
    全站： <?=round($interval/86400,1)?><br>

    <?php 
      for ($i=2; $i < 6; $i++) { 
          list($data, $returnCustomers) = getAllReturnOrders($startime,$endTime,$i);
          list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);
          ?>
          只購買 <?=$i?> 次： <?=round($interval/86400,1)?>         <br>

          <?php
      }

    ?>

  </body>
</html>
