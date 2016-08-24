<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo ($novelinfo["novelname"]); ?>最新章节_<?php echo ($novelinfo["novelname"]); ?>无弹窗_<?php echo ($novelinfo["novelauthor"]); ?>小说作品 - <?php echo ($siteinfo["site_name"]); ?></title>
        <meta name="keywords" content="<?php echo ($novelinfo["novelname"]); ?>">
        <meta name="description" content="<?php echo ($siteinfo["site_name"]); ?>提供的小说<?php echo ($novelinfo["novelname"]); ?>其作者是<?php echo ($novelinfo["novelauthor"]); ?>，本站仅提供<?php echo ($novelinfo["novelname"]); ?>最新章节和<?php echo ($novelinfo["novelname"]); ?>无弹窗供各位读者在线阅读小说<?php echo ($novelinfo["novelname"]); ?>！如果各位朋友喜欢<?php echo ($novelinfo["novelname"]); ?>，请支持正版！">
        <meta name="MobileOptimized" content="240">
        <meta name="applicable-device" content="mobile">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <link rel="shortcut icon" href="">
        <meta http-equiv="Content-Type" content="application/vnd.wap.xhtml+xml;charset=UTF-8">
        <meta http-equiv="Cache-Control" content="max-age=0">
        <meta http-equiv="Cache-Control" content="no-transform ">
        <link rel="stylesheet" type="text/css" href="__ROOT__/Public/css/mobile/vol.css">
        <script src="__ROOT__/Public/js/jquery-1.7.1.min.js" type="text/javascript"></script>
    </head>
    <body>
        <div class="header">

        </div>
        <div class="nav">

        </div>
        <div class="search">

        </div>

        <div class="description">
            <div class="">
                <div class="float-left">
                    <img alt="<?php echo ($novelinfo["novelname"]); ?>" src="<?php echo ($novelinfo["novelimg"]); ?>" border="0" width="85" height="100">
                </div>
                <div class="float-left margin-left-5-percent">
                    <div style="font-size: 18px;"><a href="">史上最强师兄</a></div>
                    <div>作者：<a href=""><?php echo ($novelinfo["novelauthor"]); ?></a></div>
                    <div>分类：<a href=""><?php echo ($novelinfo["classname"]); ?></a></div>
                    <div>状态：<?php if($novelinfo["novelstate"] == 0): ?>连载中<?php else: ?>已完本<?php endif; ?></div>
                    <div>更新：<?php echo (date('Y-m-d H:i:s',$novelinfo["update_time"])); ?></div>
                    <div>最新：<a href="" class="red"></a></div>
                </div>
            </div>
            <div class="clear"></div>

            <div class="margin-top-2-percent">
                <div class="vol_button_function_block float-left"><a href="">开始阅读</a></div>
                <div class="vol_button_function_block float-left margin-left-2-percent"><a href="">TXT下载</a></div>
                <div class="clear"></div>
            </div>
            <div class="margin-top-2-percent">
                <div class="vol_button_function_block float-left"><a href="">加入书架</a></div>
                <div class="vol_button_function_block float-left margin-left-2-percent"><a href="">投推荐票</a></div>
                <div class="clear"></div>
            </div>

            <div class="">小说简介</div>
            <div id="infodes">简介：<?php echo ($novelinfo["noveldes"]); ?></div>

            <div class="box_con">
                <div id="list">
                    <?php if(is_array($chapters)): foreach($chapters as $key=>$chapter): ?><h4><?php echo ($chapter["volname"]); ?></h4>
                        <dl>
                            <?php if(is_array($chapter[0])): foreach($chapter[0] as $key=>$con): ?><div><a href="<?php echo ($con["con_url"]); ?>"  title="<?php echo ($con["con_name"]); ?>"><?php echo ($con["con_name"]); ?></a></div><?php endforeach; endif; ?>
                        </dl><?php endforeach; endif; ?>
                </div>
            </div>

        <div class="footer margin-top-5-percent">
            <div class="float-left vol_button-bottom margin-left-12-percent"><a href="">首页</a></div>
            <div class="float-left vol_button-bottom margin-left-12-percent"><a href="">搜索</a></div>
            <div class="float-left vol_button-bottom margin-left-12-percent"><a href="">书架</a></div>
            <div class="float-left vol_button-bottom margin-left-12-percent"><a href="">报错</a></div>
            <div class="clear"></div>
        </div>
    </body>
</html>