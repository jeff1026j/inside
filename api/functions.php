<?php
function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="'.$filename.'";');

        // open the "output" stream
        // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
        $f = fopen('php://output', 'w');

        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }
} 

function timehandler($str){
      $result = '';

      if (strlen($str)<12) {
          $time = strptime($str,'%m/%d %H:%M');    
          @$year = $time[tm_mon]==11?2014:2015;
          @$result = $year.'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';        
      }else if(stripos($str, "/") < 4){
          $time = strptime($str,'%m/%d/%Y %H:%M');    
          @$result = ($time[tm_year]+1900).'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';
      }else{
          $time = strptime($str,'%Y/%m/%d %H:%M');    
          @$result = ($time[tm_year]+1900).'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';
      }
      // echo $result;
      return $result;
}

function array_median($array) {
  // perhaps all non numeric values should filtered out of $array here?
  $iCount = count($array);
  if ($iCount == 0) {
    throw new DomainException('Median of an empty array is undefined');
  }
  // if we're down here it must mean $array
  // has at least 1 item in the array.
  $middle_index = floor($iCount / 2);
  sort($array, SORT_NUMERIC);
  $median = $array[$middle_index]; // assume an odd # of items
  // Handle the even case by averaging the middle 2 items
  if ($iCount % 2 == 0) {
    $median = ($median + $array[$middle_index - 1]) / 2;
  }
  return $median;
}

/* @param string $csvFile Path to the CSV file
    * @return string Delimiter
    */
    function detectDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle); 
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }
?>
