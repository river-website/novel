<?php
    import("ORG.Util.Newpage");
    class MobileAction extends CommonAction {
        public function index(){
            //网站信息
            $s=M('Site');//echo ROOT;
            $siteinfo=$s->find(2);
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

            foreach($this->common_classs as $class){
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
                            'classname'=>$this->common_classs[$tuinovel['novel_cid']-1]['classname'],
                            'classurl'=>$this->common_classs[$tuinovel['novel_cid']-1]['clsurl'])
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
            $siteinfo=$s->find(2);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            if(isset($_GET['classname'])){
                $where['id']=$_GET['classname'];
                $C=M('Class');

                $classinfo=$C->where($where)->find();
                if(is_array($classinfo)){
                    $n=M('Novel');
                    $this->assign('classinfo',$classinfo);

                    $clsid=$classinfo['id'] - 1;
                    $this->common_classs[$clsid]=array_merge($this->common_classs[$clsid],array('clscls'=>'active'));
                    $this->assign('classes',$this->common_classs);
                    $novel['novel_cid']=$classinfo['id'];
                    $count=$n->where($novel)->count();

                    $p = $_GET['p'];
                    if($p==null)$p=1;
                    if($p==1)$preclass='disable';
                    $firstRow = ($p-1) *10;
                    $tempurl = $this->common_classs[$_GET['classname']-1]['clsurl'];
                    if ($p!=1){
                        $preurl = $tempurl.($p-1);
                    }
                    $nexturl=$tempurl.($p+1);
                    $tuinovels=$n->where($novel)->order('id desc')->limit($firstRow.',10')->select();

                    foreach($tuinovels as $tuinovel){
                        //book URL
                        $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                        $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                        $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
                    }
                    // $this->assign('pageshow',$pageshow);
                    $this->assign('pagecontrol',array('preurl'=>$preurl,'nexturl'=>$nexturl,'pageid'=>$p,'preclass'=>$preclass));
                    $this->assign('tuinovels',$tui);

                    $this->display('mobile:content/cls');
                }else{
                    $this->error('错误的访问！');
                }

            }else{
                $this->error('出现错误！');
            }

        }

        public function history(){
            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(2);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            $n=M('novel');
            $c=M('content');
            $hislist=cookie('histlist');
            $keys=array_keys($hislist);
            foreach ($keys as $key) {
                $novels =$n->field('id,novelname,novelauthor,novelstate,novelimg')->where('id='.$key)->select();
                $novel=$novels[0];
                $contents=$c->field('id,con_name')->where('id='.$hislist[$key])->select();
                $content=$contents[0];
                $bookurl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$novel);
                $contenturl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novel,$content);
                $con=array_merge($content,array('contenturl'=>$contenturl));
                $tui[]=array_merge($novel,array('bookurl' => $bookurl,'con'=>$con));
            }
            $this->assign('hislist',$tui);
            $this->display('mobile:content/his');
        }


        public function look(){

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(2);
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
                    $w='id='.$_GET['id'].' AND con_nid='.$novelInfo['id'];
                    $coninfo=$c->where($w)->find();
                    if(!is_array($coninfo)){
                        $this->error('错误的访问！');
                    }
                    $coninfo=$this->getContentByPath($coninfo);
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

                    $hislist=cookie('histlist');
                    if($hislist==null){
                        $hislist=array();
                    }
                    unset($hislist[$novelInfo['id']]);
                    $hislist=array($novelInfo['id']=>$coninfo['id'])+$hislist;
                    if(count($hislist)>10)
                        $hislist=array_slice($hislist,0,10);
                    cookie('histlist',$hislist,3600);
                    $this->display('mobile:content/chapter');
                }else{
                    //小说章节目录
                    //小说章节目录

                    //查询最新章节
                    $c=M('Content');
                    $newChapters=$c->field('id,con_name')->where('con_nid='.$novelInfo['id'])->order('id desc limit 1')->select();
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
                    $vol=null;
                    $vol['volname']='所有章节';
                    $where=null;
                    $where['con_nid']=$novelInfo['id'];
                    //循环章节列表
                    $chapter=$c->field('id,con_name')->where($where)->select();
                    $chapters_tmp=null;
                    $chp_num = 1;
                    foreach($chapter as $chp){
                        //con URl
                        $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novelInfo,$chp);
                        $chp_css = 'hidden';
                        if($chp_num < 51){
                            $chp_css = '';
                        }
                        $chapters_tmp[]=array_merge($chp,array('con_url'=>$conUrl,'chp_num'=>$chp_num,'chp_css'=>$chp_css));
                        $chp_num++;
                    }
                    $vol['chapter_num'] = $chp_num - 1;
                    $firstUrl = $chapters_tmp[0]['con_url'];
                    array_push($vol,$chapters_tmp );
                    $chapters[]=$vol;

                    $chapter_info = array('con_nid'=>$novelInfo['id'],'id'=>$newChapters[0]['id']);
                    $first_con = $this->getContentByPath($chapter_info);
                    $first_con['con_text']=mb_substr($first_con['con_text'],0,150,'utf-8').">";

                    //查询同一作家的作品
                    $where='novelauthor="'.$novelInfo['novelauthor'].'"'.' and id!='.$novelInfo['id'];
                    $author_novels=$n->where($where)->order('novelgrade desc limit 10')->select();
                    $is_first_novel = true;
                    $author_first_novels = array();
                    $author_second_novels = array();
                    foreach ($author_novels as $author_novel) {
                        if($is_first_novel){
                            array_push($author_first_novels,$author_novel);
                            $is_first_novel = false;
                        }else{
                            array_push($author_second_novels,$author_novel);
                        }
                    }

                    //查询同一类型评分相近的十本小说
                    $com_condition='novel_cid='.$novelInfo['novel_cid'].' and id!='.$novelInfo['id'];
                    $similar_novels=$n->where($com_condition.' and novelgrade>='.$novelInfo['novelgrade'])
                        ->order('novelgrade asc limit 5')->select();
                    $bottom_novel_array=$n->where($com_condition.' and novelgrade<'.$novelInfo['novelgrade'])
                        ->order('novelgrade desc limit 5')->select();
                    if(!$similar_novels){
                        $similar_novels = array();
                    }
                    foreach ($bottom_novel_array as $bottom_novel){
                        array_push($similar_novels,$bottom_novel);
                    }
                    $top_similars = array();
                    array_push($top_similars,array_shift($similar_novels));

                    $this->assign('author_first_novels',$author_first_novels);
                    $this->assign('author_second_novels',$author_second_novels);
                    $this->assign('top_similars',$top_similars);
                    $this->assign('similar_novels',$similar_novels);
                    $this->assign('novelInfo',$novelInfo);
                    $this->assign('first_con',$first_con);
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
            $siteinfo=$s->find(2);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            $n=M('novel');
            $count=$n->where('novelstate=1')->count();

            $p = $_GET['p'];
            if($p==null)$p=1;
            if($p==1)$preclass='disable';
            $firstRow = ($p-1) *10;
            $tempurl = $siteurl.'/d/';
            if ($p!=1){
                $preurl = $tempurl.($p-1);
            }
            $nexturl=$tempurl.($p+1);
            $donenovels=$n->where('novelstate=1')->order('novelwords desc')->limit($firstRow.',10')->select();
            foreach($donenovels as $donenovel){
                //book URL
                $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$donenovel);

                $des=mb_substr($donenovel['noveldes'],0,60,'utf-8')."...";
                $tui[]=array_merge($donenovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
            }
            $this->assign('pagecontrol',array('preurl'=>$preurl,'nexturl'=>$nexturl,'pageid'=>$p,'preclass'=>$preclass));
            $this->assign('tuinovels',$tui);
            $this->display('mobile:content/done');
        }


        //搜索功能
        public function search(){

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(2);
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

        private function novel_rank(){
            $novel_cid_dict =array(1=>100,2=>100,3=>100,4=>100,5=>100,6=>100,7=>100,8=>100,9=>50,10=>50);
            $novel_name_dict=array();
            $novel_author_dict=array();
            $novel_state_dict=array(1=>5000,0=>rand(1000,3000));
            $fields_arr =array_merge(array($novel_cid_dict),array($novel_name_dict));
            $fields_arr =  array_merge($fields_arr,array($novel_author_dict));
            $fields_arr = array_merge($fields_arr,array($novel_state_dict));
            $novel_fields = 'novel_cid,novelname,novelauthor,novelstate,clicktoday,clickmonth,clicksum,novelwords';
            $novel_field_arr = explode(',',$novel_fields);
            $n=M('Novel');
            $novels = $n->field('id,'.$novel_fields)->select();
            foreach($novels as $novel){
                $novel_grade = 0;
                $count = 0;
                foreach($novel_field_arr as $novel_field){
                    $novel_grade += $this->get_field_grade($fields_arr,$novel[$novel_field],$count);
                    $count++;
                }
                echo($novel_grade.'<br>');
                $data['novelgrade'] = $novel_grade;
                $n->where('id='.$novel['id'])->save($data);
            }
        }

        private function get_field_grade($field_arr,$f,$count){
            $filed_grade = null;
            if($count < 4){
                $filed_grade = $field_arr[$count][$f];
                if($filed_grade == null){
                    $filed_grade = rand(100,1000);
                }
            }else if($count < 7){
                $filed_grade = floor(((10/($count - 3))*$f)/20);
            }else{
                $filed_grade = floor($f/1000);
            }
            return $filed_grade;
        }
    }
?>