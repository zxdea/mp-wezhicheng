<?php
	//设置数据库连接参数
	$config = array();
	$config['mysql_host'] = 'qdm220791467.my3w.com';
	$config['mysql_port'] = '3306';
	$config['mysql_username'] = 'qdm220791467';
	$config['mysql_password'] = 'Zzx19940728';
	$config['mysql_database'] = 'qdm220791467_db';


	//引入数据库类
	include_once('../db1_2.php');
	$db = new DB2();

	$db->qqq();
	//基本查询
	$res = $db->query('select * from user');
	

	//遍历输出用户表数组
	$db->get('user');
	$user_list = $db->arr();
	foreach($user_list as $user){
		echo $user['user_id'];
		echo $user['user_name'];
	}


	//获取订单表id为1的数据以json输出
	$db->get('order','id=1');
	var_dump($db->json());


	//插入新的订单
	$new_order = array(
		"id"=>1,
		"price"=>2,
		"content"=>"xxxxx"
		);
	$db->insert('order',$new_order);


	//删除id为2的订单
	$db->del('order',"id='2'");


	//查询订单id为1的订单日期
	$date = $db->val('order','order_date',"id='1'");


	//修改id为1的订单价格为2,状态为0
	$order_data = array(
		"price"=>2,
		"state"=>"0"
		);
	$db->set('order',$order_data,"id='1'");


	//修改订单id为1的价格为3
	$db->val('order','price',"id='1'",'3');


	//查询用户是否存在
	$flag = $db->exist('user',"username='admin'");
	var_dump($flag);


	//过滤字符串防止注入
	$str = $db->escape($str);