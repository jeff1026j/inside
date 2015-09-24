<?php
    require_once 'header.php';
    define('__ROOT__', dirname(__FILE__));
    require_once (__ROOT__ . '/api/functions.php');
    require_once (__ROOT__ . '/config/deconfig.php');

    /**
     * Check if a string is a valid date(time)
     *
     * DateTime::createFromFormat requires PHP >= 5.3
     *
     * @param string $str_dt
     * @param string $str_dateformat
     * @param string $str_timezone (If timezone is invalid, php will throw an exception)
     * @return bool
     */
    function isValidDateTimeString($str_dt, $str_dateformat) {
      $date = DateTime::createFromFormat($str_dateformat, $str_dt);
      return $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
    }

    function getAllReturnOrders($endTime){
      global $mysqli;
      //compute the endTime
      $endTimeSqlO1 = $endTime?'where O1.order_time <\''.$endTime.'\'':'';
      $endTimeSqlO3 = $endTime?'And O3.order_time <\''.$endTime.'\'':'';

      // echo $endTimeSqlO1.'<br/>';
      // echo $endTimeSqlO3;

      //get all return oders and users
      $sql = 'SELECT O3.email, O3.'.cohortkey.' , O3.order_id, UNIX_TIMESTAMP(O3.order_time) as order_time, SUM(product_price) as order_price            
              FROM (SELECT O1.email,O1.'.cohortkey.', COUNT(DISTINCT O1.order_id) as returnCustomer                    
                    FROM Orders as O1  '.$endTimeSqlO1.'
                    GROUP By O1.'.cohortkey.' HAVING returnCustomer > 1 ) as O2, Orders O3             
              WHERE O3.'.cohortkey.' = O2.'.cohortkey.' AND O2.email <> "morning@ouregion.com" AND O2.email <> "jpj0121@hotmail.com" AND O2.email <> "jake.tzeng@gmail.com" AND O2.email <> "iqwaynewang@gmail.com"    '.$endTimeSqlO3.'
              GROUP BY O3.order_id 
              ORDER BY O3.'.cohortkey.', O3.order_time;';
      

      $stmt = $mysqli->query($sql); 
      $data = array();
      $returnCustomers = array();
      while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
        $data[] = $row;
        $returnCustomers[] = $row['email'];
      }

      $stmt->close();
      
      return array($data, $returnCustomers);
    }

    function getReturnUsersbyNumberReturn($endTime){
      global $mysqli;
      //compute the endTime
      $endTimeSqlO1 = $endTime?'and O1.order_time <\''.$endTime.'\'':'';
      
      //get all return oders and users
      $sql = 'SELECT returnCustomer, COUNT(returnCustomer) as numberReturn
              FROM (
                    SELECT O1.'.cohortkey.', COUNT(DISTINCT O1.order_id) as returnCustomer                    
                    FROM Orders as O1  WHERE O1.email <> "morning@ouregion.com" AND O1.email <> "jpj0121@hotmail.com" AND O1.email <> "jake.tzeng@gmail.com" AND O1.email <> "iqwaynewang@gmail.com" '.$endTimeSqlO1.'
                    GROUP By O1.'.cohortkey.' HAVING returnCustomer > 1 
                    ) as O2
              GROUP BY returnCustomer
       ;';


      $stmt = $mysqli->query($sql); 
      $data = array();
      $returnCustomers = array();
      while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
        $returnCustomers[] = $row;
      }

      $stmt->close();
      
      return $returnCustomers;
    }

    function numberOfReturnOrdersThisMonth($endTime, $endTimeThisMonth){
      global $mysqli;
      $endTimeSqlO1 = $endTime?'AND order_time <\''.$endTime.'\'':'';
      $year_month = (new DateTime($endTimeThisMonth))->modify('first day of this month')->format('Ym');

      //EXTRACT(YEAR_MONTH from (order_time)) format 201506
      $sql='SELECT Count(*) FROM (
              SELECT Orders.'.cohortkey.', cohortDate , Orders.order_time
              FROM  Orders 
                    JOIN (SELECT '.cohortkey.', EXTRACT(MONTH from (Min(order_time))) AS cohortDate 
                          FROM  Orders 
                          WHERE email <> "morning@ouregion.com" AND email <> "jpj0121@hotmail.com" AND email <> "jake.tzeng@gmail.com" AND email <> "iqwaynewang@gmail.com"   
                          GROUP  BY '.cohortkey.') AS cohorts 
                    ON Orders.'.cohortkey.' = cohorts.'.cohortkey.'
              WHERE EXTRACT(YEAR_MONTH from (order_time)) = ? AND cohortDate  <> EXTRACT(MONTH from (order_time)) '.$endTimeSqlO1.'
            GROUP BY Orders.order_id ORDER BY Orders.'.cohortkey.') as tablea;';

      $stmt = $mysqli->prepare($sql); 
      $stmt->bind_param('s',$year_month);

      $stmt->execute();
      $stmt->bind_result($mReturnOrders);
      $stmt->fetch();
      $stmt->close();

      return $mReturnOrders;
    }

    function getDistincOrders($endTime){
      global $mysqli;

      //count all orders and customers
      $endTimeSqlO1 = $endTime?'where O1.order_time <\''.$endTime.'\'':'';
      $sql = 'SELECT COUNT(Distinct email), COUNT(Distinct order_id) FROM Orders O1 '.$endTimeSqlO1.';';
      $stmt = $mysqli->prepare($sql);
      $stmt->execute();
      $stmt->bind_result($totalCustomer,$totalOrders);
      $stmt->fetch();
      $stmt->close();

      return array($totalCustomer, $totalOrders); 
    }

    function returnInterval($orders){
      //compute return order interval
      $interval = 0;
      $email = '';
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

          if ($email == $value['email']) {
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
              $email = $value['email'];
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
    
    
    $date = isset($_GET['endtime'])&&isValidDateTimeString($_GET['endtime'],'Y-m-d')?$_GET['endtime']:null;

    $endTimeLastMonthSameDay = (new DateTime($date))->modify('-1 month')->modify('+1 day')->format('Y-m-d');$endTimeLastMonthSameDay = $endTimeLastMonthSameDay.' 00:00:00';
    $endTime = (new DateTime($date))->modify('+1 day')->format('Y-m-d');$endTime.' 00:00:00';

    $endTimeThisMonth = (new DateTime($date))->modify('first day of this month')->format('Y-m-d');$endTimeThisMonth = $endTimeThisMonth.' 00:00:00';
    $endTimeLastMonth = (new DateTime($date))->modify('first day of this month')->modify('first day of last month')->format('Y-m-d');$endTimeLastMonth=$endTimeLastMonth.' 00:00:00';



     // echo '$date: '. $date;
     // echo '<br/>$endTimeLastMonthSameDay: '. $endTimeLastMonthSameDay;    

     // echo '<br/>$endTime: '. $endTime;
     // echo '<br/>$endTimeLastMonth.: '. $endTimeLastMonth;
     // echo '<br/>$endTimeThisMonth: ' .$endTimeThisMonth;

    //get current customer
    //endTime : 6/10
    list($data, $returnCustomers) = getAllReturnOrders($endTime);
    list($totalCustomer, $totalOrders) = getDistincOrders($endTime);
    
    // echo '$totalCustomer: ' .$totalCustomer.'<br>';
    // echo '$totalOrders: ' .$totalOrders.'<br>'; 

    //endTimeThisMonth : 6/1
    //list($dataTM, $returnCustomersTM) = getAllReturnOrders($endTimeThisMonth);
    list($totalCustomerTM, $totalOrdersTM) = getDistincOrders($endTimeThisMonth);
    
    //endTimeLastMonth : 5/1
    //list($dataLM, $returnCustomersLM) = getAllReturnOrders($endTimeLastMonth);
    list($totalCustomerLM, $totalOrdersLM) = getDistincOrders($endTimeLastMonth);
    
    //endTimeLastMonthSameDay : 5/10
    //list($dataSLM, $returnCustomersSLM) = getAllReturnOrders($endTimeLastMonthSameDay);
    list($totalCustomerSLM, $totalOrdersSLM) = getDistincOrders($endTimeLastMonthSameDay);

    $numberReturns = getReturnUsersbyNumberReturn($endTime);

    //compute this year month
    $numberReturnsThisMonth = numberOfReturnOrdersThisMonth($endTime,$endTimeThisMonth);
?>
<?php
    // foreach ($holder as $h) {
    //     $returnCustomers[] = $h['username'];
    // }   


    $newOrderGoal = 6527;
    $oldOrderGoal = 3473;
    list($interval,$modeCount,$modeDay,$order_price) = returnInterval($data);
    $returnCustomers = array_unique($returnCustomers);
    $returnCustomer  = count($returnCustomers);
    $returnOrders    = count($data);
    ksort($modeCount);
    // print_r($modeCount);
    $month = (new DateTime($date))->modify('first day of this month')->format('m');
    $lastMonth = (new DateTime($date))->modify('first day of this month')->modify('first day of last month')->format('m');
?>

<!-- <ul class="list-inline">
    <li class="dataSegment"><div class="listtitle">月新增會員數</div><div class="listMiddle"><?=($totalCustomer-$totalCustomerTM)?>/<?=($totalCustomerTM-$totalCustomerLM) ?></div><div class="listnumber"><?=round((($totalCustomer-$totalCustomerTM) - ($totalCustomerTM-$totalCustomerLM))*100/($totalCustomerTM-$totalCustomerLM),2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">月新增訂單數</div><div class="listMiddle"><?=($totalOrders-$totalOrdersTM)?>/<?=($totalOrdersTM-$totalOrdersLM)?></div><div class="listnumber"><?=round((($totalOrders-$totalOrdersTM)-($totalOrdersTM-$totalOrdersLM))*100/($totalOrdersTM-$totalOrdersLM),2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">回購人數/總會員數</div><div class="listMiddle"><?=$returnCustomer?>/<?=$totalCustomer?></div><div class="listnumber"><?=round($returnCustomer*100/$totalCustomer,2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">重複訂單/總定單數</div><div class="listMiddle"><?=$returnOrders?>/<?=$totalOrders?></div><div class="listnumber"><?=round($returnOrders*100/$totalOrders,2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">回購次數</div><div class="listMiddle"><?=round(($returnOrders-$returnCustomer)/$returnCustomer,2)?></div></li>
    <li class="dataSegment"><div class="listtitle">回購週期</div><div class="listMiddle"><?=round($interval/86400,1)?>天</div></li>

</ul> -->

<h3>新增訂單 & 會員</h3>
<table data-toggle="table" data-classes="table-condensed" data-striped="true" id="OrdersAndMembersTable">
    <thead>
        <tr>
            <th class="col-xs-2">時間</th>
            <th>新增會員數</th>
            <th>新增定單數</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=$month?>月今天</td>
            <td><?=($totalCustomer-$totalCustomerTM)?></td>
            <td><?=($totalOrders-$totalOrdersTM)?></td>
        </tr>
        <tr>
            <td><?=$lastMonth?>月今天</td>
            <td><?=($totalCustomerSLM-$totalCustomerLM) ?></td>
            <td><?=($totalOrdersSLM-$totalOrdersLM)?></td>
        </tr>
        <tr>
            <td>成長</td>
            <td><div class="growthRate"><div class="percentage"><?=round((($totalCustomer-$totalCustomerTM) - ($totalCustomerSLM-$totalCustomerLM))*100/($totalCustomerSLM-$totalCustomerLM),2)?></div>%</div></td>
            <td><div class="growthRate"><div class="percentage"><?=round((($totalOrders-$totalOrdersTM)-($totalOrdersSLM-$totalOrdersLM))*100/($totalOrdersSLM-$totalOrdersLM),2)?></div>%</div></td>
        </tr>
    </tbody>
</table>
<hr/>
<h3><?=$month?>月今天 新客訂單/老會員訂單 佔比</h3>
<div class="progress ratioOrders">
  <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?=100-round($numberReturnsThisMonth*100/($totalOrders-$totalOrdersTM),2)?>%">
    <div>新客訂單</div>
    <div class="newOldOrders"><?=(($totalOrders-$totalOrdersTM)-$numberReturnsThisMonth)?></div>
    <div class="newOldPercentage"><?=100-round($numberReturnsThisMonth*100/($totalOrders-$totalOrdersTM),2)?>%</div>
  </div>
  <div class="progress-bar progress-bar-danger" role="progressbar" style="width:<?=round($numberReturnsThisMonth*100/($totalOrders-$totalOrdersTM),2)?>%">
    <div>老會員訂單</div>
    <div class="newOldOrders"><?=$numberReturnsThisMonth?></div>
    <div class="newOldPercentage"><?=round($numberReturnsThisMonth*100/($totalOrders-$totalOrdersTM),2)?>%</div>
  </div>
</div>

<h3>新客達成目標</h3>
<div class="progress progressGoal">
  <div class="progress-bar" role="progressbar" aria-valuenow="60"
  aria-valuemin="0" aria-valuemax="100" style="width:<?=round((($totalOrders-$totalOrdersTM)-$numberReturnsThisMonth)*100/$newOrderGoal,2)?>%">
    <h4><?=round((($totalOrders-$totalOrdersTM)-$numberReturnsThisMonth)*100/$newOrderGoal,2)?>% 完成</h4>
  </div>
</div>
<h3>舊客達成目標</h3>
<div class="progress progressGoal">
  <div class="progress-bar" role="progressbar" aria-valuenow="60"
  aria-valuemin="0" aria-valuemax="100" style="width:<?=round($numberReturnsThisMonth*100/$oldOrderGoal,2)?>%">
    <h4><?=round($numberReturnsThisMonth*100/$oldOrderGoal,2)?>% 完成</h4>
  </div>
</div>

<hr/>
<h3>回購人數/總會員數：<?=$returnCustomer?>/<?=$totalCustomer?>  :  <?=round($returnCustomer*100/$totalCustomer,2)?>%</h3>
<h3>重複訂單/總定單數：<?=$returnOrders?>/<?=$totalOrders?>  :  <?=round($returnOrders*100/$totalOrders,2)?>%</h3>
<hr/>
<h3>每人回購週期(平均)： <?=round($interval/86400,1)?>天</h3>
<!-- <h3>每人每月貢獻金額(假設老顧客每月回頭購買)： $<?=round($order_price*86400*30)?></h3> -->
<!-- 如果假設不是每月回頭購買：回購 user x 月份數 x  -->
<ul class="list-inline">
<?php 
  $lastNumberofReturn = 0;
  //Medium Number of return orders
  $medReturnOrder = 0;
  foreach ($numberReturns as $value) { 
      $lastNumberofReturn += $value['numberReturn'];
      $overNumberofReturn = $value['numberReturn'] + $returnCustomer - $lastNumberofReturn;
      
      if ($lastNumberofReturn*100/$returnCustomer > 50 && $medReturnOrder==0) {
          $medReturnOrder = $value['returnCustomer'];
      }
    ?>
    <li class="dataSegment">
      <div class="listtitle">購買次數</div>
      <div class="listMiddle"><?=$value['returnCustomer']?>
        <div class="upNumberIC">(以上)</div>
      </div>
      <div class="listnumber"><?=round($overNumberofReturn*100/$totalCustomer,2)?>%</div>
    </li>
  <?php } ?>
</ul>
<h3>每人回購次數： <?=round(($returnOrders-$returnCustomer)/$returnCustomer,2)?> (平均) / <?=$medReturnOrder-1?> (中位數)</h3>

<div class='hidden'>endTime: <?=$endTime?></div>
<div class='hidden'>endTimeThisMonth: <?=$endTimeThisMonth?></div>
<div class='hidden'>endTimeLastMonth: <?=$endTimeLastMonth?></div>
<div class='hidden'>endTimeLastMonthSameDay: <?=$endTimeLastMonthSameDay?></div>

<h3>選擇結束週期：</h3>
<div class="input-group date">
  <input type="text" class="form-control" value="<?=$date?>"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
</div>

<script type="text/javascript">

    //change goal progress color
    $( ".progress.progressGoal .progress-bar" ).each(function( index ) {
      var f = $(this).width() / $(this).parent().width() * 100;  
      if (f<50) {
        $(this).addClass("progress-bar-danger");
      }else{
        $(this).addClass("progress-bar-success");
      }

    });
    

    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(drawChart);

    function drawChart() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', '回購週期');
      data.addColumn('number', '總數');

      data.addRows([
        <?php 
        foreach ($modeCount as $key => $value) {
            echo '[\''.($key*$modeDay).'~'.(($key+1)*$modeDay-1).'\','.$value.'],';
        }
        ?>
      ]);

      var options = {
        width: 1000,
        height: 563,
        hAxis: {
          title: '回購週期',
          gridlines: {count: 50}
        },
        vAxis: {
          title: '統計總數'
        }
      };

      var chart = new google.visualization.ColumnChart(
        document.getElementById('ex0'));

      chart.draw(data, options);
    }

    $(function() {
        $('.input-group.date').datepicker({
            format: "yyyy-mm-dd",
            orientation: "top auto",
            todayHighlight: true,
            autoclose: true
        }).on("changeDate", function(e){
            // console.log(e.date);
            window.location = "/?endtime="+ e.date.getFullYear() + "-" + (e.date.getMonth() + 1)  + "-" + e.date.getDate();

        });;      

        $('.percentage').each(function(){
            var rate = parseFloat($(this).html());
            if(rate > 0){
              $(this).parent().addClass('green');
              $(this).parent().html($(this).parent().html()+' ↑');
            }else{
              $(this).parent().addClass('red');
              $(this).parent().html($(this).parent().html()+' ↓');
            }

        });
    });

</script>

<div id="ex0"></div>
<?php require_once 'footer.php';?>
