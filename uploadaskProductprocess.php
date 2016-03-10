<?php
    require_once 'header.php';
    /* @param string $csvFile Path to the CSV file
    * @return string Delimiter
    */
    function errcodeInterpret($erCode){
        switch ($erCode) {
            case "01":
                return "欄位格式順次錯誤";
            case "02":
                return "無商品編號";
            default:
                return "無 error code";
        }
    } 

    
    //file
    $rawfilename = isset($_FILES['csvfile']['tmp_name'])?$_FILES['csvfile']['tmp_name']:null;
    //check csv file first

    $fileValid = true;
    $errorCode = "00";

    //detect delimeter first   
    $del = detectDelimiter($rawfilename);  

    // echo "del:".$del."end";

    if (($handle = fopen($rawfilename, "r")) !== FALSE) {
        $i = 0;
        while (($data = fgetcsv($handle, 1000000, $del)) !== FALSE) {
            if ($i==0) {
                //check if the column is all the same
                if ( !strstr($data[0],"品項") || 
                     !strstr($data[1],'賣場 ID')|| 
                     !strstr($data[2],'商品 ID')
                     ){ 
                    $fileValid = false;
                    $errorCode = "01";    
                }
            }
            if(!$fileValid)
                break;

            $i++;
        }
    }
    //parse data~
    if (($handle = fopen($rawfilename, "r")) !== FALSE && $fileValid) {
        while (($data = fgetcsv($handle, 1000000, $del)) !== FALSE) {
            $num = count($data);
            $product_id             = $data[2]; 
            $buy                    = $data[4];
            $sql = "update product set tradebuy = ? where product_id = ?;";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('ds',$buy,$product_id);

            $stmt->execute(); 
            $stmt->close();
            // echo $data[1]."<br>";
            // echo $order_time."<br>";

        }
        fclose($handle);
        $mysqli->close();
    }
    unlink($rawfilename);

    if (!$fileValid) {
        echo '<h3> 上傳錯誤， 錯誤代碼：'.$errorCode.'<br/>錯誤解釋： '.errcodeInterpret($errorCode).'</h3>';
    }

    require_once 'footer.php';
    //clear the cache first
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/ppp.php');
    curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
    curl_setopt($ch, CURLOPT_USERPWD, 'morningshop' . ":" . 'goodmorning');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( "url"=>'http://'.$_SERVER['HTTP_HOST'].'/')));
    curl_exec($ch);
    curl_close($ch);
   
    if ($fileValid)
        header("Location: /");

?>