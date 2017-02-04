<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
ini_set("max_execution_time", "1800"); 

include_once('config.inc.php');
include_once('config_fix.php');
include_once('db1_2.php'); 
include_once('wx_util.php');

$db = new DB2();
$wx = new wx_util($wx_appid,$wx_secret);
$wx->get_access_token();

//var_dump($wx->send_message('oD46HwwKF0bA2_DJ0c3PHaWEg5-s','友情提醒：今晚还没有签到哦'));


$action = $_GET['action'];

switch($action){
	case 'fs_check':
		include_once('mis/mis.class.php');
		$users = $db->query('select stu_id,stu_pw,wx_id,grade from `user`');
		$send_num = 0;
		foreach ($users as $user) {
			//if($user['grade']=='2016')continue;
			
			$mis = new Mis();
			$login = $mis->login($user['stu_id'],$user['stu_id']);

			if(!$login)
				$login = $mis->login($user['stu_id'],$user['stu_pw']);

			if($login){
				$check = $mis->isChecked();

				if(!$check){
					$state = $wx->send_message($user['wx_id'],'[友情提醒]今晚还没有指纹签到');
					if($state)$send_num++;
				}
				
			}
		}
		echo $send_num;
		//var_dump($db->arr());
		// $mis = new Mis();
		// $mis->login('211306416','2015090');
		// var_dump($mis->isChecked());
		break;

	case 'birthday':
		$words = [
			"有树的地方,就有我的思念;有你的地方,就有我深深的祝福,祝你生日快乐!",
			"盈盈今日天如水，恋恋当年水似天。情缘驻我心，相思比梦长。祝福你生日快乐！",
			"今天是你的生日，愿所有的快乐、所有的幸福、所有的温馨、所有的好运围绕在你身边。生日快乐！",
			"愿你的生日充满无穷的快乐，愿你今天的回忆温馨，愿你所有的梦想甜蜜，愿你这一年称心如意!",
			"心到，想到，看到，闻到，听到，人到，手到，脚到，说到，做到，得到，时间到，你的礼物没到，先把我的祝福传到。",
		];

		
		$today = date('m-d',time());
		$db->query("select wx_id,stu_id,full_name from user where birthday like '%$today%'");
		$list = $db->arr();

		foreach ($list as $user) {
			$word_idx = array_rand($words, 1);
			$word = $words[$word_idx];
			$wx->send_message($user['wx_id'],$word);
		}
		break;
	case 'all':
		include_once('mis/mis.class.php');
		$users = $db->query('select wx_id from `user`');
		$send_num = 0;
		foreach ($users as $user) {
			$state = $wx->send_message($user['wx_id'],$_GET['msg']);
			if($state)$send_num++;
		}
		echo $send_num;
		break;
}


