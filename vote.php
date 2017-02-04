<?php
header("Content-type:text/html;charset=utf-8");  
include_once('safe.inc.php');
include_once('config_fix.php');
include_once('db1_2.php'); //新版db
include_once('utils.php');
$db = new DB2();

$action = $_GET['action'];
$openid = $_GET['openid'];
$id = $_GET['id'];
$callback = $_GET['callback'];

switch($action){
	case 'get_list':
		$db->get('vote','1 order by `like` desc');
		$json = $db->json();
		echo empty($callback)?$json:$callback.'('.$json.')';
		break;
	case 'get_person':
		if(empty($id)){
			$db->get('vote','1 order by rand() limit 1');
		}else{
			$db->get('vote',"id='$id'");
		}
		
		$json = json_encode($db->arr()[0]);
		echo empty($callback)?$json:$callback.'('.$json.')';
		break;
	case 'like':
		$bind = isBind($openid);
		if($bind){
			session_start();
			if($_SESSION['vote_count']<3){
				$db->query("update vote set `like`=`like`+1 where id='$id'");
				$_SESSION['vote_count']+=1;
			}
		}
		$res = array("success"=>$bind);
		$json = json_encode($res);
		echo empty($callback)?$json:$callback.'('.$json.')';
		break;
}