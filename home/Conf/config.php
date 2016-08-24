<?php
	$arr1=require './config.php';

	$arr2=array(
		'DEFAULT_THEME'=>'',//����Ĭ��ģ������
		'HTML_CACHE_ON'=>false,
		'HTML_FILE_SUFFIX'  =>  '.html', // ���þ�̬�����׺
		'HTML_CACHE_RULES'=>array(
			'index:look'=>array('{:action}/{name}/{$_SERVER.REQUEST_URI|md5}',600),	//Ŀ¼������ҳ����10����
			'index:index'=>array('{:action}/{$_SERVER.REQUEST_URI|md5}',600),	//��ҳ����10����{:module}/
			'index:cls'=>array('{:action}/{$_SERVER.REQUEST_URI|md5}',1200),	//��Ŀҳ����20����
			//'index:search'=>array('',false),	//����ҳ�����л���
			//'*'=>array('{:action}/{$_SERVER.REQUEST_URI|md5}',600),	//����10����{:module}/
		)
	);
	
	return array_merge($arr1,$arr2);
?>