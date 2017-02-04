<?php

header("location:open_bind.php?openid={$_REQUEST['wx_id']}&jump={$_REQUEST['jump_url']}");

//
 error_reporting(E_ALL^E_NOTICE^E_WARNING);
  include_once('config.inc.php');
  include_once('safe.inc.php');
  //include_once('src_check.php');
  include_once('db.php');
  include_once('userManager.php');
  include_once('eas.php');

  function refererCheck(){
    //来源检测
    return strstr($_SERVER["HTTP_REFERER"],$_SERVER['SERVER_NAME']);
  }

  $stu_id=$_REQUEST['stu_id'];
  $stu_pw=$_REQUEST['stu_pw'];
  $wx_id=$_REQUEST['wx_id'];
  if(isset($_REQUEST['openid']))$wx_id=$_REQUEST['openid'];
  $wx_name=$_REQUEST['wx_name'];
  $jump_url=$_REQUEST['jump_url'];
  //$url=$_REQUEST['_url'];
  //if(empty($url))$url=$jump_url;
  $goto = $_REQUEST['goto'];

  if(!empty($stu_id)&&!empty($stu_pw)&&!empty($wx_id)){
    if(!refererCheck())exit();
    
    $eas = new EAS($stu_id ,$stu_pw);
    $login = $eas->login();
    if($login==1)
    {
      $user = new userManager($stu_id,$stu_pw,$wx_id);
      var_dump(strlen($user->wx_id));
      var_dump(strlen($wx_id));
      if(strlen($user->wx_id)==28&&strlen($wx_id)!=28){
        $msg="<span class='label label-warning'>重复绑定</span>";
      }else{
        $bind = $user->bindID();
        $eas->getUserInfo();
        $user->updateUserInfo("wx_name",$wx_name);
        if($bind)$msg="<span class='label label-success'>绑定成功</span>";else $msg="<span class='label label-warning'>绑定失败</span>";
      }
    }

    if($login==-1)$msg="<span class='label label-warning'>登陆失败</span>";
    if($login==-2)$msg="<span class='label label-danger'>信息有误</span>";
  }
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
if(!empty($goto)){
  echo "<meta http-equiv=refresh content='1;url=$goto'>";
}
?>
<title>绑定学号</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container"> <a href="index.html"><img src="img/logo.png"></a>
  </div>
</nav>
<div class="container">

<form action="bind.php?wx_id=<?php echo $wx_id;?>&jump_url=<?php echo $jump_url;?>" method="post" class="form-horizontal">
  <div class="form-group">
    <label class="col-sm-2 control-label">学号</label>
    <div class="col-sm-10">
      <input class="form-control" name="stu_id" placeholder="" value="<?php echo $_GET['id'];?>"required>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label">密码</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="stu_pw" placeholder="" value="<?php echo $_GET['pw'];?>"required>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input type="checkbox" checked="checked" required>
          接受 <a href="#" target="_blank">用户协议</a> </label>
      </div>
      <div class="text-right">
<?php echo $msg;?>
</div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10 text-right">
      <button type="submit" class="btn btn-default">绑定</button>
    </div>
  </div>
<input type="hidden" name="wx_name" value="<?php echo $wx_name;?>">
<input type="hidden" name="goto" value="<?php echo $jump_url;?>">
</form>
</div>
<div class="text-center" style="margin-top:20px;"><a href="./welcome.html"><img src="img/wx_zs_logo.png" height="20"></a></div>
<div class="text-center" style="color:#CCC; margin:10px;"><small>Copyright &copy; 至诚小助手 All Rights Reserved</small></div>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
