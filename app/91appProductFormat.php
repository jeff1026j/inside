<?php
/**
generate 91 app product import format
**/
  
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/header.php');
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/deconfig.php');
require_once (__ROOT__ . '/config/aws-config-key.php'); 
require_once (__ROOT__ . '/config/aws.phar');

//upload the photo first
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;


function getAllProduct($cat){
	global $mysqli;
	
	$cat = $cat ? 'where cat = "'.$cat.'"' : '';
	$sql = 'SELECT * From product where  product_id = "201508AG190004492" or product_id = "201508AG200003930" or product_id = "201501AG210000258" or product_id = "201501AG210000239" or product_id = "201501AG210000242" or product_id = "201501AG210000248" or product_id = "201501AG210000254" or product_id = "201501AG210000266" or product_id = "201501AG210000268" or product_id = "201512AG150008779" or product_id = "201512AG100012396" or product_id = "201512AG150008894" or product_id = "201512AG100012442" or product_id = "201511AG060000622" or product_id = "201511AG060001513" or product_id = "201507AG290003769" or product_id = "201507AG290003702" or product_id = "201507AG290003654" or product_id = "201507AG290003505" or product_id = "201411AG210000042" or product_id = "201411AG210000068" or product_id = "201412AG240000966" or product_id = "201412AG240000965" or product_id = "201512AG290015527" or product_id = "201512AG290015656" or product_id = "201412AG190000080" or product_id = "201412AG190000209" or product_id = "201412AG190000109" or product_id = "201511AG170006463" or product_id = "201412AG190000207" or product_id = "201511AG170006372" or product_id = "201412AG190000219" or product_id = "201412AG190000085" or product_id = "201412AG190000176" or product_id = "201412AG190000084" or product_id = "201412AG190000215" or product_id = "201412AG120000035" or product_id = "201412AG120000039" or product_id = "201412AG120000031" or product_id = "201412AG120000040" or product_id = "201506AG150001545" or product_id = "201506AG150001540" or product_id = "201506AG150001537" or product_id = "201506AG150001535" or product_id = "201506AG150001525" or product_id = "201506AG150001523" or product_id = "201506AG150001510" or product_id = "201506AG220001740" or product_id = "201506AG220001877" or product_id = "201506AG220001797" or product_id = "201506AG220001757" or product_id = "201509AG250006571" or product_id = "201512AG280007968" or product_id = "201512AG280007916" or product_id = "201512AG280007914" or product_id = "201509AG170005886" or product_id = "201509AG170004994" or product_id = "201509AG170005890" or product_id = "201601AG060002409" or product_id = "201601AG060002419" or product_id = "201601AG060002540" or product_id = "201601AG060002536" or product_id = "201601AG060002357" or product_id = "201601AG060002363" or product_id = "201601AG060002344" or product_id = "201601AG060002337" or product_id = "201501AG140000237" or product_id = "201501AG140000234" or product_id = "201501AG200000509" or product_id = "201501AG140000224" or product_id = "201501AG200000493" or product_id = "201501AG200000511" or product_id = "201412AG250000892" or product_id = "201412AG250000893" or product_id = "201412AG250000890" or product_id = "201504AG230001827" or product_id = "201505AG150001350" or product_id = "201507AG220000582" or product_id = "201504AG130000514" or product_id = "201509AG230004331" or product_id = "201509AG230003993" or product_id = "201509AG230004312" or product_id = "201509AG230003953" or product_id = "201509AG230003296" or product_id = "201509AG230003311" or product_id = "201509AG230004310" or product_id = "201509AG230003750" or product_id = "201509AG230003680";';

	$stmt = $mysqli->query($sql); 
	$data = array();
	
	while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
		$data[] = $row;
	}

	$stmt->close();

	return $data;
}


function getInnerHtmlNode( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML; 
} 



function getUrlContent($url){
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$updateUrlContent = curl_exec($curl);
	curl_close($curl);

	return $updateUrlContent;
}
		  
