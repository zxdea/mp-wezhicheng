<?php

include_once('config.inc.php');
include_once('config_fix.php');
include_once('db1_2.php');
include_once('mis/mis.class.php');
include_once('userManager.php');

$db = new DB2();

$db->query('select stu_id,stu_pw,wx_id from user');
$users = $db->arr();
$num = 0;
foreach ($users as $user) {
	$mis = new Mis();
	$login = $mis->login($user['stu_id'],$user['stu_id']);
	if(!$login)
		$login = $mis->login($user['stu_id'],$user['stu_pw']);
	if($login){
		$num++;
		$info = $mis->getInfo();
		$user = new userManager(NULL,NULL,$user['wx_id']);
		$user->updateUserInfo('mis_id',$info['mis_id']);
		$user->updateUserInfo('qq',$info['qq']);
		$user->updateUserInfo('email',$info['email']);
		$phone = $user->getUserInfo('phone');
		if(empty($phone)){
			$user->updateUserInfo('phone',$info['phone']);
		}

	}
}

echo $num;




// include_once('db.php');
// include_once('userManager.php');
// include_once('eas.php');

//  $db = new DB();
//  $sql = "SELECT * FROM user";
//  $res = $db->SQL($sql);
//  while($row = $res->fetch_assoc()){
//    $wx_id=$row['wx_id'];
//    $user = new userManager(NULL,NULL,$wx_id);
//    $eas = new EAS($user->stu_id,$user->stu_pw);
//    $login = $eas->login();
//    if($login==1){
//    		$eas ->getUserInfo();
//     	$view = $eas->getTimeTable(0);
//     	echo $user->stu_id .'<br>';
//    }

//  }
?>
