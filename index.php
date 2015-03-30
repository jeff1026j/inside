<?php
    require_once 'header.php';
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
      $sql = 'SELECT O3.email,  O3.order_id, UNIX_TIMESTAMP(O3.order_time) as order_time            
              FROM (SELECT O1.email, COUNT(DISTINCT O1.order_id) as returnCustomer                    
                    FROM Orders as O1  '.$endTimeSqlO1.'
                    GROUP By O1.email HAVING returnCustomer > 1 ) as O2, Orders O3             
              WHERE O3.email = O2.email  '.$endTimeSqlO3.'
              GROUP BY O3.order_id 
              ORDER BY O3.email, O3.order_time;';


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

    
    $endTime = isset($_GET['endtime'])&&isValidDateTimeString($_GET['endtime'],'Y-m-d')?$_GET['endtime']:null;
    $date = (new DateTime($endTime))->modify('first day of this month');
    $endTimeThisMonth = $date->format('Y-m-d');
    $endTimeLastMonth = $date->modify('first day of last month')->format('Y-m-d');
    // echo $endTimeThisMonth;
    // echo $endTimeLastMonth;
    //get current customer
    list($data, $returnCustomers) = getAllReturnOrders($endTime);
    list($totalCustomer, $totalOrders) = getDistincOrders($endTime);
    list($dataTM, $returnCustomersTM) = getAllReturnOrders($endTimeThisMonth);
    list($totalCustomerTM, $totalOrdersTM) = getDistincOrders($endTimeThisMonth);
    list($dataLM, $returnCustomersLM) = getAllReturnOrders($endTimeLastMonth);
    list($totalCustomerLM, $totalOrdersLM) = getDistincOrders($endTimeLastMonth);

?>
<?php
    // foreach ($holder as $h) {
    //     $returnCustomers[] = $h['username'];
    // }   
    
    //compute return order interval
    $interval = 0;
    $email = '';
    $counter = 0;
    $mainCounter = 0;
    $temp_interval = 0;
    $arraycount = 1;
    $modeDay = 3;
    $modeCount = array();

    foreach ($data as $value) {

        if ($email == $value['email']) {
            //compute time difference to pre one
            if(abs($value['order_time'] - $order_time) > 86400*2){
                $temp_interval += abs($value['order_time'] - $order_time);
                $counter ++; 
            }
            $order_time = $value['order_time'];


            if ($arraycount == count($data)) {
                @$modeCount[floor($temp_interval/$counter/$modeDay/86400)]++;
                $interval += $temp_interval/$counter;
                $temp_interval = 0;
                $mainCounter++;
            }

        }else{
            $email = $value['email'];
            $order_time = $value['order_time'];
            
            if ($counter > 0) {
                @$modeCount[floor($temp_interval/$counter/$modeDay/86400)]++;
                $interval += $temp_interval/$counter;
                $temp_interval = 0;
                $mainCounter++;
            }
            $counter = 0;
        }

        $arraycount ++;

    }    

    $interval = $interval/$mainCounter;
    $returnCustomers = array_unique($returnCustomers);
    $returnCustomer  = count($returnCustomers);
    $returnOrders    = count($data);
    ksort($modeCount);
    // print_r($modeCount);

?>

<h2>回購目標：30%</h2>

<!-- <ul class="list-inline">
    <li class="dataSegment"><div class="listtitle">月新增會員數</div><div class="listMiddle"><?=($totalCustomer-$totalCustomerTM)?>/<?=($totalCustomerTM-$totalCustomerLM) ?></div><div class="listnumber"><?=round((($totalCustomer-$totalCustomerTM) - ($totalCustomerTM-$totalCustomerLM))*100/($totalCustomerTM-$totalCustomerLM),2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">月新增訂單數</div><div class="listMiddle"><?=($totalOrders-$totalOrdersTM)?>/<?=($totalOrdersTM-$totalOrdersLM)?></div><div class="listnumber"><?=round((($totalOrders-$totalOrdersTM)-($totalOrdersTM-$totalOrdersLM))*100/($totalOrdersTM-$totalOrdersLM),2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">回購人數/總會員數</div><div class="listMiddle"><?=$returnCustomer?>/<?=$totalCustomer?></div><div class="listnumber"><?=round($returnCustomer*100/$totalCustomer,2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">重複訂單/總定單數</div><div class="listMiddle"><?=$returnOrders?>/<?=$totalOrders?></div><div class="listnumber"><?=round($returnOrders*100/$totalOrders,2)?>%</div></li>
    <li class="dataSegment"><div class="listtitle">回購次數</div><div class="listMiddle"><?=round(($returnOrders-$returnCustomer)/$returnCustomer,2)?></div></li>
    <li class="dataSegment"><div class="listtitle">回購週期</div><div class="listMiddle"><?=round($interval/86400,1)?>天</div></li>

</ul> -->

<h3>本月/上月新增會員數：<?=($totalCustomer-$totalCustomerTM)?>/<?=($totalCustomerTM-$totalCustomerLM) ?>  成長 :  <div class="growthRate"><div class="percentage"><?=round((($totalCustomer-$totalCustomerTM) - ($totalCustomerTM-$totalCustomerLM))*100/($totalCustomerTM-$totalCustomerLM),2)?></div>%</div></h3>
<h3>本月/上月新增定單數：<?=($totalOrders-$totalOrdersTM)?>/<?=($totalOrdersTM-$totalOrdersLM)?>  成長 :  <div class="growthRate"><div class="percentage"><?=round((($totalOrders-$totalOrdersTM)-($totalOrdersTM-$totalOrdersLM))*100/($totalOrdersTM-$totalOrdersLM),2)?></div>%</div></h3>
<hr/>

<h3>回購人數/總會員數：<?=$returnCustomer?>/<?=$totalCustomer?>  :  <?=round($returnCustomer*100/$totalCustomer,2)?>%</h3>
<h3>重複訂單/總定單數：<?=$returnOrders?>/<?=$totalOrders?>  :  <?=round($returnOrders*100/$totalOrders,2)?>%</h3>
<hr/>
<h3>每人回購次數： <?=round(($returnOrders-$returnCustomer)/$returnCustomer,2)?></h3>
<h3>每人回購週期(平均)： <?=round($interval/86400,1)?>天</h3>

<h3>選擇結束週期：</h3>
<div class="input-group date">
  <input type="text" class="form-control" value="<?=$endTime?>"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
</div>

<script type="text/javascript">
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
