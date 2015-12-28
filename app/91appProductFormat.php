<?php
/**
generate 91 app product import format
**/

define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__ . '/header.php');
require_once (__ROOT__ . '/api/functions.php');
require_once (__ROOT__ . '/config/deconfig.php');


function getAllProduct($cat){
	global $mysqli;
	
	$cat = $cat ? 'where cat = "'.$cat.'"' : '';
	$sql = 'SELECT * From product ' . $cat;

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
	$item = $data->desc;
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

function parseitemSpec($item_spec){
	$doc = new DomDocument;
	$doc->loadHtml($item_spec);

	// //get class item detail
	$finder = new DomXPath($doc);
	$classname="spec";
	$nodes = $finder->query("//*[contains(@class, '$classname')]");

	$spec = getInnerHtmlNode($nodes->item(0)->getElementsByTagName('li')->item(0));	
	$nutrition = getInnerHtmlNode($nodes->item(2)->getElementsByTagName('li')->item(0));	

	return array('spec' => utf8_decode(changeBRlineBreak($spec)), 'nutrition' => utf8_decode(changeBRlineBreak($nutrition)));
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
		return array('status' => 'fail');
	}

	$info = $item_detail = $item_spec = array();
	$doc = new DomDocument;

	$url = "http://www.morningshop.tw/item/".$uitoxAMid;
	$item_detail_url = "http://www.morningshop.tw/api_item/1/".$uitoxAMid;
	$item_spec_url = "http://www.morningshop.tw/api_item/3/".$uitoxAMid;



	// We need to validate our document before refering to the id
	$doc->validateOnParse = true;
	$content = getUrlContent($url);

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
		$info['item_detail'] = itemDetailHTML($item_detail);
		$info['item_spec'] = parseitemSpec($item_spec);
		
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



$data = getAllProduct('保鮮盒');
$output = array();
$fileName = '/tmp/91appProduct';

foreach ($data as $col) {
	$uitoxAMid = $col['uitoxAmid'];

	$product_id = $col['product_id'];
	$product_name = $col['product_name'];
	$qty = 0;
	$price = $col['price'];
	$cost = $col['cost'];
	
	$info = parseUITOXweb($uitoxAMid);

	if ($info['status'] == 'success') {

		//saveimgfromWeb("/tmp/".$product_id.'.jpg',$info['previewUrl']);

		$output[] = array('生活、居家、寵物 > 餐廚 > 餐廚用品 > 微波盒、保鮮盒、罐',/*'商店類別' => */'麥片小物',
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
						/*'商品料號'    => */$product_id,
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