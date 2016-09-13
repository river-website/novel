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

            //友情链接
            $L=M('Links');
            $links=$L->order('linkweight desc')->select();
            $this->assign('links',$links);

            $n=M('Novel');
            //查询推荐小说
            $tuinovels=$n->order('tuitime desc limit 6')->select();
            foreach($tuinovels as $tuinovel){
                //book URL
                $tuiUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$tuinovel);

                $des=mb_substr($tuinovel['noveldes'],0,60,'utf-8')."...";
                $tui[]=array_merge($tuinovel , array('tuiUrl'=>$tuiUrl,'des'=>$des) );
            }

            $this->assign('tuinovels',$tui);
            //查询最新更新的小说
            $novels=$n->field('id,novel_cid,novelname,novelauthor,insert_time')->order('insert_time desc limit 15')->select();

            $C=M('Content');
            $novelinfos=array();
            foreach($novels as $novel){
                $where['con_nid']=$novel['id'];
                $novelname=$novel['novelname'];
                $novelpy=$novel['id'];

                $novelauthor=$novel['novelauthor'];
                $insert_time=$novel['insert_time'];

                //book URL
                //$bookUrl=str_ireplace('%book_name%',urlencode($novelname),$bookUrl);绝世武神
                $bookUrl=$this->bookToUrl($siteinfo['urlrewrite_book'],$siteurl,$novel);
                $c=M('Class');
                $classes=$c->select();
                //分类
                $cid=$novel['novel_cid']-1;
                $class=$classes[$cid];

                $coninfo=$C->query('select * from ck_content where id in(select id from ck_content where con_nid='.$novel['id'].') order by id desc limit 1');

                //con URl
                $conUrl=$this->chapterToUrl($siteinfo['urlrewrite_con'],$siteurl,$novel,$coninfo[0]);

                if($coninfo != null )
                    $novelinfos[]=array_merge( array('novelname' => $novelname,'novelauthor'=>$novelauthor,'update_time'=>$insert_time,'class'=>$class,'bookurl'=>$bookUrl,'conurl'=>$conUrl) , $coninfo[0]);
            }
            $this->assign('novels',$novelinfos);

            $this->display('pc:index/index');
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
                    $page=new NewPage($count,9);
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


                    $this->display('pc:content/cls');
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
                    $first_con['con_text']=mb_substr($first_con['con_text'],0,700,'utf-8').">";

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