function itemDetailHTML($data){
	$item = $data;
	$desc_content = '<div class="item-detail" style="outline: 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin: 0;padding: 0;border: 0;font-size: 100%;vertical-align: baseline;background: transparent;zoom: 1;">
            <div class="container" style="outline: 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin: 30px
               auto;padding: 0
               0 30px;border: 0;font-size: 100%;vertical-align: baseline;background: transparent;zoom: 1;position: relative;border-bottom-color: #ccc;border-bottom-width: 1px;border-bottom-style: solid;">
        ';
	foreach ($item as $value) {
		$type = (int) $value->type;
		if($type==1||$type==3){
			$desc_content .= '<p class="desc" style="outline: 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin: 5px
                  0;padding: 0;border: 0;font-size: 100%;vertical-align: baseline;background: transparent;line-height: 1.9;word-wrap: break-word;">'.$value->desc.'</p>';
		}else if ($type == 2) {
			$marginbottom=($value->pic_pos==0)?'margin-bottom: 15px !important;':'';
			$desc_content .= '<img itemprop="image" src="'.$value->pic.'" style="outline: 0;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin: 0
                  auto;padding: 0;border: 0;font-size: 100%;vertical-align: baseline;background: transparent;max-width: 100%;display: block; ' .$marginbottom. ' ">';
		}

	}
	$desc_content .= '  </div></div>';
	return $desc_content;
}

function changeBRlineBreak($text){

	$breaks = array("<br />","<br>","<br/>","<br />","&lt;br /&gt;","&lt;br/&gt;","&lt;br&gt;");
	$text = str_ireplace($breaks, "\n", $text);  

	return $text;
}

function uitoxtoimgurl($imgurl){
	$client_id = "57249ab69c7794d";//"3b9c7d61b701011";
	$image = file_get_contents($imgurl);
	$timeout = 50;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
	curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => base64_encode($image)));

	$reply = curl_exec($ch);
	curl_close($ch);

	$reply = json_decode($reply);

	if ($reply->data->link == "") { //second chance
		$image = file_get_contents($imgurl);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => base64_encode($image)));

		$reply = curl_exec($ch);
		curl_close($ch);

		$reply = json_decode($reply);
	}
	return $reply->data->link;

}


function savetos3($url){

	$client = S3Client::factory(array(
	    'key'    => AWS_KEY,
	    'secret' => AWS_SECRET
	));

	$uniid = uniqid();	
	$tempImgLocal = "/tmp/$uniid.jpg";
	copy($url, $tempImgLocal);
	echo "string";
	// Upload an object by streaming the contents of a file
	// $pathToFile should be absolute path to a file on disk

	$result = $client->putObject(array(
	    'Bucket'     => AWS_BUCKET,
	    'Key'        => $uniid.'.jpg',
	    'SourceFile' => $tempImgLocal,
	    'ACL'    => CannedAcl::PUBLIC_READ_WRITE,
	    'CacheControl' => 'max-age=94608000',
        'ContentType' => 'image/jpeg'
	));
	echo "string2";
	unlink($tempImgLocal);
	$photopath = $result['ObjectURL'];
	echo "string3";
	return $photopath;

}

function parseitemdetail($data){
	$doc = new DOMDocument();
	
	try{
		@$doc->loadHtml($data);
	
	}catch(Exception $e) {

		return null;
	}

	$xpath = new DOMXPath($doc);

	$div = $xpath->query('//div[@class="item-detail"]');
	$div = $div->item(0);

	$xpath2 = new DOMXPath($doc);
	$div2 = $xpath->query('//div[@class="desc"]');

	$output = array();

	$imgnodes = $div->getElementsByTagName('img');

	$i = 0;
	
	foreach ($imgnodes as $img) {
		
		$imgData = new stdClass;
    	$imgData->type=2;
		$imgData->pic_pos = $i;   
    	
    	$imgData->pic = savetos3($img->attributes->getNamedItem("data-original")->value);

    	$output[] = $imgData;
    	$i++;
	}

	//add 5 selection in it
	$imgData = new stdClass;
	$imgData->type=2;
	
	$imgData->pic = "http://i.imgur.com/wGn9bmc.jpg";
	$output[] = $imgData;
	

	foreach ($div2 as $desc) {
		$Data = new stdClass;
    	$Data->type=1;
    	$Data->desc = $desc->nodeValue;

    	$output[] = $Data;			
	}

	// print_r($output);
	// echo $p->nodeValue;

	return $output;
}

