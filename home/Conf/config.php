<?php
	$arr1=require './config.php';

	$arr2=array(
		'DEFAULT_THEME'=>'',//����Ĭ��ģ������
		'HTML_CACHE_ON'=>true,
		'HTML_FILE_SUFFIX'  =>  '.html', // ���þ�̬�����׺
		'HTML_CACHE_RULES'=>array(
			'index:look'=>array('pc/{:action}/{name}/{id}',0),	//Ŀ¼������ҳ����10����
			'index:index'=>array('pc/{:action}',86400),	//��ҳ����10����{:module}/
			'index:cls'=>array('pc/{:action}/{classname}/{p}',86400),	//��Ŀҳ����20����
			'index:done'=>array('index/{:action}/{p}',86400),
			'index:map'=>array('index/{:action}/{name}',0),
			'mobile:look'=>array('mobile/{:action}/{name}/{id}',0),	//Ŀ¼������ҳ����10����
			'mobile:index'=>array('mobile/{:action}',86400),	//��ҳ����10����{:module}/
			'mobile:cls'=>array('mobile/{:action}/{classname}/{p}',86400),	//��Ŀҳ����20����
			'mobile:done'=>array('mobile/{:action}/{p}',86400),	//��Ŀҳ����20����
			'mobile:map'=>array('mobile/{:action}/{name}',0),
		),
		'APP_SUB_DOMAIN_DEPLOY'=>1,
		'APP_SUB_DOMAIN_RULES'=>array('m'=>array('home/Mobile'))
	);
	
	return array_merge($arr1,$arr2);
?>