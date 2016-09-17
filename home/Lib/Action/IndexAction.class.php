<?php
    import("ORG.Util.Newpage");
    class indexAction extends CommonAction {
        public function index(){
            //网站信息
            $s=M('Site');//echo ROOT;
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $this->assign('currentindex','current');
            $siteurl=trim($siteinfo['site_url'],'/');

            $n=M('Novel');

            //查询高分小说
            $gradelist=$n->order('novelgrade desc limit 25')->select();
            foreach($gradelist as $grade){
                //book URL
                $gradeUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$grade);
                $gradecls = $this->common_classs[$grade['novel_cid']-1];
                $gradelist_ret[]=array_merge($grade , array('bookurl'=>$gradeUrl,'cls'=>$gradecls));
            }
            $this->assign('gradelist',$gradelist_ret);

            //查询一个月点击最高
            $clicklist=$n->order('clickmonth desc limit 25')->select();
            foreach($clicklist as $click){
                //book URL
                $clickurl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$click);
                $clickcls = $this->common_classs[$click['novel_cid']-1];
                $clicklist_ret[]=array_merge($click , array('bookurl'=>$clickurl,'cls'=>$clickcls));
            }
            $this->assign('clicklist',$clicklist_ret);

            //查询最新推荐
            $tuilist=$n->order('tuitime desc limit 11')->select();
            foreach ($tuilist as $tui) {
                $tuiurl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tui);
                $tuilist_ret[]=array_merge($tui,array('bookurl'=>$tuiurl));
            }
            $tuis=array_chunk($tuilist_ret, 9);
            $this->assign('toptuilist',$tuis[0]);
            $this->assign('bottomtuilist',$tuis[1]);
            
            foreach ($this->common_classs as $class) {
                $Sql=$n->field('id')->where('novel_cid='.$class['id'])->buildSql();
                $class_grade_list=$n->field('id,novelname,novelauthor,novelimg,novelgrade')->where('id in '.$Sql)->order('id desc limit 20')->select();
                for($orderid=0;$orderid<count($class_grade_list);$orderid++){
                    $class_grade_temp =$class_grade_list[$orderid];
                    $class_grade_list[$orderid] = array_merge($class_grade_temp,array('orderid'=>($orderid+1)));
                }
                $cls_first_grade= array_shift($class_grade_list);
                $class_grade['cls']=$class;
                $class_grade['first']=$cls_first_grade;
                $class_grade['after']=$class_grade_list;
                $classs_grade_ret[]=$class_grade;
            }
            $classs_grade_ret=array_chunk($classs_grade_ret, 5);
            $this->assign('classs_grade0',$classs_grade_ret[0]);
            $this->assign('classs_grade1',$classs_grade_ret[1]);
            $this->display('pc:index/index');
        }

        public function cls(){
            //页面显示最大书籍数
            $page_max_num = 24;

            //网站信息
            $s=M('Site');
            $siteinfo=$s->find(1);
            $this->assign('siteinfo',$siteinfo);
            $siteurl=trim($siteinfo['site_url'],'/');

            if(isset($_GET['classname'])){
                $class_name = $_GET['classname'];
                $where='`id`=\''.$class_name.'\'';
                $C=M('Class');

                $classinfo=$C->where($where)->find();
                if(is_array($classinfo)){
                    $n=M('Novel');
                    $query_page_num = $_GET['p'];
                    $query_novel_start_id = 1;
                    if($query_page_num){
                        $query_novel_start_id = (intval($_GET['p']) - 1) * $page_max_num + 1;
                    }else{
                        $query_page_num = 1;
                    }
                    $w = 'novel_cid='.$classinfo['id'];
                    $sub_query = $n->field('id')->where($w)->buildSql();
                    $tuinovels = $n->where('id in '.$sub_query)->order('novelgrade desc')->limit($query_novel_start_id,$page_max_num)->select();
                    if(!isset($tuinovels)){
                        $this->error('人家还满足不了你吗！');
                    }
                    foreach($tuinovels as $tuinovel){
                        //book URL
                        $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                        $words = round(floatval($tuinovel['novelwords']) / 10000,2);
                        $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'words'=>$words));
                    }

                    //查询同一类型月点击率前50本小说
                    $where = 'novel_cid ='.$class_name;
                    $novel_count = $n->where($where)->count();
                    $sub_query = $n->field('id')->where($where)->buildSql();
                    $click_month_novels = $n->where('id in '.$sub_query)->order('clickmonth desc limit 50')->select();

                    $this->page($novel_count,$query_page_num,$page_max_num,$siteinfo,$classinfo);

                    $this->assign('novel_count',$novel_count);
                    $this->assign('click_month_novels',$click_month_novels);
                    $this->assign('query_page_num',$query_page_num);
                    $this->assign('tuinovels',$tui);

                    $this->display('pc:content/cls');
                }else{
                    $this->error('错误的访问！');
                }

            }else{
                $this->error('出现错误！');
            }

        }

        private function page($novel_count,$query_page_num,$page_max_num,$siteinfo,$classinfo){
            $max_page = intval(ceil($novel_count / $page_max_num));
            $first_ellipsis = 'hidden';
            $last_ellipsis = 'hidden';
            $page_set = array();
            if ($max_page < 7) {
                for ($i = 1; $i <= $max_page; $i++) {
                    array_push($page_set, $i);
                }
            } else {
                if ($query_page_num >= 5) {
                    $first_ellipsis = '';
                }
                if ($query_page_num < $max_page - 3) {
                    $last_ellipsis = '';
                }
                if ($query_page_num > 3 && $query_page_num < $max_page - 2) {
                    $tmp = $query_page_num + 2;
                    for ($i = $query_page_num - 2; $i <= $tmp; $i++) {
                        array_push($page_set, $i);
                    }
                }

                if ($query_page_num < 4) {
                    $page_set = array(2, 3, 4, 5);
                }

                if ($query_page_num > $max_page - 3) {
                    $page_set = array($max_page - 4, $max_page - 3, $max_page - 2, $max_page - 1);
                }
            }
            if($classinfo) {
                $c_url_prefix = $siteinfo['site_url'] . '/c/' . $classinfo['id'];
            }else {
                $c_url_prefix = $siteinfo['site_url'] .'/d/';
            }


            $this->assign('c_url_prefix', $c_url_prefix);
            $this->assign('max_page', $max_page);
            $this->assign('page_set', $page_set);
            $this->assign('first_ellipsis', $first_ellipsis);
            $this->assign('last_ellipsis', $last_ellipsis);
        }

        //全本
        public function done()
        {
            //页面显示最大书籍数
            $page_max_num = 24;

            //网站信息
            $s = M('Site');
            $siteinfo = $s->find(1);
            $this->assign('siteinfo', $siteinfo);
            $siteurl = trim($siteinfo['site_url'], '/');

            $n = M('Novel');
            $query_page_num = $_GET['p'];
            $query_novel_start_id = 1;
            if ($query_page_num) {
                $query_novel_start_id = (intval($_GET['p']) - 1) * $page_max_num + 1;
            } else {
                $query_page_num = 1;
            }
            $w = 'novelstate=1';
            $sub_query = $n->field('id')->where($w)->buildSql();
            $tuinovels = $n->where('id in ' . $sub_query)->order('novelgrade desc')->limit($query_novel_start_id, $page_max_num)->select();
            if (!isset($tuinovels)) {
                $this->error('人家还满足不了你吗！');
            }
            foreach ($tuinovels as $tuinovel) {
                //book URL
                $tuiUrl = $this->bookToUrl($siteinfo['urlrewrite_book'], $siteurl, $tuinovel);

                $words = round(floatval($tuinovel['novelwords']) / 10000, 2);
                $tui[] = array_merge($tuinovel, array('tuiUrl' => $tuiUrl, 'words' => $words));
            }

            //查询月点击率前50本小说
            $novel_count = $n->where($w)->count();
            $sub_query = $n->field('id')->where($w)->buildSql();
            $click_month_novels = $n->where('id in ' . $sub_query)->order('clickmonth desc limit 50')->select();

            $this->page($novel_count,$query_page_num,$page_max_num,$siteinfo,null);

            $this->assign('novel_count', $novel_count);
            $this->assign('click_month_novels', $click_month_novels);
            $this->assign('query_page_num', $query_page_num);
            $this->assign('tuinovels', $tui);

            $this->display('pc:content/cls');

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
                $NovelUrl=$tuiUrl;  //当前URL，保存一份，用于上下翻页时没有页面
                $novelInfo=array_merge($novelInfo,array('classname'=>$clssss['classname'] , 'classurl'=>$clsUrl,'bookurl'=>$tuiUrl) );

                $this->assign('novelinfo',$novelInfo);



                if(isset($_GET['id']) ){  //内容
                    //内容
                    $c=M('Content');
                    $data['con_namepy']=$_GET['id'];
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

                    $coninfo=array_merge($coninfo,array('prePage'=>$prePage , 'nextPage'=>$nextPage ) );

                    $this->assign('coninfo',$coninfo);

                    $addMath=rand(20,50); //每访问一次页面随机加几

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


                    if ($_GET['buildHtml'])
                          $this->buildHtml($coninfo['id'], HTML_PATH . 'pc/look/' . $novelInfo['id'] . '/','pc:content/chapter');
                      else
                          $this->display('pc:content/chapter');
                }else{
                    //小说章节目录

                    //查询最新章节
                    $c=M('Content');
                    $newChapters=$c->query('select id,con_name from ck_content where id in(select id from ck_content where con_nid='.$novelInfo['id'].') order by id desc limit 1');
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
                    $first_con['con_text']=mb_substr($first_con['con_text'],0,350,'utf-8').">";

                    //查询同一作家的作品
                    $where='novelauthor="'.$novelInfo['novelauthor'].'"'.' and id!='.$novelInfo['id'];
                    $sub_query=$n->field('id')->where($where)->buildSql();
                    $author_novels=$n->where('id in '.$sub_query)->order('novelgrade desc limit 10')->select();
                    $is_first_novel = true;
                    $author_first_novels = array();
                    $author_second_novels = array();
                    foreach ($author_novels as $author_novel) {
                        $author_novel = array_merge($author_novel,array('cls_name'=>$this->common_classs[$author_novel['novel_cid']-1]['classname']));
                        if($is_first_novel){
                            array_push($author_first_novels,$author_novel);
                            $is_first_novel = false;
                        }else{
                            array_push($author_second_novels,$author_novel);
                        }
                    }

                    //查询同一类型评分相近的十本小说
                    $com_condition='novel_cid='.$novelInfo['novel_cid'].' and id!='.$novelInfo['id'];
                    $sub_query=$n->field('id')->where($com_condition.' and novelgrade>='.$novelInfo['novelgrade'])->buildSql();
                    $similar_novels=$n->where('id in '.$sub_query)->order('novelgrade asc limit 5')->select();
                    $sub_query=$n->field('id')->where($com_condition.' and novelgrade<'.$novelInfo['novelgrade'])->buildSql();
                    $bottom_novel_array=$n->where('id in '.$sub_query)->order('novelgrade desc limit 5')->select();
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
                    if ($_GET['buildHtml'])
                          $this->buildHtml('', HTML_PATH . 'pc/look/' . $novelInfo['id'] . '/','pc:content/vol');
                      else
                          $this->display('pc:content/vol');
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

            $this->display('pc:content/search');
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
            $this->buildHtml($coninfo['id'], HTML_PATH . 'pc/' . $novelInfo['id'] . '/', 'pc:content/chapter');
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
            $this->buildHtml('index', HTML_PATH . 'pc/' . $novelInfo['id'] . '/', 'pc:content/vol');
            // 所有章节静态化
            foreach ($conents as $content) {
                if (!$this->getState()) return;
                $this->chapter_build($n, $c, $content, $novelInfo, $siteurl, $siteinfo, $NovelUrl);
            }
        }
        public function novels_build() {
              file_put_contents('/home/htmltime', 'start-time:'.date('Y-m-d H:i:s')."\r\n" ,FILE_APPEND);
              ignore_user_abort(true);
              ini_set('max_execution_time', '0');
              ini_set('memory_limit', '500M');
              $id = $_GET['Id'];
              $chapter_num =$_GET['chpnum'];
              if ($id == null) return;
              if ($id == 0) return;
              if ($chapter_num == 0)return;
              $_GET['buildHtml'] = true;
              $n=M('novel');
              $c=M('content');
              $novelInfo=$n->field('id')->where('id<='.$id)->select();

              foreach ($novelInfo as $novel) {
                    $_GET['name'] = $novel['id'];
                    $_GET['id'] =null;
                  $this->look();
                  $subsql=$c->field('id')->where('con_nid='.$novel['id'])->buildSql();
                  $max=$c->where('id in '.$subsql)->order('id desc limit 1')->select();
                  if(is_array($max)){
                    $max=$max[0];
                    }
                    else continue;
                  $maxid=$max['id'] -$novel['id'] * 10000;
                  $manum=min($maxid,$chapter_num);
                  for ($i=1;$i<=$manum;$i++){
                      $chid=$novel['id'] * 10000 + $i;
                      // $conents=$c->where('id='.$chid)->find();
                      // if(is_array($conents)){
                          $_GET['id'] = $chid;
                          $this->look();
                      // }
                  }
              }
              file_put_contents('/home/htmltime', 'end-time:'.date('Y-m-d H:i:s')."\r\n",FILE_APPEND);
        }



    }
?>