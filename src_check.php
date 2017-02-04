<?php
	include_once('wxHelper.php');
	$wx= new wxHelper();
	if(!$wx->isWeChat()&&$_SERVER['HTTP_HOST']!='localhost')header("location: " . "http://jwb.fdzcxy.com/");
?>
