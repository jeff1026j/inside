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

function getEmailsforDays($days){
	global $mysqli;

	  //count all orders and customers
	$sql = 'SELECT Distinct email, phone FROM Orders O1 where order_time > DATE_SUB(curdate(), INTERVAL ? DAY) ;';
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('d',$days);
    $stmt->execute();

	$stmt->bind_result($email,$phone);
	$emails = array();
	while($stmt->fetch()){
	      $emails[] = ["email" => trim($email),"phone"=>"886".ltrim(trim($phone),'0')];
	}
    $stmt->close();	
	return $emails;
}

$output = getEmailsforDays(4);
$today = date("Y_m_d");
$file = 'orderstofb_'.$today.'.csv';
$path = __ROOT__ .'/tmp/';
outputcsvfile($output, $path.$file);
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
$email->Subject   = 'Jake Tzeng'.$today."  orders to facebook";
$email->Body      = $today."  訂單 - 請儘速匯入 FB\nJAKE \n
\n
十分重要！！！！       十分重要！！！！       十分重要！！！！       十分重要！！！！    十分重要！！！！
\n
匯一份名單，多一份希望

今日名言：
1. 不被無助感困住 \n
2. 保持熱情\n
3. 行動\n
4. 再多做一點\n
5. 追求成果\n
6. 保持彈性\n
7. 當事情不如預期，不要抱怨\n
8. 一以貫之\n

";
$email->AddAddress( 'jake.tzeng@ouregion.com','Jake Tzeng' );
$file_to_attach = $path.$file;
$email->AddAttachment( $file_to_attach , $file);

if(!$email->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $email->ErrorInfo;
} else {
    echo 'Message has been sent';
}
?>