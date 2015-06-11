<?php
    require_once 'header.php';
    function errcodeInterpret($erCode){
        switch ($erCode) {
            case "01":
                return "欄位格式順次錯誤";
            case "02":
                return "無訂單編號";
            case "03":
                return "訂單時間格式錯誤";
            case "04":
                return "無 email";
            default:
                return "無 error code";
        }
    }

    function timehandler($str){
        $result = '';
        if (strlen($str)<12) {
            $time = strptime($str,'%m/%d %H:%M');    
            @$year = $time[tm_mon]==11?2014:2015;
            @$result = $year.'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';        
        }else if(strrpos($str, "/") < 4){
            $time = strptime($str,'%m/%d/%Y %H:%M');    
            @$year = $time[tm_mon]==11?2014:2015;
            @$result = $year.'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';
        }else{
            $time = strptime($str,'%Y/%m/%d %H:%M');    
            @$year = $time[tm_mon]==11?2014:2015;
            @$result = $year.'-'.($time[tm_mon]+1).'-'.$time[tm_mday].' '.$time[tm_hour].':'.$time[tm_min].':00';


        }
        
        return $result;
    }
    //file
    $rawfilename = isset($_FILES['csvfile']['tmp_name'])?$_FILES['csvfile']['tmp_name']:null;
    //check csv file first

    $fileValid = true;
    $errorCode = "00";

    if (($handle = fopen($rawfilename, "r")) !== FALSE) {
        $i = 0;
        while (($data = fgetcsv($handle, 1000000, ",")) !== FALSE) {
            if ($i==0) {
                //check if the column is all the same
                if ( !strstr($data[0]," 訂單狀態") || !strstr($data[1],' 下單時間') || !strstr($data[2],' 出貨時間') || !strstr($data[3],' 配達時間') || !strstr($data[4],' 訂單編號') || !strstr($data[6],' 商品名稱') || !strstr($data[9],' 數量') || !strstr($data[10],' 總售價')!=0  || !strstr($data[13],' 商品ID') || !strstr($data[16],' 購買人姓名')){ 
                    
                    $fileValid = false;
                    $errorCode = "01";    
                }
            }elseif (strcasecmp($data[4],'')==0) {
                # code...
                $fileValid = false;
                $errorCode = "02";
            }elseif (strcasecmp(timehandler($data[1]),'0000-00-00 00:00:00')==0) {
                # code...
                $fileValid = false;
                $errorCode = "03";
            }elseif (strcasecmp($data[21],'')==0) {
                # code...
                $fileValid = false;
                $errorCode = "04";
            }
            if(!$fileValid)
                break;

            $i++;
        }
    }
    //parse data~
    if (($handle = fopen($rawfilename, "r")) !== FALSE && $fileValid) {
        while (($data = fgetcsv($handle, 1000000, ",")) !== FALSE) {
            $num = count($data);
            $order_id            = $data[4];
            $status              = $data[0];
            $order_time          = timehandler($data[1]);
            $ship_time           = timehandler($data[2]);
            $arrive_time         = timehandler($data[3]);
            $product_name        = $data[6];
            $product_rank        = $data[5];
            $product_quantity    = $data[9];
            $product_price       = $data[10];
            $product_cost        = $data[11];
            $product_id          = $data[13];
            $username            = $data[16];
            $email               = $data[21];
            $phone               = $data[20];
  //          echo "row number: # $row \n <br/><br/>";
  //          echo "order_id: $order_id, status: $status, order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time, product_name: $product_name, product_rank: $product_rank, product_quantity: $product_quantity, product_price: $product_price, product_cost: $product_cost, product_id: $product_id, username: $username <br/><br/>";
//            echo   "order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time <br/><br/>";
            //insert into db
            
            $sql = "INSERT IGNORE INTO Orders (order_id,status,order_time,ship_time,arrive_time, product_name, product_rank, product_quantity,product_price,product_cost,product_id,username,email,phone ) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('ssssssddddssss',$order_id,$status,$order_time,$ship_time,$arrive_time, $product_name, $product_rank, $product_quantity,$product_price,$product_cost,$product_id,$username,$email, $phone);

            $stmt->execute(); 
            $stmt->close();
            

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


