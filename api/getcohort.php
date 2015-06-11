  <?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

//get all return oders and users
  $sql = 
  'SELECT Count(*) as orderNumbers, cohort_table.firstdate, cohort_table.PERIOD 
  FROM  (SELECT Orders.email, EXTRACT(YEAR_MONTH from cohorts.cohortdate) as firstdate, Orders.order_time, 
         (PERIOD_DIFF(EXTRACT(YEAR_MONTH from Orders.order_time), EXTRACT(YEAR_MONTH from cohorts.cohortdate))) AS PERIOD
         FROM  Orders 
               JOIN (SELECT email, Min(order_time) AS cohortDate 
                     FROM  Orders 
                     GROUP  BY email) AS cohorts 
               ON Orders.email = cohorts.email 
               GROUP BY Orders.order_id ORDER BY Orders.email
         ) AS cohort_table
  WHERE firstdate > 0 AND cohort_table.email <> "morning@ouregion.com" AND cohort_table.email <> "morning@ouregion.com" AND cohort_table.email <> "jpj0121@hotmail.com" AND cohort_table.email <> "jake.tzeng@gmail.com" AND cohort_table.email <> "iqwaynewang@gmail.com"    
  GROUP BY cohort_table.firstdate, cohort_table.PERIOD;';

    
  $stmt = $mysqli->query($sql); 
  $data = $cohort = array();

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
    $firstDateMonth = DateTime::createFromFormat('Y-m-d', substr($row['firstdate'], 0, 4).'-'.substr($row['firstdate'], 4,strlen($row['firstdate'])-4).'-'.'01');
    // echo '$firstDateMonth: '.$firstDateMonth->format('Ym').'<br/>';
    $firstDateMonth->modify( 'first day of + '.$row['PERIOD'].' months' );
    $returnDate = $firstDateMonth->format('Ym');
    //echo 'PERIOD: '.$row['PERIOD'].'<br/>firstdate: '.$row['firstdate'].'<br/>$returnDate: '.$returnDate.'<br/>orderNumbers: '.$row['orderNumbers'].'<br/><br/>';
    //prepare the first cohort array  
    $cohort['Month'][$row['firstdate']] = $row['firstdate'];

    $cohort[$row['firstdate']][$returnDate] = !strcmp($row['firstdate'],$returnDate)?$row['orderNumbers']:$row['orderNumbers'].'  ('.round($row['orderNumbers']*100/$cohort[$row['firstdate']][$row['firstdate']],2).'%)';

  }

  $cohortResult = array();

  foreach ($cohort as $key => $value) {
    //print_r($value);
    $cohortResult[] = array('Month' =>$key) + $value;
  }
  
  //print_r($cohortResult);

  $stmt->close();
  $mysqli->close();


  echo json_encode($cohortResult);
?>