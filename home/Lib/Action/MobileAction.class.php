<?php
    import("ORG.Util.Newpage");
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
                $ret = $this->gettui($class_tuinovels, $siteinfo, $siteurl);
                $class_tui['class'] = $class;
                $class_tui['tuinovels'] = $ret;
                $classs_tui[] = $class_tui;
            }

            $this->assign('classs_tuinovels', $classs_tui);
            $this->display('mobile:index/index');
        }

        private function gettui($tuinovels, $siteinfo, $siteurl){
            $first = null;
            $tui = null;
            foreach($tuinovels as $tuinovel){
                //book URL
                $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                if ($tuinovels[0] == $tuinovel){
                    $first = array_merge(
                        $tuinovel ,
                        array('tuiUrl'=>$tuiUrl,'des'=>$des)
                    );
                }
                else{
                    $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des,
                        'classname'=>$this->common_classs[$tuinovel['novel_cid']]['classname'],
                        'classurl'=>$this->common_classs[$tuinovel['novel_cid']]['clsurl'])
                    );
                }
            }
            $ret['first'] = $first;
            $ret['tui'] = $tui;
            return $ret;
        }

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
                    $this->assign('classinfo',$classinfo);

                    $novel['novel_cid']=$classinfo['id'];
                    $count=$n->where($novel)->count();
                    $page=new NewPage($count,10);
                    $pageshow=$page->show();

                    $pageshow=str_ireplace(__ACTION__.'/classname',$siteurl.'/c',$pageshow);


                    $tuinovels=$n->where($novel)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();

                    foreach($tuinovels as $tuinovel){
                        //book URL
                        $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                        $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                        $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                    }
                    $this->assign('pageshow',$pageshow);
                    $this->assign('tuinovels',$tui);

                    $this->display('mobile:content/cls');
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
            $where='`id`=\''.$_GET['name'].'\'';
            $novelInfo=$n->where($where)->find();

            if(is_array($novelInfo)){
                //所属分卷
                $C=M('Class');
                $clssss=$C->find($novelInfo['novel_cid']);

                //当前小说分类的链接
                $clsUrl = $this->classToUrl($siteinfo['urlrewrite_cls'],$siteurl,$clssss);
                //当前小说的链接
                $tuiUrl = $this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$novelInfo);
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
                    $tuinovels=$n->field('id,novelname')->order('rand() limit 15')->select();
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

                    //上一章，下一章
                    $Pre=$c->where('id <'.$coninfo['id'].' and con_nid='.$novelInfo['id'])->order('id desc')->find();
                    $Nex=$c->where('id >'.$coninfo['id'].' and con_nid='.$novelInfo['id'])->order('id asc')->find();

                    $prePage=$NovelUrl;
                    $nextPage=$NovelUrl;
                    //伪静态化
                    if(is_array($Pre))
                        $prePage = $this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelInfo,$Pre);
                    
                    //伪静态化
                    if(is_array($Nex))
                        $nextPage = $this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelInfo,$Nex);
                    
                    $coninfo=array_merge($coninfo,array('prePage'=>$prePage , 'nextPage'=>$nextPage ));

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
                        $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelInfo,$newChapter);
                        $this->assign('newestUrl',$conUrl);
                        $this->assign('newestName',$newChapter['con_name']);
                    }

                    //查询所有章节
                    $firstUrl = null;
                    $firstName = null;
                    foreach($vols as $vol){
                        $where=null;
                        $where['con_vid']=$vol['id'];
                        $where['con_nid']=$novelInfo['id'];
                        //循环章节列表
                        $chapter=$c->field('id,con_name,con_namepy')->where($where)->select();
                        $chapters_tmp=null;
                        foreach($chapter as $chp){
                            //con URl
                            $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelInfo,$chp);
                            $chapters_tmp[]=array_merge($chp,array('con_url'=>$conUrl));

                        }
                        $firstUrl = $chapters_tmp[0]['con_url'];
                        array_push($vol,$chapters_tmp );
                        $chapters[]=$vol;
                    }

                    $this->assign('firstUrl',$firstUrl);
                    $this->assign('chapters',$chapters);
                    $this->display('mobile:content/vol');
                }
            }else{
                $this->error('错误的访问！');
            }
        }


        public function done(){

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');
            
            $n=M('novel');
            $count=$n->where('novelstate=1')->count();
            $page=new NewPage($count,10);
            $pageshow=$page->show();
            $pageshow=str_ireplace(__ACTION__,$siteurl.'/d',$pageshow);
            $this->assign('pageshow',$pageshow);
            $donenovels=$n->where('novelstate=1')->order('novelwords desc')->limit($page->firstRow.','.$page->listRows)->select();
            foreach($donenovels as $donenovel){
                //book URL
                $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$donenovel);

                $des=mb_substr($donenovel['noveldes'],0,60,'utf-8')."...";
                $tui[]=array_merge($donenovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
            }    
            $this->assign('pageshow',$pageshow);
            $this->assign('tuinovels',$tui); 
            $this->display('mobile:content/done');       
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

            $this->display('mobile:content/search');
        }

        public function getState() {
            $c = M('caiji');
            $caiji = $c->select();
            if (count($caiji) > 0) return true;
            else return false;
        }

        public function chapter_build($n, $c, $content, $novelInfo, $siteurl, $siteinfo, $NovelUrl) {
            if (file_exists(HTML_PATH . 'pc/' . $novelInfo['id'] . '/' . $content['id'] . '.html')) return;
            //随机推荐小说
            $strLength = 0;
            $strMaxLength = 225;
            $tuinovels = $n->field('id,novelname')->order('rand() limit 15')->select();
            $tui = null;
            foreach ($tuinovels as $tuinovel) {
                //book URL
                $strLength+= strlen($tuinovel['novelname']);
                if ($strLength >= $strMaxLength) {
                    break;
                }
                $tuiUrl = $this->bookToUrl($siteinfo['urlrewrite_book'], $siteurl, $tuinovel);
                $tui[] = array_merge($tuinovel, array('tuiUrl' => $tuiUrl));
            }
            $this->assign('pagetuis', $tui);
            //上一章，下一章
            $Pre = $c->where('id <' . $content['id'] . ' and con_nid=' . $novelInfo['id'])->order('id desc')->find();
            $Nex = $c->where('id >' . $content['id'] . ' and con_nid=' . $novelInfo['id'])->order('id asc')->find();
            $prePage = $NovelUrl;
            $nextPage = $NovelUrl;
            //伪静态化
            if(is_array($Pre))
                $prePage = $this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelinfo,$Pre);

            //伪静态化
            if(is_array($Nex))
                $nextPage = $this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelinfo,$Nex);    

            $coninfo = array_merge($content, array('prePage' => $prePage, 'nextPage' => $nextPage));
            $this->assign('coninfo', $coninfo);
            $this->buildHtml($coninfo['id'], HTML_PATH . 'mobile/' . $novelInfo['id'] . '/', 'mobile:content/chapter');
        }
        public function novel_build($n, $c, $v, $cls, $siteinfo, $siteurl, $novelInfo) {
            $conents = $c->where('con_nid=' . $novelInfo['id'])->select();
            $files = scandir(HTML_PATH . 'pc/' . $novelInfo['id']);
            if ((count($files) - 2) == (count($conents) + 1)) return;
            //所属分卷
            $clssss = $cls->find($novelInfo['novel_cid']);
            //当前小说分类的链接
            $clsUrl = $this->classToUrl($siteinfo['urlrewrite_cls'],$siteurl,$clssss);
            //当前小说的链接
            $tuiUrl = $this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$novelinfo);
            $NovelUrl = $tuiUrl; //当前URL，保存一份，用于上下翻页时没有页面
            $novelInfo = array_merge($novelInfo, array('classname' => $clssss['classname'], 'classurl' => $clsUrl, 'bookurl' => $tuiUrl));
            $this->assign('novelinfo', $novelInfo);
            //简介静态化
            //小说章节目录
            //小说章节目录
            $vols = $v->where('vol_nid=0')->select();
            //查询最新章节
            $newChapters = $c->field('id,con_name')->where('con_nid=' . $novelInfo['id'])->order('id desc limit 1')->select();
            $vol['volname'] = '最新章节';
            foreach ($newChapters as $newChapter) {
                //con URl
                $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelinfo,$newChapter);
                $this->assign('newestUrl', $conUrl);
                $this->assign('newestName', $newChapter['con_name']);
            }
            //查询所有章节
            $firstUrl = null;
            $firstName = null;
            foreach ($vols as $vol) {
                $where = null;
                $where['con_vid'] = $vol['id'];
                $where['con_nid'] = $novelInfo['id'];
                //循环章节列表
                $chapter = $c->field('id,con_name')->where($where)->select();
                $chapters_tmp = null;
                foreach ($chapter as $chp) {
                    //con URl
                    $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelinfo,$chp);
                    $chapters_tmp[] = array_merge($chp, array('con_url' => $conUrl));
                }
                $firstUrl = $chapters_tmp[0]['con_url'];
                array_push($vol, $chapters_tmp);
                $chapters[] = $vol;
            }
            $this->assign('firstUrl', $firstUrl);
            $this->assign('chapters', $chapters);
            $this->buildHtml('index', HTML_PATH . 'mobile/' . $novelInfo['id'] . '/', 'mobile:content/vol');
            // 所有章节静态化
            foreach ($conents as $content) {
                if (!$this->getState()) return;
                $this->chapter_build($n, $c, $content, $novelInfo, $siteurl, $siteinfo, $NovelUrl);
            }
        }
        public function novels_build() {
            ignore_user_abort(false);
            ini_set('max_execution_time', '0');
            ini_set('memory_limit', '500M');
            $start_id = $_GET['startId'];
            $end_id = $_GET['endId'];
            if ($start_id == 0) $id = 1;
            else $id = $start_id;
            if ($id > $end_id) return;
            //网站信息
            $s = M('Site');
            $siteinfo = $s->find(1);
            $this->assign('siteinfo', $siteinfo);
            $siteurl = trim($siteinfo['site_url'], '/');
            // 获取类别信息
            $cls = M('Class');
            // 所有章节
            $c = M('Content');
            //循环分卷
            $v = M('Vol');
            //获取小说、
            $n = M('Novel');
            $novels = $n->where('id=' . $id)->select();
            $novelInfo = $novels[0];
            if (!$this->getState()) return;
            $this->novel_build($n, $c, $v, $cls, $siteinfo, $siteurl, $novelInfo);
            $id+= 1;
            redirect('/novel/index.php/mobile/novels_build/startId/'.$id.'/endId/'.$end_id);
        }
    }
?>