<?php
Class UITOX_API
{	

	//Uitox API host domain name
	protected $uitox_api_host = "https://uxapi.uitox.com";
	
	//Uitox API account
	protected $uitox_api_account = "morningshop";	
		
	//Uitox API password
	protected $uitox_api_password = "UK3737ujM7";	
	
	//Platform_id
	protected $platform_id = "AW000780";

	//Uitox Key1
	protected $uitox_key_1 = "9f06ea04845d1f740401a6e6b2e52d22ac2d314a3d138eb8f61ebeffb1d6ee9A";	
	
	//Uitox Key2
	protected $uitox_key_2 = "dee5cb4a2c4b5b61a729337fc9153daf6387a54b765877752ca36b44a721f469";

	public function __construct()
    {        
    }
	
	protected function error_handler($msg = null)
	{
		throw new exception($msg);
	}
	
	public function set_test_env()
	{		
		$this->uitox_api_host = "https://uxapi.uitoxbeta.com";		
	}
		
	public function call_api($api = null, $api_version = null, $data = null, $method = "POST", $proxy = false, $proxyauth = false)
	{
		if (is_null($api) || is_null($api_version) || is_null($data))
		{
			$this->error_handler(__METHOD__ . " input error");
		}
		
		//setup url
		$url = $this->uitox_api_host . $api;
		
		$post_data = array(
			"account" => $this->uitox_api_account,
			"password" => $this->uitox_api_password,
			"platform_id" => $this->platform_id,
			"version" => $api_version,
			"data" => $data
		);

		// 產生檢查碼 cv	
		$cv = $this->get_cv($post_data);

		// 檢查碼 hash 後放入傳送陣列中	
		$post_data["cv"] = hash_hmac('sha256', $cv, "9f06ea04845d1f740401a6e6b2e52d22ac2d314a3d138eb8f61ebeffb1d6ee9A");

		$curl = curl_init();		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	
		if ($proxy !== false)
		{
			curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
			curl_setopt($curl, CURLOPT_PROXY, $proxy);			
		}
		
		if ($proxyauth !== false)
		{
			curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyauth);
		}
		
		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1); 				
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data)); 
				break;
				
			case "PUT":
				curl_setopt($curl, CURLOPT_POST, 1); 
				break;
				
			default:
				$url = sprintf("%s?%s", $url, http_build_query($data));        
		}
		
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HEADER, '0');   
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, '1'); 
		
		$result = curl_exec($curl);		
		
		$curl_info = curl_getinfo($curl);
		
		if($curl_info['http_code'] != '200')
		{
			$curl_error_msg = 'curl_post http_code != 200, ';
			$curl_error_msg .= PHP_EOL;
			$curl_error_msg .= 'curl_error=>['. curl_error($curl) .'],';
			$curl_error_msg .= PHP_EOL;
			$curl_error_msg .= 'info=>['. json_encode($curl_info) .'],'; 
			$curl_error_msg .= PHP_EOL;
			$curl_error_msg .= 'post_data=>['. json_encode($post_data);
			$curl_error_msg .= PHP_EOL;

			$this->error_handler($curl_error_msg);
		}
		
		curl_close($curl);
		
		//decode result
		$result = $this->api_input_decode($result);
		
		return $result;
	}
	
	protected function api_input_encode($json_data)
    {
		// define the key
		$key2 = pack("H*",$this->uitox_key_2);
		$text = json_encode($json_data, JSON_UNESCAPED_UNICODE);		
		$en_out = $this->api_encrypt($text, $key2);
		return $en_out;
	}
	
	protected function api_input_decode($json_data)
    {
		$de_out = $json_data;
		if (!is_array(json_decode($json_data, true)))
		{
			// define the key
			$key2 = pack("H*",$this->uitox_key_2);		
			$de_out = $this->api_decrypt($json_data, $key2);
		}
		
		return $de_out;
	}
	
	protected function api_encrypt($str, $key2)
    {
		$key1 = pack("H*",$this->uitox_key_1);		
		$block_size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
		$pad = $block_size - (strlen($str) % $block_size);
		$str .= str_repeat(chr($pad),$pad);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size,MCRYPT_DEV_URANDOM);
		$crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key1,$str,MCRYPT_MODE_CBC,$iv);
		$hmac = hash_hmac("sha256",$iv.$crypt,$key2,true);
		$output = bin2hex($iv.$crypt.$hmac);
		return $output;
	}
	
	protected function api_decrypt($str, $key2)
    {
		$message = pack("H*",$str);
		$key1 = pack("H*",$this->uitox_key_1);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
		$iv = substr($message,0,$iv_size);
		$hmac_size = strlen(hash_hmac("sha256","","",true));
		$hmac = substr($message,-$hmac_size);
		$crypt = substr($message,$iv_size,-$hmac_size);
		$crypt_hmac = hash_hmac("sha256",$iv.$crypt,$key2,true);
		$text = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key1,$crypt,MCRYPT_MODE_CBC,$iv);
		$pad = ord($text[strlen($text)-1]);
		$text = substr($text,0,-1*$pad);
		return $text;
	}
	
	
	public function get_cv($array_cv)
	{

		global $cv;

    	if ( is_array($array_cv) )
    	{
	 		ksort($array_cv);

			foreach ($array_cv as $index => $hstr1 )
			{
				if ( is_array($hstr1) )
				{
					$this->get_cv($hstr1);
				}
				else 
				{
					if ($index != "cv")
					{
		   				$cv .= $index . "" . $hstr1 ."";
					} 
				}
   			}
   	 	}
	
		return $cv;
		
	}
		
}

//end file
