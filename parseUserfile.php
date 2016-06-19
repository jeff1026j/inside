<?php
    require_once 'header.php';
    /* @param string $csvFile Path to the CSV file
    * @return string Delimiter
    */
    function errcodeInterpret($erCode){
        switch ($erCode) {
            case "01":
                return "欄位格式順次錯誤";
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
    $type = 0;

    if (($handle = fopen($rawfilename, "r")) !== FALSE) {
        $data = fgetcsv($handle, 1000000, $del);
            
        //check if the column is all the same
        if ( !strstr($data[0],"會員編號") || 
             !strstr($data[2],'會員姓名')||   
             !strstr($data[3],'手機號碼')|| 
             !strstr($data[4],'註冊日期')|| 
             !strstr($data[5],'最後消費日期')
             ){ 

            $fileValid = false;
            $errorCode = "01";    
        }
    
        if(!$fileValid)
            break;
        if (strstr($data[14],'生日')) {
                $type = 1;
        }elseif (strstr($data[11],'生日')) {
            $type = 2;
        }else{
            $type = false;
        }

    }

    
    

    //parse data~
    if (($handle = fopen($rawfilename, "r")) !== FALSE && $fileValid && $type) {
        while (($data = fgetcsv($handle, 1000000, $del)) !== FALSE) {
            $num = count($data);
            $appmemid           = $data[0]; 
            $name               = $data[2]; 
            $phone              = $data[3]; 
            $regisdate          = $data[4]; 
            $lastbuydate        = $data[5]; 
            $userfrom           = $data[7]; 

            if ($type==1) {
                $installapp         = $data[8]; 
                $totalspend         = $data[10]; 
                $totalspendtime     = $data[11]; 
                $birth              = $data[14]; 
                $email              = $data[15];     

            }else if ($type ==2) {
                $installapp         = "無"; 
                $totalspend         = $data[9]; 
                $totalspendtime     = $data[10]; 
                $birth              = $data[11]; 
                $email              = $data[12];     
            }

            



            // echo "appmemid: ".$appmemid;
            // echo "name: ".$name;
            // echo "phone: ".$phone;
            // echo "regisdate: ".$regisdate;
            // echo "lastbuydate: ".$lastbuydate;
            // echo "birth: ".$birth;
            // echo "email: ".$email;

            $sql = "INSERT IGNORE INTO user (appmemid,name,regisdate,lastbuydate,birth,email,phone, installapp, userfrom, totalspend, totalspendtime ) VALUES (?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=?,lastbuydate=?,email=?, installapp=?, userfrom=?, totalspend=?, totalspendtime=?";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('sssssssssddsssssdd',$appmemid,$name,$regisdate,$lastbuydate,$birth,$email,$phone,$installapp, $userfrom, $totalspend, $totalspendtime,$name,$lastbuydate,$email,$installapp, $userfrom, $totalspend, $totalspendtime);

            $stmt->execute(); 
            $stmt->close();
            // echo $data[1]."<br>";
            // echo $order_time."<br>";

        }
        fclose($handle);
        $mysqli->close();
    }
    unlink($rawfilename);
 
    if (!$fileValid || !$type) {
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