function parseitemSpec($item_spec){
	$doc = new DomDocument;
	$spec = null;
	$nutrition = null;

	try{
		$doc->loadHtml($item_spec);
	
	}catch(Exception $e) {

		return null;
	}
	
	
	// //get class item detail
	$finder = new DomXPath($doc);
	$classname="spec";
	$nodes = $finder->query("//*[contains(@class, '$classname')]");
	
	if ($nodes->length > 3) {
		$spec = getInnerHtmlNode($nodes->item(0)->getElementsByTagName('li')->item(1));	
		$nutrition = getInnerHtmlNode($nodes->item(2)->getElementsByTagName('li')->item(1));	
	}
	
	//return array('spec' => utf8_decode(changeBRlineBreak($spec)), 'nutrition' => utf8_decode(changeBRlineBreak($nutrition)));
	return array('spec' => strip_tags(changeBRlineBreak($spec)), 'nutrition' => strip_tags(changeBRlineBreak($nutrition)));
}

/*
	parse $uitox product website
	output product info
	1.previewUrl
	2.item-detail
	3.item_spec
	4.item_key_point
	5.sug_price
	6.error

*/
function parseUITOXweb($uitoxAMid){
	
	if (!$uitoxAMid || $uitoxAMid == 'NULL') {
		// echo "hahaha";
		return array('status' => 'fail');
	}
	// echo "bobobo";
	$info = $item_detail = $item_spec = array();
	$doc = new DomDocument;

	$url = "http://www.morningshop.tw/item/".$uitoxAMid;
	$item_detail_url = "http://www.morningshop.tw/api_item/1/".$uitoxAMid;
	$item_spec_url = "http://www.morningshop.tw/api_item/3/".$uitoxAMid;



	// We need to validate our document before refering to the id
	$doc->validateOnParse = true;
	$content = getUrlContent($url);

	// print_r($content);
	try{
		
		$doc->loadHtml($content);
		
		$item_detail = json_decode(getUrlContent($item_detail_url));
		
		$item_spec = getUrlContent($item_spec_url);
		


	} catch(Exception $e) {
		$doc = null;

		$info['status'] = "fail";
	}

	if ($doc) {
		$info['status'] = "success";
		//$info['previewUrl'] = $doc->getElementById('item_photo')->getAttribute('src');
		$info['item_detail'] = itemDetailHTML(parseitemdetail($content));//itemDetailHTML($item_detail);
		$info['item_spec'] = parseitemSpec($content);
		
		if (preg_match('/var product_data=(.*?);/m', $content, $matches)) {

		    $item_match = json_decode($matches[1]);
			foreach ($item_match->slogan_info as $value) {

				$item_key_point .= 	$value->SLOGAN."\n";
			}       
		}
		$info['item_key_point'] = $item_key_point; 
		
		//get suggestion price
		$xpath = new DOMXpath($doc);
		$sug_price = @$xpath->query('//span[@itemprop="highPrice"]')->item(0)->nodeValue;
		
		$info['sug_price'] = $sug_price;
	}

	if (!$info['item_spec']['spec'] && !$info['item_spec']['nutrition']) {
		$info['status'] = "fail";
	}

	return $info;
}

