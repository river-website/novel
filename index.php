<?php

	function myIsRedict(){
		$client = $_SERVER['HTTP_USER_AGENT'];
		#$url = $_SERVER['HTTP_HOST'];
		$url = $_SERVER['REQUEST_URI'];
		#$is_include_m = strpos($url,'/m');
		$is_include_m = strpos($url,'m.');
		$clientkeywords = array('iphone','mobile','ipod','ipad','android','symbianos','windows phone','phone');
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		$is_mobile = preg_match("/(".implode('|', $clientkeywords).")/i", strtolower($client));
		$flag = false;
		if (!$is_mobile && $is_include_m){
			$flag = true;
		}

		if($is_mobile && !$is_include_m){
			$flag = true;
		}
		return $flag;
	}

	function myRediectUrl(){
		$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if(strpos($url,'m.')){
			$url = preg_replace("/(m\.)/","",$url);
			#$url = preg_replace("/(\/m)/","",$url);
		}else{
			#$url = $url.'m';
			$url = 'm.'.$url;
			#$url = str_replace('localhost/novel', 'localhost/novel/m', $url);
		}
		return "http://".$url;
	}

	// if(myIsRedict()){
	// 	header(myRediectUrl());
	// }
	
	define('THINK_PATH',"./ThinkPHP/");
	define("APP_NAME","home");
	define("APP_PATH","./home/");
	define("APP_DEBUG",true);
	//require 'SiteConfig.php';
	require THINK_PATH.'ThinkPHP.php';
?>