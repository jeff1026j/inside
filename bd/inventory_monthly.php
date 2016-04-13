<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');
require (__ROOT__ . '/src/PHPMailer/PHPMailerAutoload.php');

function outputcsvfile($data,$re_filepath){
	
	$fp = fopen($re_filepath, 'w');
	   
	foreach ($data as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);	
}

function getInventory(){
	global $mysqli;

	  //count all orders and customers
	$sql = 'SELECT product_name,quantity,price,cost,storage_id,tradebuy
			FROM product;';


	$stmt = $mysqli->prepare($sql);
    $stmt->execute();

	$stmt->bind_result($product_name,$quantity,$price,$cost,$storage_id,$tradebuy);
	$products = array(["product_name" => "商品名稱","quantity" => "庫存","price" => "價格","cost" => "成本","storage_id" => "QC碼","tradebuy" => "切貨1寄倉0"]);

	$inventory = 0;
	while($stmt->fetch()){
		  // echo "email: ".$email."  appemail: ".$appemail."<br>";
		$inventory += $cost * $tradebuy * $quantity;
		  // echo "newemail: ".$email."<br>";
	    $products[] = ["product_name" => trim($product_name),"quantity"=>$quantity,"price"=>$price,"cost"=>$cost,"storage_id"=>$storage_id,"tradebuy"=>$tradebuy];
	}
    $stmt->close();	
	return array("products"=>$products,"inventory"=>$inventory);
}

$output = getInventory();

$today = date("Y_m_d");
$file = 'products_'.$today.'.csv';
$path = __ROOT__ .'/tmp/';
outputcsvfile($output['products'], $path.$file);
//print_r($output);

$email = new PHPMailer();
$email->IsSMTP();                                // 設定使用SMTP方式寄信        
$email->SMTPAuth = true;                         // 設定SMTP需要驗證
// $email->SMTPDebug = 1; 
$email->SMTPSecure = "tls";                      // Gmail的SMTP主機需要使用SSL連線   
$email->Host = "smtp.gmail.com";                 // Gmail的SMTP主機        
$email->Port = 587;                              // Gmail的SMTP主機的port為465      
$email->CharSet = "utf-8";                       // 設定郵件編碼   
$email->Encoding = "base64";
$email->WordWrap = 50;                           // 每50個字元自動斷行
      
$email->Username = gmailAccount;     // 設定驗證帳號        
$email->Password = gmailPassword;              // 設定驗證密碼    

$email->From      = 'morningshop.tw@gmail.com';
$email->FromName  = 'MorningShop BI';
$email->Subject   = $today." inventory report";
$email->Body      = $today." 庫存價值（切貨）: ".$output['inventory']."\n";
$email->AddAddress( 'jeffreywu@ouregion.com','Jeffrey Wu' );
$file_to_attach = $path.$file;
$email->AddAttachment( $file_to_attach , $file);

if(!$email->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $email->ErrorInfo;
} else {
    echo 'Message has been sent';
}
?>