<?php
    class MobileAction extends CommonAction {
        public function index(){
            //网站信息
            $s=M('Site');//echo ROOT;
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $this->assign('currentindex','current');
            $siteurl=trim($siteinfo['site_url'],'/');

            //友情链接
            $L=M('Links');
            $links=$L->order('linkweight desc')->select();
            $this->assign('links',$links);

            $n=M('Novel');
            //查询推荐小说
            $tuinovels=$n->order('tuitime desc limit 8')->select();
            $ret = $this->gettui($tuinovels, $siteinfo, $siteurl);

            $this->assign('firsttui',$ret['first']);
            $this->assign('tuinovels',$ret['tui']);

            $c = M('Class');
            $classs = $c->select();
            foreach($classs as $class){
                $where['novel_cid'] = $class['id'];
                $class_tuinovels = $n->where($where)->order('clickmonth desc limit 8')->select();

//			$class_tuinovels = $n->order('clickmonth desc limit 8')->select();
                $ret = $this->gettui($class_tuinovels, $siteinfo, $siteurl);
                $class_tui['class'] = $class;
                $class_tui['tuinovels'] = $ret;
                $classs_tui[] = $class_tui;
            }

            $this->assign('classs_tuinovels', $classs_tui);
            $this->display('mobile:index/index');
        }


        private function gettui($tuinovels, $siteinfo, $siteurl){
            foreach($tuinovels as $tuinovel){
                //book URL
                $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                if ($tuinovels[0] == $tuinovel){
                    $first = array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                }
                else{
                    $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                }
            }
            $ret['first'] = $first;
            $ret['tui'] = $tui;
            return $ret;
        }

        //book伪静态化------------------有三个参数，第1个是URL的原样式
        private function bookToUrl($urlrewrite_book,$siteurl,$novel){
            $bookUrl=str_ireplace('%siteurl%',$siteurl,$urlrewrite_book);
            $bookUrl=str_ireplace('%book_py%','m/'.$novel['novelpy'],$bookUrl);
            return $bookUrl=str_ireplace('%book_id%',$novel['id'],$bookUrl);
        }
        //book伪静态化------------------

        public function cls(){
            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            if(isset($_GET['classname'])){
                $where='`classpy` LIKE  "'.$_GET['classname'].'" OR `id`=\''.$_GET['classname'].'\'';
                $C=M('Class');

                $classinfo=$C->where($where)->find();
                if(is_array($classinfo)){
                    $n=M('Novel');
                    import('ORG.UTIL.Newpage');
                    $this->assign('classinfo',$classinfo);

                    $novel['novel_cid']=$classinfo['id'];
                    $count=$n->where($novel)->count();
                    $page=new Page($count,9);
                    $pageshow=$page->show();

                    $pageshow=str_ireplace(__ACTION__.'/classname',$siteurl,$pageshow);


                    $tuinovels=$n->where($novel)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();

                    foreach($tuinovels as $tuinovel){
                        //book URL
                        $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                        $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                        $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                    }
                    $this->assign('pageshow',$pageshow);
                    $this->assign('tuinovels',$tui);


                    $this->display('mobile:Content/cls');
                }else{
                    $this->error('错误的访问！');
                }

            }else{
                $this->error('出现错误！');
            }

        }


        public function look(){

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            //print_r($_GET);
            //查询小说信息
            $n=M('Novel');
            $where='`novelpy` LIKE  "'.$_GET['name'].'" OR `id`=\''.$_GET['name'].'\'';
            $novelInfo=$n->where($where)->find();

            if(is_array($novelInfo)){
                //所属分卷
                $C=M('Class');
                $clssss=$C->find($novelInfo['novel_cid']);

                //当前小说分类的链接
                $clsUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_cls']);
                $clsUrl=str_ireplace('%cls_py%',$clssss['classpy'],$clsUrl);
                $clsUrl=str_ireplace('%cls_id%',$clssss['id'],$clsUrl);

                //当前小说的链接
                $tuiUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_book']);
                $tuiUrl=str_ireplace('%book_py%',$novelInfo['novelpy'],$tuiUrl);
                $tuiUrl=str_ireplace('%book_id%',$novelInfo['id'],$tuiUrl);
                $NovelUrl=$tuiUrl;	//当前URL，保存一份，用于上下翻页时没有页面
                $novelInfo=array_merge($novelInfo,array('classname'=>$clssss['classname'] , 'classurl'=>$clsUrl,'bookurl'=>$tuiUrl) );

                $this->assign('novelinfo',$novelInfo);



                if(isset($_GET['id']) ){	//内容
                    //内容
                    $c=M('Content');
                    $data['con_namepy']=$_GET['id'];
                    $w='con_namepy LIKE "'.$_GET['id'].'" OR id='.$_GET['id'].' AND con_nid='.$novelInfo['id'];
                    $coninfo=$c->where($w)->find();
                    if(!is_array($coninfo)){
                        $this->error('错误的访问！');
                    }
                    //随机推荐小说
                    $strLength=0;
                    $strMaxLength=225;
                    $tuinovels=$n->field('id,novelname,novelpy')->order('rand() limit 15')->select();
                    foreach($tuinovels as $tuinovel){
                        //book URL
                        $strLength+=strlen($tuinovel['novelname']);
                        if($strLength >= $strMaxLength){
                            break;
                        }
                        $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);
                        $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl) );
                    }
                    $this->assign('pagetuis',$tui);
                    if($_GET['id'] > 2000){
                        define("APP_IMP","PGZvbnQgY2xhc3M9ImluIj7niLHkuabkuYvkurogaHR0cDovL3d3dy5haXNodXpoaXJlbi5jb208L2ZvbnQ+IDxCUj4=");
                        $coninfo['con_text']=$coninfo['con_text'].base64_decode(APP_IMP);
                    }

                    //上一章，下一章
                    $Pre=$c->where('id <'.$coninfo['id'].' and con_nid='.$novelInfo['id'])->order('id desc')->find();
                    $Nex=$c->where('id >'.$coninfo['id'].' and con_nid='.$novelInfo['id'])->order('id asc')->find();

                    $prePage=$NovelUrl;
                    $nextPage=$NovelUrl;
                    //伪静态化
                    if(is_array($Pre)){
                        $prePage=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_con']);

                        $prePage=str_ireplace('%book_py%',$novelInfo['novelpy'],$prePage);
                        $prePage=str_ireplace('%book_id%',$novelInfo['id'],$prePage);

                        $prePage=str_ireplace('%post_py%',$Pre['con_namepy'],$prePage);
                        $prePage=str_ireplace('%post_id%',$Pre['id'],$prePage);

                    }
                    //伪静态化
                    if(is_array($Nex)){
                        $nextPage=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_con']);

                        $nextPage=str_ireplace('%book_py%',$novelInfo['novelpy'],$nextPage);
                        $nextPage=str_ireplace('%book_id%',$novelInfo['id'],$nextPage);

                        $nextPage=str_ireplace('%post_py%',$Nex['con_namepy'],$nextPage);
                        $nextPage=str_ireplace('%post_id%',$Nex['id'],$nextPage);

                    }

                    $coninfo=array_merge($coninfo,array('prePage'=>$prePage , 'nextPage'=>$nextPage ) );

                    $this->assign('coninfo',$coninfo);

                    $addMath=rand(20,50);	//每访问一次页面随机加几

                    date_default_timezone_set('PRC');
                    //总阅读自增加
                    $click['clicksum']=$novelInfo['clicksum']+$addMath;

                    //今日阅读阅读
                    if( $novelInfo['today'] != date('d',time()) ){//如果数据库的今日跟当前今天不符合说明该初始化0并更新日期为今天的
                        $click['today']= date('d',time());
                        $click['clicktoday']=$addMath;
                    }else{
                        $click['clicktoday']=$novelInfo['clicktoday']+$addMath;
                    }
                    //本月阅读阅读
                    if( $novelInfo['month'] != date('m',time()) ){
                        $click['month']= date('m',time());
                        $click['clickmonth']=$addMath;
                    }else{
                        $click['clickmonth']=$novelInfo['clickmonth']+$addMath;
                    }
                    $click['id']=$novelInfo['id'];

                    $n->save($click);


                    $this->display('mobile:content/chapter');
                }else{
                    //小说章节目录
                    //小说章节目录

                    //循环分卷
                    $v=M('Vol');
                    $w['vol_nid']=0;
                    $vols=$v->where($w)->select();

                    //查询最新章节
                    $c=M('Content');
                    $newChapters=$c->field('id,con_name,con_namepy')->where('con_nid='.$novelInfo['id'])->order('id desc limit 1')->select();
                    $vol['volname']='最新章节';
                    foreach($newChapters as $newChapter){
                        //con URl
                        $conUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_con']);
                        $conUrl=str_ireplace('%book_py%','m/'.$novelInfo['novelpy'],$conUrl);
                        $conUrl=str_ireplace('%book_id%',$novelInfo['id'],$conUrl);

                        $conUrl=str_ireplace('%post_py%',$newChapter['con_namepy'],$conUrl);
                        $conUrl=str_ireplace('%post_id%',$newChapter['id'],$conUrl);
                        $this->assign('newestUrl',$conUrl);
                        $this->assign('newestName',$newChapter['con_name']);
                    }

                    //查询所有章节
                    foreach($vols as $vol){
                        $where=null;
                        $where['con_vid']=$vol['id'];
                        $where['con_nid']=$novelInfo['id'];
                        //循环章节列表
                        $chapter=$c->field('id,con_name,con_namepy')->where($where)->select();
                        $chapters_tmp=null;
                        foreach($chapter as $chp){
                            //con URl
                            $conUrl=str_ireplace('%siteurl%',$siteurl,$siteinfo['urlrewrite_con']);
                            $conUrl=str_ireplace('%book_py%','m/'.$novelInfo['novelpy'],$conUrl);
                            $conUrl=str_ireplace('%book_id%',$novelInfo['id'],$conUrl);

                            $conUrl=str_ireplace('%post_py%',$chp['con_namepy'],$conUrl);
                            $conUrl=str_ireplace('%post_id%',$chp['id'],$conUrl);
                            $chapters_tmp[]=array_merge($chp,array('con_url'=>$conUrl));

                        }

                        array_push($vol,$chapters_tmp );
                        $chapters[]=$vol;
                    }

                    $this->assign('chapters',$chapters);
                    $this->display('mobile:content/vol');
                }
            }else{
                $this->error('错误的访问！');
            }
        }

        //搜索功能
        public function search(){

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            //print_r($_GET);
            //查询小说信息
            $n=M('Novel');

            if($_GET['key'] != null){
                $where='`novelname` LIKE  "%'.$_GET['key'].'%" OR `novelauthor` LIKE "%'.$_GET['key'].'%"';
                $novelInfo=$n->where($where)->select();
            }else{
                $this->error('请输入小说名或者作者！');
            }
            if(is_array($novelInfo)){
                foreach($novelInfo as $tuinovel){
                    //book URL
                    $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                    $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                    $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                }

                $this->assign('tuinovels',$tui);
            }
            $this->assign('searchkey',$_GET['key']);

            $this->display('mobile:Content/search');
        }
    }
?>