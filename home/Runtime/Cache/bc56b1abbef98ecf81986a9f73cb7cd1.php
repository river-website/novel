<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo ($siteinfo["site_name"]); ?> - <?php echo ($siteinfo["site_title"]); ?></title>
<meta name="keywords" content="<?php echo ($siteinfo["site_keywords"]); ?>">
<meta name="description" content="<?php echo ($siteinfo["site_des"]); ?>">
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/css/base.css" />
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/css/style.css" media="all" />

<script src="__ROOT__/Public/js/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="__ROOT__/Public/js/playclass.js" type="text/javascript"></script>
<script src="__ROOT__/Public/js/gg.js" type="text/javascript"></script>


</head>
<body>
<script type="text/javascript" language="javascript">
	$(function(){
        //当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失
        $(function () {
            $(window).scroll(function(){
                if ($(window).scrollTop()>300){
                    $("#back-to-top").fadeIn(1500);
                }
                else
                {
                    $("#back-to-top").fadeOut(1500);
                }
            });

            //当点击跳转链接后，回到页面顶部位置

            $("#back-to-top").click(function(){
                $('body,html').animate({scrollTop:0},1000);
                return false;
            });
        });
    });
	function postkey(){
		document.getElementById('searform').submit();
		var cb=document.getElementById('key').value;
		
		window.location="__ROOT__/search/keyword/"+cb; 
	}
	window.load=postkey();
	

	function addfavorite() { 
		if (document.all){ 
			window.external.addFavorite('',''); 
		} 
		else if (window.sidebar){ 
			window.sidebar.addPanel('', '', ""); 
		} 
	} 

</script>


<div id="header">
<div id="navbar">
<div class="layout fn-clear">
<ul id="nav" class="ui-nav">
<li class="nav-item  <?php echo ($currentindex); ?>" id="nav-home">
<a class="nav-link" target="_self" href="<?php echo ($siteinfo["site_url"]); ?>" title="<?php echo ($siteinfo["site_name"]); ?>"><i class="ui-icon home-nav"></i></a>
</li>

<?php if(is_array($classes)): foreach($classes as $key=>$class): ?><li class="nav-item " id="nav-cartoon"><a class="nav-link" target="_self" href="<?php echo ($class["clsurl"]); ?>" title="<?php echo ($class["classname"]); ?>"><i class="ui-icon cartoon-nav"></i><?php echo ($class["classname"]); ?></a></li><?php endforeach; endif; ?>
</ul>

<ul id="sign" class="ui-nav"><li class="nav-item drop-down" id="nav-looked"><a class="nav-link drop-title" target="_self"><i class="ui-icon looked-nav"></i>阅读记录</a><div class="drop-box" style="display: none;"><div class="looked-list"><p><a class="close-his" target="_self" href="javascript:;">关闭</a><a href="javascript:;" id="emptybt" data="1" target="_self">你读的最近十个页面会显示在这里</a></p><ul class="highlight" id="playhistory"><li class="no-his"><p>暂无阅读历史列表...</p></li></ul><div class="his-todo" id="morelog" style="display: none;"></div><div class="his-todo" id="his-todo"><A href="<?php echo ($siteinfo["site_url"]); ?>" target="_blank"><?php echo ($siteinfo["site_name"]); ?></A>是你理想的阅读家园</div></div><script type="text/javascript">PlayHistoryObj.viewPlayHistory('playhistory');</script></div></li></ul>
</div></div>
<DIV class="clear"></DIV>
</div>


<DIV class="warpper div_part_index">
<DIV class="div_breadcrumbs">
<H2 class="breadcrumbs">
<SMALL>
如果觉得<A href="<?php echo ($siteinfo["site_url"]); ?>" target="_blank"><?php echo ($siteinfo["site_name"]); ?></A>不错，请加入收藏，或者分享给你的朋友！你的支持是我们最大的动力！ 
</SMALL>
</H2>

<SMALL class="right">
<A href="#" rel="nofollow" onclick="window.external.AddFavorite(location.href, document.title)">加入收藏</A> 
</SMALL>
</DIV>

<DIV class="div_breadcrumbs" style="height:55px;">
<form action="" method="get" id='searform' style="width:100%;padding-top:10px;padding-bottom:5px;">
	<font color="#c00" size="2">小说搜索：</font><input type="text" placeholder="请输入小说名或者作者" id="key"/>
	<input type="submit" value="提交" onclick="postkey();"/> 
	<SMALL style="font-size:15px;font-weight :bold;"><font style="font-size:14px;padding-left:10px;"> 热词：</font>
		<?php if(is_array($hotnovels)): foreach($hotnovels as $key=>$hotnovel): ?><A title="<?php echo ($hotnovel["keyword"]); ?>" href="<?php echo ($hotnovel["url"]); ?>"><font style="padding-left:12px;color:black;"><?php echo ($hotnovel["keyword"]); ?></font></A><?php endforeach; endif; ?>
	</SMALL>
