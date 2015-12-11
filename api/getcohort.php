  <?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');

  function isValidDateTimeString($str_dt, $str_dateformat) {
        $date = DateTime::createFromFormat($str_dateformat, $str_dt);
        return $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
  }

  function getHighRetentionRatiobyMonth($firstMonth, $endTime){
    
    //假設 3 月進來 user，9 月 來看，回購週期 45 天，6 個月至少要回購 6/1.5 = 4 次以上的比例
    //first month : 201503

    global $mysqli;
    $returnPeriod = 45;//(天)

    //parse date object
    $firstDateMonth = DateTime::createFromFormat('Y-m-d', substr($firstMonth, 0, 4).'-'.substr($firstMonth, 4,strlen($firstMonth)-4).'-'.'15');
    $lastDateMonth = DateTime::createFromFormat('Y-m-d H:i:s', $endTime);
    $diffDays = $lastDateMonth->diff($firstDateMonth)->days;
    
    $returnOrderBenchMark = round($diffDays/$returnPeriod); //四捨五入


    $timeSql = $endTime?' AND Orders.order_time < "'. $endTime .'"':null;

    
    //get return times and users from first month
    $sql = 'SELECT numberReturn, COUNT(numberReturn) as returnCustomer
            FROM
             (SELECT Orders.email, Orders.'.cohortkey.', COUNT(DISTINCT Orders.order_id) as numberReturn                    
              FROM  Orders 
                   JOIN (SELECT '.cohortkey.', EXTRACT(YEAR_MONTH from Min(order_time)) AS cohortDate 
                         FROM  Orders 
                         GROUP  BY '.cohortkey.') AS cohorts 
                   ON Orders.'.cohortkey.' = cohorts.'.cohortkey.'
              WHERE cohortDate = "'.$firstMonth.'"' . $timeSql . ' AND Orders.email <> "morning@ouregion.com" AND Orders.email <> "morning@ouregion.com" AND Orders.email <> "jpj0121@hotmail.com" AND Orders.email <> "jake.tzeng@gmail.com" AND Orders.email <> "iqwaynewang@gmail.com"  
              GROUP BY Orders.'.cohortkey.') as O2
            GROUP BY numberReturn;';

    $stmt = $mysqli->query($sql); 
    $data = array();
    $totalCustomer = 0;
    $customerOverBenchMark = 0;
    
    while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
      $totalCustomer += $row['returnCustomer'];
      if ($row['numberReturn'] >= $returnOrderBenchMark && $row['numberReturn'] > 1) {
        $customerOverBenchMark += $row['returnCustomer'];
      }
    }
    // echo "firstDateMonth: ".$firstMonth.'<br>';
    // echo "totalCustomer: ".$totalCustomer.'<br>';
    // echo "returnOrderBenchMark: ".$returnOrderBenchMark.'<br>';
    // echo "customerOverBenchMark: ".$customerOverBenchMark.'<br><br><br>';

    $stmt->close();

    return round($customerOverBenchMark*100/$totalCustomer, 2);

  }

  $endTime = isset($_GET['endtime'])&&isValidDateTimeString($_GET['endtime'],'Y-m-d')?$_GET['endtime']:null;
  $endTime = (new DateTime($endTime))->modify('+1 day')->format('Y-m-d');
  $endTime = $endTime.' 00:00:00';

  $timeSql = $endTime?'WHERE Orders.order_time < "'. $endTime .'"':null;