/*
	save image with name
*/
function saveimgfromWeb($filename,$url){

	$ch = curl_init($url);
	$fp = fopen(__ROOT__.$filename, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

}


function outputcsvfile($data,$re_filepath){
	
	$fp = fopen( __ROOT__ . $re_filepath, 'w');
	
	// fwrite( $fp, chr(255) . chr(254)); 
	//fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

	foreach ($data as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);	
}


$data = getAllProduct('沖泡即食品');
echo "data 數量： ".count($data)."<br>";
print_r($data);
$output = array();
$fileName = '/tmp/91appProduct';

// print_r($data);

// print_r(parseUITOXweb('201411AM210000002'));

foreach ($data as $col) {
	$uitoxAMid = $col['uitoxAmid'];

	$product_id = $col['product_id'];
	$storage_id = $col['storage_id'];
	$product_name = $col['product_name'];
	$qty = 0;
	$price = $col['price'];
	$cost = $col['cost'];
	// echo "<br>";echo "<br>";echo "<br>";
	// echo "start: id: ".$product_id." name: ".$product_name;

	$info = parseUITOXweb($uitoxAMid);

	// echo "<br>";echo "<br>";echo "<br>";
	// print_r($info);	

	if ($info['status'] == 'success') {

		//saveimgfromWeb("/tmp/".$product_id.'.jpg',$info['previewUrl']);

		$output[] = array('美食、名特產 > 零食、蜜餞 > 果乾/堅果',/*'商店類別' => */'餅乾',
						/*'商品名稱' => */$product_name,
						/*'數量'    => */$qty,
						/*'建議售價' => */$info['sug_price'],
						/*'售價'    => */$price,
						/*'成本'    => */$cost,
						/*'一次最高購買量' => */'',
						/*'銷售開始日期' => */'',
						/*'銷售結束日期' => */'',
						/*'交期'       => */'一般',
						/*'預定出貨日期' => */'',
						/*'付款完成後幾天出貨' => */'',
						/*'物流設定'    => */'1',//宅配＆超商
						/*'付款方式'    => */'信用卡一次付款',
						/*'品牌'       => */'',
						/*'型號'       => */'',
						/*'商品選項'    => */'無',
						/*'商品選項一'  => */'',
						/*'商品選項二'  => */'',
						/*'商品料號'    => */$storage_id,
						/*'商品選項圖檔' => */'',
						/*'商品規格'    => */'',
						/*'商品圖檔一'   => */"$product_id.jpg",
						/*'商品圖檔二'   => */'',
						/*'商品圖檔三'   => */'',
						/*'商品圖檔四'   => */'',
						/*'商品圖檔五'   => */'',
						/*'商品圖檔六'   => */'',
						/*'商品圖檔七'   => */'',
						/*'商品圖檔八'   => */'',
						/*'商品圖檔九'   => */'',
						/*'商品圖檔十'   => */'',
				$info['item_spec']['spec'].$info['item_spec']['nutrition'],
				$info['item_key_point'],
				$info['item_detail'],
				'早餐吃麥片'

		 );
	}
	
}



// //output format
// //平台類別	商店類別	商品名稱	數量	建議售價	售價	成本	一次最高購買量	銷售開始日期	銷售結束日期	交期	預定出貨日期	付款完成後幾天出貨	物流設定	付款方式	品牌	型號	商品選項	商品選項一	商品選項二	商品料號	商品選項圖檔	商品規格	商品圖檔一	商品圖檔二	商品圖檔三	商品圖檔四	商品圖檔五	商品圖檔六	商品圖檔七	商品圖檔八	商品圖檔九	商品圖檔十	銷售重點	商品特色	詳細說明	商店名稱
// // mb_convert_encoding('美食、名特產 ＞ 茶、咖啡、沖泡 ＞ 纖食/穀類', 'UTF-16LE', 'UTF-8'),
// // 						mb_convert_encoding($info['item_spec']['spec'].$info['item_spec']['nutrition'], 'UTF-16LE', 'UTF-8'),
// // 						mb_convert_encoding($info['item_key_point'], 'UTF-16LE', 'UTF-8'),
// // 						mb_convert_encoding($info['item_detail'], 'UTF-16LE', 'UTF-8'),
// // 						mb_convert_encoding('早餐吃麥片', 'UTF-16LE', 'UTF-8')


$today = date("Y_m_d");
$newfilename = $fileName.'_'.$today.'.csv';
outputcsvfile($output, $newfilename);

//$info = parseUITOXweb('201501AM280000764');


?>


<h3>處理資料筆數： <?=count($output)?></h3>
<h4>日期：<?=Date("Y-m-d")?></h4>

<div class="top-buffer"></div>
<form method="get" action="<?=$newfilename?>">
	檔案路徑：<?=$newfilename?> <br><br>
	<input type="submit" class="btn btn-success" value="下載 csv">
</form>

<?
//headers
// header('Pragma: public');
// header('Expires: 0');
// header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
// header('Content-Description: File Transfer');
// header('Content-Type: text/csv');
// header('Content-Disposition: attachment; filename=export123.csv;');
// header('Content-Transfer-Encoding: binary'); 

// //open file pointer to standard output
// $fp = fopen('php://output', 'w');

// //add BOM to fix UTF-8 in Excel
// fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
// if ($fp)
// {
//   foreach ($output as $fields) {
// 	    fputcsv($fp, $fields);
// 	}
// }

// fclose($fp);

?>
<?php require_once( __ROOT__ . '/footer.php');?>


