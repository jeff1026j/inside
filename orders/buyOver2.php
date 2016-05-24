<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/config_db.php');
require_once (__ROOT__ . '/config/conn_db.php');
require_once (__ROOT__ . '/config/deconfig.php');
require (__ROOT__ . '/src/PHPMailer/PHPMailerAutoload.php');

function kylistupdatebatch($json_input,$list_id){

	$toURL = "https://a.klaviyo.com/api/v1/list/".$list_id."/members/batch";
	$post = array(
		"api_key"=>"pk_c7748135f279a53d0564775e3b528f7c05",
		"batch"=> $json_input,
		"confirm_optin"=> "false"
	);
	$ch = curl_init();
	$options = array(
		CURLOPT_URL=>$toURL,
		CURLOPT_HEADER=>0,
		CURLOPT_VERBOSE=>0,
		CURLOPT_RETURNTRANSFER=>true,
		CURLOPT_USERAGENT=>"Mozilla/4.0 (compatible;)",
		CURLOPT_POST=>true,
		CURLOPT_POSTFIELDS=>http_build_query($post),
	);
	curl_setopt_array($ch, $options);
	// CURLOPT_RETURNTRANSFER=true 會傳回網頁回應,
	// false 時只回傳成功與否
	$result = curl_exec($ch); 
	curl_close($ch);
	echo $result;

}

function outputcsvfile($data,$re_filepath){
	
	$fp = fopen($re_filepath, 'w');
	//add bom for win system
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

	foreach ($data as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);	
}

function getEmailsOvertwo(){
	global $mysqli;

	$data = array();
	  //count all orders and customers
	$sql = 'SELECT distinct o2.email, 
			          u.email AS appemail, 
			          o2.username, 
			          o2.phone, 
			          o2.appmemberid 
			FROM      ( 
			                   SELECT   o1.email, 
			                            o1.username, 
			                            o1.'.cohortkey.', 
			                            count(DISTINCT o1.order_id) AS returncustomer, 
			                            o1.appmemberid 
			                   FROM     Orders AS o1 
			                   WHERE o1.order_time > "2015-04-01"
			                   GROUP BY o1.'.cohortkey.'
			                   HAVING   (returncustomer > 1) ) o2 
			LEFT JOIN user u 
			ON        u.phone = o2.phone;';
	$stmt = $mysqli->prepare($sql);
    $stmt->execute();

	$stmt->bind_result($email, $appemail, $username, $phone, $appmemberid);

	while($stmt->fetch()){
		$email = $email == "temp@no.email" && !is_null($appemail) ? $appemail : $email;
		$email = $appemail != "" && !is_null($appemail) ? $appemail : $email;
		
		if($email == "" || is_null($email) || $email == "temp@no.email") continue;
		
		$data[] = array('email'=>$email,'username'=>$username,'phone'=>(string) $phone , 'appmemberid'=>$appmemberid);
		
	}

    $stmt->close();	
	return $data;
}

function getProductsBuylist($product_name){
	global $mysqli;

	$data = array();
	$product_sql = "";
	//count all orders and customers
	
	//if it has multiple products
	if(is_array($product_name)){
		$i=0;
		foreach ($product_name as $value) {
			$pre_product_sql = $i==0 ? "" : "or";
			$product_sql .=  $pre_product_sql.' product_name like \'%'.$value.'%\'';
			$i++;
		}

	}else{
		$product_sql .= ' product_name like \'%'.$product_name.'%\'';
	}

	$sql = 'SELECT distinct o2.email, 
			          u.email AS appemail, 
			          o2.username, 
			          o2.phone, 
			          o2.appmemberid 
			FROM      ( 
			                   SELECT   o1.email, 
			                            o1.username, 
			                            o1.'.cohortkey.', 
			                            o1.appmemberid 
			                   FROM     Orders AS o1 
			                   WHERE '.$product_sql.'
			                    ) o2 
			LEFT JOIN user u 
			ON u.phone = o2.phone;';

	$stmt = $mysqli->prepare($sql);
    $stmt->execute();

	$stmt->bind_result($email, $appemail, $username, $phone, $appmemberid);

	while($stmt->fetch()){
		$email = $email == "temp@no.email" && !is_null($appemail) ? $appemail : $email;
		$email = $appemail != "" && !is_null($appemail) ? $appemail : $email;
		
		if($email == "" || is_null($email) || $email == "temp@no.email") continue;
		
		$data[] = array('email'=>$email,'username'=>$username,'phone'=>(string) $phone , 'appmemberid'=>$appmemberid);
		
	}

    $stmt->close();	
	return $data;
}

