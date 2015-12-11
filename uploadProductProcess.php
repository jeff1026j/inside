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
                if ( !strstr($data[0],"商品名稱") || 
                     !strstr($data[6],'分類')|| 
                     !strstr($data[21],'商品ID')
                     ){ 
                    $fileValid = false;
                    $errorCode = "01";    
                }
            }elseif (strcasecmp($data[21],'')==0) {
                # code...
                $fileValid = false;
                $errorCode = "02";
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
            $product_id             = $data[21]; 
            $product_name           = $data[0]; 
            $color                  = $data[1]; 
            //$seller                 = ??? 
            $size                   = $data[2]; 
            $insurance              = $data[3]; 
            $in_tax                 = $data[4]; 
            $out_tax                = $data[5]; 
            $cat                    = $data[6]; 
            $pre_order              = $data[7]; 
            $cvs                    = $data[8]; 
            $big_volume             = $data[9]; 
            $length                 = $data[10]; 
            $width                  = $data[11]; 
            $height                 = $data[12]; 
            $weight                 = $data[13]; 
            $quantity               = $data[14]; 
            $order_patern           = $data[15]; 
            $ean                    = $data[16]; 
            $isbn                   = $data[17]; 
            $vendor                 = $data[18]; 
            $vendor_custom_numeber   = $data[19]; //廠商自有料號
            $vendor_custom          = $data[20]; 
            $first_stock_time       = timehandler($data[22]);
            $createtime             = timehandler($data[24]);

  //          echo "row number: # $row \n <br/><br/>";
  //          echo "order_id: $order_id, status: $status, order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time, product_name: $product_name, product_rank: $product_rank, product_quantity: $product_quantity, product_price: $product_price, product_cost: $product_cost, product_id: $product_id, username: $username <br/><br/>";
//            echo   "order_time: $order_time, ship_time: $ship_time, arrive_time: $arrive_time <br/><br/>";
            //insert into db
            
            $sql = "INSERT IGNORE INTO product (product_id,product_name,color,size,insurance,in_tax,out_tax,cat,pre_order,cvs,big_volume,length,width,height,weight,quantity,order_patern,ean,isbn,vendor,vendor_custom_numeber,vendor_custom,first_stock_time,createtime) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE product_name =?,color=?,size=?,insurance=?,in_tax=?,out_tax=?,cat=?,pre_order=?,cvs=?,big_volume=?,length=?,width=?,height=?,weight=?,quantity=?,order_patern=?,ean=?,isbn=?,vendor=?,vendor_custom_numeber=?,vendor_custom=?,first_stock_time=?,createtime=?";
            $stmt = $mysqli->prepare($sql); 
            $stmt->bind_param('ssssdssssssdddddsssssssssssdssssssdddddssssssss',$product_id,$product_name,$color,$size,$insurance,$in_tax,$out_tax,$cat,$pre_order,$cvs,$big_volume,$length,$width,$height,$weight,$quantity,$order_patern,$ean,$isbn,$vendor,$vendor_custom_numeber,$vendor_custom,$first_stock_time,$createtime,$product_name,$color,$size,$insurance,$in_tax,$out_tax,$cat,$pre_order,$cvs,$big_volume,$length,$width,$height,$weight,$quantity,$order_patern,$ean,$isbn,$vendor,$vendor_custom_numeber,$vendor_custom,$first_stock_time,$createtime);

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