</form>


</DIV>
<DIV class="clear"></DIV>
</DIV>

<p id="back-to-top"><a href="#top" title="返回顶部"><span></span>返回顶部</a></p>


<!--推荐小说-->
<DIV class="warpper">

<DIV class="div_breadcrumbs">
<H2 class="breadcrumbs"><DIV class="breadcrumbs">推荐小说</DIV></H2>
</DIV>
</DIV>


<DIV class="warpper">
<DIV class="div_img_with_title">
<?php if(is_array($tuinovels)): foreach($tuinovels as $key=>$tuinovel): ?><DIV class="div_post"><A title="<?php echo ($tuinovel["novelname"]); ?>" class="img_preview" href="<?php echo ($tuinovel["tuiUrl"]); ?>">
<DIV class="thumbnail"><IMG class="captify" alt="<?php echo ($tuinovel["novelname"]); ?>" src="<?php echo ($tuinovel["novelimg"]); ?>" rel="caption1"/></DIV></A>

</DIV>
<DIV class="div_showinfo">
<p class="div_novelname"><A title="<?php echo ($tuinovel["novelname"]); ?>"  href="<?php echo ($tuinovel["tuiUrl"]); ?>"><?php echo ($tuinovel["novelname"]); ?></a></p>
<p class="author">作者:<?php echo ($tuinovel["novelauthor"]); ?></p>
<p class="div_des">简介：<?php echo ($tuinovel["des"]); ?></p>
</DIV><?php endforeach; endif; ?>
</DIV>
<DIV class=clear></DIV>
</DIV>


<!--最新更新-->
<DIV class="warpper">
<DIV class="div_breadcrumbs">
<H2 class="breadcrumbs"><DIV class="breadcrumbs">最近更新</DIV></H2>
</DIV>
</DIV>

<DIV class="warpper">
<DIV class="div_img_with_title">
<table class="update_table">
	<thead>
		<tr style="border-bottom: 1px solid #dfdfdf;font-size:22px;font-weight:bold;">
			<th style="width:100px;padding-left:15px;">分类</th>
			<th  style="width:240px;">小说名称</th>
			<th  style="width:480px;">最新章节</th>
			<th  style="width:120px;">小说作者</th>
			<th style="font-weight:bold;">更新时间</th>
		</tr>
	</thead>
	<tbody style="padding: 2px 4px 4px;overflow: hidden;">
	<?php if(is_array($novels)): foreach($novels as $key=>$n): ?><tr style="border-bottom: dashed 1px #DDDDDD;height: 50px;line-height: 28px;overflow: hidden;">
		<td  style="font-size:15px;font-weight:bold;">[<?php echo ($n["class"]); ?>]</td>
		<td  style="font-size:22px;font-weight:bold;"><a href="<?php echo ($n["bookurl"]); ?>"><?php echo ($n["novelname"]); ?></a></td>
		<td style="font-size:16px;font-weight:bold;"><a href="<?php echo ($n["conurl"]); ?>"><?php echo ($n["con_name"]); ?></a></td>
		<td style="font-size:18px;font-weight:bold;"><?php echo ($n["novelauthor"]); ?></td>
		<td style="font-size:16px;"><?php echo (date('m-d H:i:s',$n["update_time"])); ?></td>
		</tr><?php endforeach; endif; ?>	
	</tbody>
</table>
</DIV>
<DIV class="clear"></DIV>
</DIV>

<DIV class="warpper div_index_ad"><DIV class="warpper"><DIV class="ad_biger"><DIV class="ad_biger">
<script>ad_bottom();</script>
</DIV></DIV></DIV>
<DIV class=clear></DIV>
</DIV>


<!--友情链接-->
<DIV class="warpper div_links">
<DIV class="div_title">
<H2>友情链接<SMALL>接受PR&gt;=1,BR&gt;=1,流量相当，内容相关类链接！联系QQ：343377708</SMALL></H2></DIV>
<UL class="div_link">
	<?php if(is_array($links)): foreach($links as $key=>$link): ?><LI><A href="<?php echo ($link["linkurl"]); ?>" target="_blank"><?php echo ($link["linkname"]); ?></A> </LI><?php endforeach; endif; ?>
</UL>
<DIV class="clear"></DIV></DIV>