function getListBytime($minTime, $maxTime){
	global $mysqli;

	$data = array();
	  //count all orders and customers
	$sql = 'SELECT distinct o2.email, 
			          u.email AS appemail, 
			          o2.username, 
			          o2.phone, 
			          o2.appmemberid,
			          o2.order_time
			FROM      ( 
			                   SELECT   o1.email, 
			                            o1.username, 
			                            o1.'.cohortkey.', 
			                            o1.appmemberid,
			                            o1.order_time 
			                   FROM     Orders AS o1 
			                   WHERE order_time > "'.$minTime.'" and order_time < "'.$maxTime.'" 
			                    ) o2 
			LEFT JOIN user u 
			ON u.phone = o2.phone;';
	
	$stmt = $mysqli->prepare($sql);
    $stmt->execute();

	$stmt->bind_result($email, $appemail, $username, $phone, $appmemberid,$order_time);

	while($stmt->fetch()){
		$email = $email == "temp@no.email" && !is_null($appemail) ? $appemail : $email;
		$email = $appemail != "" && !is_null($appemail) ? $appemail : $email;
		
		if($email == "" || is_null($email) || $email == "temp@no.email") continue;
		
		$data[] = array('email'=>$email,'username'=>$username,'phone'=>(string) $phone , 'appmemberid'=>$appmemberid, 'order_time'=>$order_time);
		
	}

    $stmt->close();	
	return $data;
}

$kylistId = 'jX3eXK';
$output = getEmailsOvertwo();
//getProductsBuylist( array('Kiwigarden','親子御膳坊','NurturMe','babybio','Organix','幸福米寶','農純鄉','imeal','芭芭拉','歐佳','寶寶'));
//getEmailsOvertwo();

// $output = getProductsBuylist('早餐盒');

// $minTime = (new DateTime())->modify('-75 days')->format('Y-m-d');
// $maxTime = (new DateTime())->modify('-30 days')->format('Y-m-d');

// $output = getListBytime($minTime,$maxTime);



// $today = date("Y_m_d");
// $file = 'ordersOverTwo_'.$today.'.csv';
// $path = __ROOT__ .'/tmp/';
// outputcsvfile($output, $path.$file);
// //print_r($output);

// $email = new PHPMailer();
// $email->IsSMTP();                                // 設定使用SMTP方式寄信        
// $email->SMTPAuth = true;                         // 設定SMTP需要驗證
// // $email->SMTPDebug = 1; 
// $email->SMTPSecure = "tls";                      // Gmail的SMTP主機需要使用SSL連線   
// $email->Host = "smtp.gmail.com";                 // Gmail的SMTP主機        
// $email->Port = 587;                              // Gmail的SMTP主機的port為465      
// $email->CharSet = "utf-8";                       // 設定郵件編碼   
// $email->Encoding = "base64";
// $email->WordWrap = 50;                           // 每50個字元自動斷行
      
// $email->Username = gmailAccount;     // 設定驗證帳號        
// $email->Password = gmailPassword;              // 設定驗證密碼    

// $email->From      = 'morningshop.tw@gmail.com';
// $email->FromName  = 'MorningShop BI';
// $email->Subject   = 'Chris Chen'.$today."  orders to email";
// $email->Body      = $today."  2次回購名單\nChris \n
// \n
// 匯一份名單，多一份希望
// ";
// $email->AddAddress( 'jeffreywu@ouregion.com','Chris Chen' );
// $file_to_attach = $path.$file;
// $email->AddAttachment( $file_to_attach , $file);

// if(!$email->send()) {
//     echo 'Message could not be sent.';
//     echo 'Mailer Error: ' . $email->ErrorInfo;
// } else {
//     echo 'Message has been sent';
// }

// print_r($output);

$kyformat = array();

foreach ($output as $v) {
	$email = $v['email'];
	$id = $v['phone'];
	$phone = $v['phone'];
	$appmemberid = $v['appmemberid'];
	$username = $v['username'];

	$kyformat[] = array('email' => $email,
		"properties"=> 
			array('$first_name' => "　",
				'$last_name' => $username,
				'$phone_number' => $phone, 
				'$id' => $email, 
				)
			);
}


for ($i=0; $i < count($kyformat); $i+=100) { 
	
	$kyformatpage = array_slice($kyformat, $i, 100);
	$kyforamtjson = json_encode($kyformatpage);

	//echo "kypaging: index: $i, kyarray: ".$kyforamtjson."<br><br><br><br><br><br>";
	kylistupdatebatch($kyforamtjson,$kylistId);
}	



//'[ { "email" : "george.washington@example.com", "properties" : { "$first_name" : "George", "Birthday" : "02/22/1732" } }, { "email" : "thomas.jefferson@example.com" } ]';


// echo $kyforamtjson;
//jX3eXK

?>