<?php
	include_once('config.inc.php');
	include_once('safe.inc.php');
	include_once('src_check.php');
	include_once('wxHelper.php');
	include_once('db.php');
	include_once('userManager.php');
	include_once('eas.php');

	//
  	if(file_exists("lock.txt")){
    header("location:open_bind.php");
    exit();
  	}
  	//
	header("Content-type: text/html; charset:utf-8");
	//
	
	$openid = empty($_REQUEST['openid'])?$_REQUEST['wx_id']:$_REQUEST['openid'];
	$wx_id = $openid;

	//
	$action=$_REQUEST['action'];

	if(!empty($wx_id)){
		$user = new userManager(NULL,NULL,$wx_id);
		if(!$user->isBinded())header("location: bind.php?wx_id=$wx_id&jump_url=time_table.php?wx_id=$wx_id");
		$db = new DB();
		$main_tb = $db->getValue("general","content","type='main_tb' and bind='$user->stu_id'");
		$full_tb = $db->getValue("general","content","type='full_tb' and bind='$user->stu_id'");


		if(empty($main_tb)||$action=='refresh'||date("w")==1){
			$eas = new EAS($user->stu_id,$user->stu_pw);
			$login = $eas->login();
			if($login==1){
				$view = $eas->getTimeTable(0);
			}
		}else{
			if($action=="full_tb")$view = $full_tb;else $view = $main_tb;
		}

		if($login==-2)$view="<p align='center'>无法获取课表，请尝试<a href='bind.php?wx_id=$wx_id&jump_url=time_table.php?wx_id=$wx_id'>重新绑定</a></p>";

		if(empty($view))$view = "<p align='center'>暂时无法获取课程表:(</p>";

	}else header("location: welcome.html");

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $user->getUserInfo("full_name");?>的课程表</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="favicon.ico" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
	<div id='wx_pic' style='margin:0 auto;display:none;'><img src='img/wx_logo.jpg' /></div>

	<nav class="navbar navbar-default" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
          <a href="index.html"><img src="img/logo.png"></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
          	 <li><a href="?wx_id=<?php echo $wx_id;?>&action=full_tb">完整课表</a></li>
          	 <li><a href="?wx_id=<?php echo $wx_id;?>&action=refresh">更新课表</a></li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

<div class="container">
<?php
echo $view;
?>
<div class="text-center" style="color:#666"><br>点击●●●可以分享给朋友哦</div>
</div>
<div class="text-center" style="margin-top:5px;"><a href="welcome.html"><img src="img/wx_zs_logo.png" height="20" /></a></div>
<div class="text-center" style="color:#CCC; margin:10px;"><small>Copyright &copy; 云力网络科技 All Rights Reserved</small></div>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
<!--By:Blue zzx094@gmail.com-->