//get all return oders and users
  $sql = 
  'SELECT Count(*) as orderNumbers, cohort_table.firstdate, cohort_table.PERIOD 
  FROM  (SELECT Orders.email, Orders.'.cohortkey.', EXTRACT(YEAR_MONTH from cohorts.cohortdate) as firstdate, Orders.order_time, 
         (PERIOD_DIFF(EXTRACT(YEAR_MONTH from Orders.order_time), EXTRACT(YEAR_MONTH from cohorts.cohortdate))) AS PERIOD
         FROM  Orders 
               JOIN (SELECT '.cohortkey.', Min(order_time) AS cohortDate 
                     FROM  Orders 
                     GROUP  BY '.cohortkey.') AS cohorts 
               ON Orders.'.cohortkey.' = cohorts.'.cohortkey.' ' .
               $timeSql. '
               GROUP BY Orders.order_id ORDER BY Orders.'.cohortkey.'
         ) AS cohort_table
  WHERE firstdate > 0 AND cohort_table.email <> "morning@ouregion.com" AND cohort_table.email <> "morning@ouregion.com" AND cohort_table.email <> "jpj0121@hotmail.com" AND cohort_table.email <> "jake.tzeng@gmail.com" AND cohort_table.email <> "iqwaynewang@gmail.com"    
  GROUP BY cohort_table.firstdate, cohort_table.PERIOD;';

  
  $stmt = $mysqli->query($sql); 
  $cohortResult = $cohortaAveragePerMonth = $data = $cohort = array();
  


//   +--------------+-----------+--------+
// | orderNumbers | firstdate | PERIOD |
// +--------------+-----------+--------+
// |          148 |    201412 |      0 |
// |           32 |    201412 |      1 |
// |            6 |    201412 |      2 |
// |           13 |    201412 |      3 |
// |          783 |    201501 |      0 |
// |           91 |    201501 |      1 |  
// |           81 |    201501 |      2 |
// |         1206 |    201502 |      0 |
// |          118 |    201502 |      1 |
// |         2135 |    201503 |      0 |
// +--------------+-----------+--------+

  while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
    // echo '$substr: '.substr($row['firstdate'], 0, 4).'-'.substr($row['firstdate'], 4,strlen($row['firstdate'])-4).'<br/>';
    if ($row['firstdate']=="201412" || $row['firstdate']=="201501") {
      continue;
    }
    // parse the first date new user come
    $firstDateMonth = DateTime::createFromFormat('Y-m-d', substr($row['firstdate'], 0, 4).'-'.substr($row['firstdate'], 4,strlen($row['firstdate'])-4).'-'.'01');
    // echo '$firstDateMonth: '.$firstDateMonth->format('Ym').'<br/>';

    // plus perios months to to that month order
    $firstDateMonth->modify( 'first day of + '.$row['PERIOD'].' months' );

    //so you got the 
    $returnDate = $firstDateMonth->format('Ym');
    //echo 'PERIOD: '.$row['PERIOD'].'<br/>firstdate: '.$row['firstdate'].'<br/>$returnDate: '.$returnDate.'<br/>orderNumbers: '.$row['orderNumbers'].'<br/><br/>';
    //prepare the first cohort array  
    $cohort['Month'][$row['firstdate']] = $row['firstdate'];

    $cohort[$row['firstdate']][$returnDate] = !strcmp($row['firstdate'],$returnDate)?$row['orderNumbers']:$row['orderNumbers'].' ('.round($row['orderNumbers']*100/$cohort[$row['firstdate']][$row['firstdate']],1).'%)';

    //later to compute average return rate 
    if (strcmp($row['firstdate'],$returnDate)) {
      @$cohortaAveragePerMonth[$returnDate]['sum'] += $row['orderNumbers']*round($row['orderNumbers']*100/$cohort[$row['firstdate']][$row['firstdate']],2);
      @$cohortaAveragePerMonth[$returnDate]['impact'] += $row['orderNumbers'];
    }

  }
  
  //compute the cohort average
  foreach ($cohortaAveragePerMonth as $key => $value) {
    //print_r($value);
    $cohortaAverage[$key] = round($value['sum']/$value['impact'],2).'%';

  }  

  //construct cohort table, add first & last line
  foreach ($cohort as $key => $value) {
    //print_r($value);
    $highReturnBenchmark = $key=='Month'?'固定回購比':getHighRetentionRatiobyMonth($key,$endTime)."%";

    $cohortResult[] = array('Month' =>$key) + $value + array('highReturnBenchmark' =>$highReturnBenchmark);
    
  }
  //add the last line to show average
  $cohortResult[] = array('Month' => 'AVG') + $cohortaAverage;
  
  
  $stmt->close();
  $mysqli->close();
  // echo getNumberOfReturnOrdersPerCustoemrbyMonth('201412',$endTime);
  echo json_encode($cohortResult);
?>