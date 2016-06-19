<?php
    require_once 'header.php';
    define('__ROOT__', dirname(__FILE__));
    require_once (__ROOT__ . '/api/functions.php');
    require_once (__ROOT__ . '/config/deconfig.php');
    
    //get raw orders based on the first month time and products, to try to analyze the returning rate
    function getOrdersByFirstOrderMonthProduct($firstMonth,$firstProductName, $startime,$endTime){
      global $mysqli;
      
      //$firstOrderMonth == '201505'
      $firstMonth = !$firstMonth ? date('Ym') : $firstMonth;
      $startime = !$startime ? '2014-01-01 00:00:00' : $startime;
      $endTime = !$endTime ?  '2060-01-01 00:00:00': $endTime;
      $firstProductName = !$firstProductName ?  '': $firstProductName;
      
      $sql = 'SELECT O1.phone ,
                     O1.order_id,
                     UNIX_TIMESTAMP(O1.order_time) AS order_time,
                     O1.total_payment AS order_price
              FROM Orders O1,

                (SELECT Orders.phone,
                        cohortDate
                 FROM Orders
                 JOIN
                   (SELECT phone,
                           EXTRACT(YEAR_MONTH
                                   FROM Min(order_time)) AS cohortDate
                    FROM Orders
                    WHERE email <> "morning@ouregion.com"
                      AND Orders.email <> "morning@ouregion.com"
                      AND Orders.email <> "jpj0121@hotmail.com"
                      AND Orders.email <> "jake.tzeng@gmail.com"
                      AND Orders.email <> "iqwaynewang@gmail.com"
                      AND Orders.status <> "Cancel" 
                    GROUP BY phone) AS cohorts ON Orders.phone = cohorts.phone
                 WHERE cohortDate = ?
                   AND cohortDate = EXTRACT(YEAR_MONTH
                                            FROM Orders.order_time)
                   AND Orders.product_name LIKE ?) AS O2
              WHERE O1.phone = O2.phone
                AND O1.order_time > ?
                AND O1.order_time < ?
                AND O1.status <> "Cancel" 
              GROUP BY O1.order_id
              ORDER BY O1.phone,
                       O1.order_time
              ';

      $firstProductName = "%$firstProductName%";

      $stmt = $mysqli->prepare($sql); 
      $stmt->bind_param('ssss',$firstMonth,$firstProductName, $startime,$endTime);

      $stmt->execute();
      $stmt->bind_result($phone,$order_id,$order_time, $order_price);
      //
      $data = array();
      $returnCustomers = array();

      while($stmt->fetch()){
        $data[] = ['phone'=>$phone, 'order_id'=>$order_id,'order_time'=>$order_time, 'order_price'=>$order_price];
        $returnCustomers[] = $phone;
      }

      //$stmt->close();
      
      return array($data, $returnCustomers);

    }
     

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

    //getOrdersByFirstOrderMonthProduct($firstMonth,$firstProductName, $startime,$endTime)

    $testData = array();
    $testDate = array('2015-07-01 00:00:00','2015-08-01 00:00:00','2015-09-01 00:00:00','2015-10-01 00:00:00','2015-11-01 00:00:00','2015-12-01 00:00:00','2016-01-01 00:00:00','2016-02-01 00:00:00','2016-03-01 00:00:00','2016-04-01 00:00:00');
    foreach ($testDate as $value) {
      $tempDate = new DateTime($value);
      //(new DateTime($endTimeThisMonth))->modify('first day of this month')->format('Ym');
      $firstMonth = $tempDate->format('Ym');
      $startime = $value;
      $endTime = $tempDate->modify('+ 6 months')->modify('last day of this month')->format('Y-m-d'); $endTime = $endTime." 23:59:59";
      $testData[] = ['firstMonth' => $firstMonth ,'firstProductName' => null,'startime' => $startime,'endTime' =>$endTime];
      $testData[] = ['firstMonth' => $firstMonth ,'firstProductName' => '早餐盒','startime' => $startime,'endTime' =>$endTime];

    }

    //echo json_encode($testData);
    //['firstMonth'=>,'firstProductName'=>'早餐盒','startime'=>,'endTime'=>],

    // foreach ($testData as $value) {
    //   list($data, $returnCustomers) = getOrdersByFirstOrderMonthProduct($value['firstMonth'] , $value['firstProductName'] , $value['startime'] , $value['endTime']);
    //   list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);
    //   $customers = count(array_unique($returnCustomers));
    //   $numberOrders    = count($data);  
    // }


    require_once 'footer.php';
?>

<!-- <hr/>
<h3>回購人數/總會員數：<?=$returnCustomer?>/<?=$totalCustomer?>  :  <?=round($returnCustomer*100/$totalCustomer,2)?>%</h3>
<h3>重複訂單/總定單數：<?=$returnOrders?>/<?=$totalOrders?>  :  <?=round($returnOrders*100/$totalOrders,2)?>%</h3>
<hr/>
<h3>每人回購週期(平均)： <?=round($interval/86400,1)?>天</h3>
 -->