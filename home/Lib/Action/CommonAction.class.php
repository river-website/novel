<?php
	class CommonAction extends Action{
		protected $common_classs = null;

		protected function isMobile(){
			$client = $_SERVER['HTTP_USER_AGENT'];
			$clientkeywords = array('iphone','mobile','ipod','ipad','android','symbianos','windows phone','phone');
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			$is_mobile = preg_match("/(".implode('|', $clientkeywords).")/i", strtolower($client));
			return $is_mobile;
		}

		protected function getContentByPath($coninfo){
			$path='/home/book/'.$coninfo['con_nid'].'/'.$coninfo['id'];
			$txt=file_get_contents($path);
			return array_merge($coninfo,array('con_text'=>$txt));
		}

		//book伪静态化------------------有三个参数，第1个是URL的原样式
        protected function bookToUrl($urlrewrite_book,$siteurl,$novel){
            $bookUrl=str_ireplace('%siteurl%',$siteurl,$urlrewrite_book);
            return $bookUrl=str_ireplace('%book_id%',$novel['id'],$bookUrl);
        }
        //book伪静态化------------------

        //chapter伪静态化
        protected function chapterToUrl($urlrewrite_book,$siteurl,$novelInfo,$content){
            $chapter=str_ireplace('%siteurl%',$siteurl,$urlrewrite_book);
            $chapter=str_ireplace('%book_id%',$novelInfo['id'],$chapter);
            return $chapter=str_ireplace('%post_id%',$content['id'],$chapter);
        }
        //chapter伪静态化

        //分类伪静态化
        protected function classToUrl($urlrewrite_cls,$siteurl,$class){
            $clsUrl=str_ireplace('%siteurl%',$siteurl,$urlrewrite_cls);
            return $clsUrl=str_ireplace('%cls_id%',$class['id'],$clsUrl);
        }
        //分类伪静态化

		//分类的全部小说链接
		public function map(){
			//内容
			$c=M('Novel');
			$clsId = $_GET['name'];
			$w = 'novel_cid='.$clsId;
			$novelInfos = $c->where($w)->select();
			if(!is_array($novelInfos)){
				$this->error('错误的访问！');
			}
			$this->assign('novelInfos',$novelInfos);

			$class=M('Class');
			$where='id='.$clsId;
			$classInfos = $class->where($where)->select();
			$this->assign('classname',$classInfos);

			//网站信息
			$s=M('Site');
			$is_mobile = $this->isMobile();
			if($is_mobile){
				$siteinfo=$s->find(2);
			}else{
				$siteinfo=$s->find(1);
			}
			$this->assign('siteinfo',$siteinfo);

			$page_path = 'pc:content/map';
			$is_mobile = $this->isMobile();
			if($is_mobile){
				$page_path = 'mobile:content/map';
			}
			$this->display($page_path);
		}

		public function _initialize(){

			//网站信息
			$s=M('Site');
			$is_mobile = $this->isMobile();
			if($is_mobile){
				$siteinfo=$s->find(2);
			}else{
				$siteinfo=$s->find(1);
			}

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
			foreach($classes as $cls){
				$clsUrl=$this->classToUrl($siteinfo['urlrewrite_cls'],$siteurl,$cls);
				$urlArr=array('clsurl'=>$clsUrl);
				$newcls[]=array_merge($cls,$urlArr);
			}

			$this->common_classs = $newcls;
			$this->assign('classes',$newcls);

			$commonurl['mainurl'] = $siteurl;
			$commonurl['classurl'] = $newcls[0]['clsurl'];
			$commonurl['doneurl'] = $siteurl.'/d';
			$commonurl['hisurl'] = $siteurl.'/h';
			$commonurl['searchurl']=$siteurl.'/s/';
			$this->assign('commonurl',$commonurl);
			$this->assign('build_time',date("y-m-d,h:i:s",time()));
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