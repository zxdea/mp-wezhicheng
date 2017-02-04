<?php
	include_once('config.inc.php');
	include_once('safe.inc.php');
	include_once('src_check.php');
	include_once('wxHelper.php');
	include_once('userManager.php');
	include_once('eas.php');
	 //
  	if(file_exists("lock.txt")){
	    header("location:open_bind.php");
	    exit();
 	}
  	//
  
	$openid = empty($_REQUEST['openid'])?$_REQUEST['wx_id']:$_REQUEST['openid'];
	$wx_id = $openid;
	
	$action=$_REQUEST['action'];

	if(!empty($wx_id)){
		$user = new userManager(NULL,NULL,$wx_id);
		if(!$user->isBinded())header("location: open_bind.php?openid=$wx_id&jump=report.php?wx_id=$wx_id");

			$eas = new EAS($user->stu_id,$user->stu_pw);
			$login = $eas->login();
			if($login==1){
				$view = $eas->getReport();
				if(empty($view))
					$view = '<p align="center">有新的成绩公布啦~ <a href="http://jwb.fdzcxy.com/m/default.asp">立即登陆评教</a></p>';

			}else if($login==-2){
				$view="<p align='center'>无法获取成绩单，请尝试<a href='open_bind.php?openid=$wx_id&jump=report.php?wx_id=$wx_id'>重新绑定</a></p>";
			}

	}else header("location: welcome.html");

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $user->getUserInfo("full_name");?>的成绩单</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="http://www.yun-li.com/wx/js.config.php"></script>
<script>
wx.ready(function(){
	wx.hideAllNonBaseMenuItem();
	wx.showMenuItems({
    menuList: [
    	'menuItem:share:appMessage'
    ] // 要显示的菜单项，所有menu项见附录3
	});
});
</script>

	<div id='wx_pic' style='margin:0 auto;display:none;'><img src='img/wx_logo.jpg' /></div>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container"> <a href="index.html"><img src="img/logo.png"></a>
  </div>
</nav>
<div class="container">
<?php
echo $view;
?>
</div>
<div class="text-center" style="margin-top:20px;"></div>
<div class="text-center" style="color:#CCC; margin:10px;"><small>Copyright &copy; 云力网络科技 All Rights Reserved</small></div>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
