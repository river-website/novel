<?php
	class CommonAction extends Action{
		protected $common_classs = null;

		protected function myIsRedict(){
			$client = $_SERVER['HTTP_USER_AGENT'];
			#$url = $_SERVER['HTTP_HOST'];
			$url = $_SERVER['REQUEST_URI'];
			$is_include_m = strpos($url,'/m');
			#$is_include_m = strpos($url,'m.');
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

		protected function myRediectUrl(){
			$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if(strpos($url,'m')){
				$url = preg_replace("/(m\.)|(\/m)/","",$url);
			}else{
				$url = $url.'m';
				#$url = 'm.'.$url;
			}
			return "http://".$url;
		}

		public function _initialize(){
			if($this->myIsRedict()){
				redirect($this->myRediectUrl());
			}

			//网站信息
			$s=M('Site');
			$siteinfo=$s->find(1);
			
			if(isset($siteinfo['gogo'])){
				$gourl='http://www.rennhuang8.com';
				$this->assign('gourl',$gourl);
				$this->display('Index:gourl');
				exit();
			}
			
			//栏目伪静态
			$c=M('Class');
			$classes=$c->select();
			$siteurl=trim($siteinfo['site_url'],'/');
			if (get_class($this) == 'indexAction'){
				foreach($classes as $cls){
					$clsUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_cls']);
					$clsUrl=str_ireplace('%cls_py%','c/'.$cls['classpy'],$clsUrl);
					$clsUrl=str_ireplace('%cls_id%',$cls['id'],$clsUrl);
					$urlArr=array('clsurl'=>$clsUrl);
					$newcls[]=array_merge($cls,$urlArr);
				}
			}else{
				foreach($classes as $cls){
					$clsUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_cls']);
					$clsUrl=str_ireplace('%cls_py%','m/c/'.$cls['classpy'],$clsUrl);
					$clsUrl=str_ireplace('%cls_id%',$cls['id'],$clsUrl);
					$urlArr=array('clsurl'=>$clsUrl);
					$newcls[]=array_merge($cls,$urlArr);
				}
			}

			$this->common_classs = $newcls;
			$this->assign('classes',$newcls);

			//热词搜索
			if($siteinfo['hotkeyopen']){
				$N=M('Novel');
				$novels=$N->field('novelname,novelauthor')->order('rand() limit 5')->select();
				
				$strLength=0;
				$strMaxLength=99;	//限制其长度，超过长度会影响其样式
				foreach($novels as $novel){
					$strLength+=strlen($novel['novelname']);
					if($strLength >= $strMaxLength){
						break;
					}
					$HotNovel[]=array('url'=>$siteinfo['searchurl'].urlencode($novel['novelname']),'keyword'=>$novel['novelname']);
					
					$strLength+=strlen($novel['novelauthor']);
					if($strLength >= $strMaxLength){
						break;
					}
					$HotNovel[]=array('url'=>$siteinfo['searchurl'].urlencode($novel['novelauthor']),'keyword'=>$novel['novelauthor']);
					
				}
				$this->assign('hotnovels',$HotNovel);
			}else{
				$siteinfo['searchurl'];
				$hotKeyarr=split(',',$siteinfo['hotkey']);
				$hotKeyarr=array_filter($hotKeyarr);
				foreach($hotKeyarr as $hkey){
					$HotNovel[]=array('url'=>$siteinfo['searchurl'].urlencode($hkey),'keyword'=>$hkey);
				}
				$this->assign('hotnovels',$HotNovel);
			}
		}
	}
?>