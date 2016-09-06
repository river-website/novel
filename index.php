<?php
	function myIsRedict(){
		$client = $_SERVER['HTTP_USER_AGENT'];
		$url = $_SERVER['HTTP_HOST'];
		$is_include_m = strstr($url,'m.');
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
		if(strstr($url,'m.')){
			$url = preg_replace("/(m\.)/","",$url);
		}else{
			if(strstr($url,'www.')){
				$url = str_replace('www.','m.',$url);
			}else{
				$url = 'm.'.$url;
			}
		}
		return "http://".$url;
	}

	if(myIsRedict()){
		Header('Location:'.myRediectUrl());
	}

	define('THINK_PATH',"./ThinkPHP/");
	define("APP_NAME","home");
	define("APP_PATH","./home/");
	define("APP_DEBUG",true);
	//require 'SiteConfig.php';
	require THINK_PATH.'ThinkPHP.php';
?>
