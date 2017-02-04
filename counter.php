<?php
	include_once('config.php');
	//兼容新版配置文件
	$config = array();
	$config['mysql_host'] = $mysql_host;
	$config['mysql_port'] = $mysql_port;
	$config['mysql_username'] = $mysql_username;
	$config['mysql_password'] = $mysql_password;
	$config['mysql_database'] = 'zctool';

	//新版数据库操作类
	include_once('dbx.php');
	$db = new DB();

	class Counter{

		public function __construct(){
			
		}

		public function getCount($object){
			$db->get('count',"name='$object'");
			$res = $db->array_result();
			$count = $res[0]['count'];
			return $count;
		}

		public function addCount($object){

		}

	}
?>
