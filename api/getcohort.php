<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');

//get all return oders and users
  $sql = 
  'SELECT Count(*) as orderNumbers, cohort_table.firstdate, cohort_table.PERIOD 
  FROM  (SELECT Orders.email, EXTRACT(YEAR_MONTH from cohorts.cohortdate) as firstdate, Orders.order_time, 
         ABS(PERIOD_DIFF(EXTRACT(YEAR_MONTH from Orders.order_time), EXTRACT(YEAR_MONTH from cohorts.cohortdate))) AS PERIOD
         FROM  Orders 
               JOIN (SELECT email, Min(order_time) AS cohortDate 
                     FROM  Orders 
                     GROUP  BY email) AS cohorts 
               ON Orders.email = cohorts.email 
               GROUP BY Orders.order_id ORDER BY Orders.email
         ) AS cohort_table
  WHERE firstdate > 0
  GROUP BY cohort_table.firstdate, cohort_table.PERIOD;';

    
  $stmt = $mysqli->query($sql); 
  $data = $cohort = array();

  while($row = $stmt->fetch_array(MYSQLI_ASSOC)){

    $firstDateMonth = DateTime::createFromFormat('Y-m', substr($row['firstdate'], 0, 4).'-'.substr($row['firstdate'], 4,strlen($row['firstdate'])-4));
    $firstDateMonth->add(new DateInterval('P'.$row['PERIOD'].'M'));
    $returnDate = $firstDateMonth->format('Ym');
    
    //prepare the first cohort array
    $cohort['Month'][$row['firstdate']] = $row['firstdate'];
    $cohort[$row['firstdate']][$returnDate] = $row['orderNumbers'];

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