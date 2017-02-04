<?php
	include_once('wxHelper.php');
	include_once('db.php');
	include_once('userManager.php');
	include_once('eas.php');

	$stu_id=$_REQUEST['stu_id'];

	if(!empty($stu_id)){
		$user = new userManager($stu_id,NULL,NULL);
		$eas = new EAS($user->stu_id,$user->stu_pw);
		$login = $eas->login();
		if($login==1)$view = $eas->getTimeTable();

		if($login==-2)$view="<p align='center'>无法获取课表</p>";

		if(empty($view))$view = "<p align='center'>暂时无法获取课程表:(</p>";

	}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $user->getUserInfo("full_name");?>的课程表 - 至诚小助手</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<div class="container">
<?php
echo "<h3>" . $user->getUserInfo('major'). $user->getUserInfo('class'). "班 " . $stu_id ." " .$user->getUserInfo('full_name') .'<img align="right" src="img/wx_zs_logo.png" height="25">'. "</h3>";
echo $view;
?>
<div class="text-center" style="margin-top:40px"><img src="img/qrcode.jpg" width="200"><br /><img src="img/wx_zs_logo.png" height="25"></div>
</div>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
