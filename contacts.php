<?php
  include_once('config.inc.php');
  include_once('safe.inc.php');
  include_once('src_check.php');
  include_once('db.php');
  include_once('userManager.php');
  include_once('eas.php');
  header("Content-type: text/html; charset:utf-8");
  
  $openid = empty($_REQUEST['openid'])?$_REQUEST['wx_id']:$_REQUEST['openid'];
  $wx_id = $openid;
  
  $myphone=$_REQUEST['phone'];
  $user = new userManager(NULL,NULL,$wx_id);

  //
  if(file_exists("lock.txt")){
    header("location:open_bind.php");
    exit();
  }
  //
  
  if(!$user->isBinded())header("location: bind.php?wx_id=$wx_id&jump_url=contacts.php?wx_id=$wx_id");
  $phone = $user->getUserInfo("phone");

  if(!empty($myphone)&& strlen($myphone)==11){
    $user->updateUserInfo("phone",$myphone);
    header("location: contacts.php?wx_id=".$wx_id);
  }
  $grade = $user->getUserInfo("grade");
  $major = $user->getUserInfo("major");
  $class = $user->getUserInfo("class");

  $tbh = '<table class="table table-bordered table-striped"><thead><tr><th>名字</th><th>电话</th></tr></thead><tbody>';
  $tbf ='</tbody></table>';
  $db= new DB();
  $res = $db->SQL("SELECT * FROM `user` WHERE grade='$grade' and major='$major' and class='$class' order by convert(full_name using gbk) asc");
  while($row = $res->fetch_assoc()){
    //var_dump($row);
    //echo $row['full_name'].$row['phone']."<br/>";
    $tb=$tb."<tr><td>" .$row['full_name'].'</td><td><a href="tel:'.$row['phone'] .'">' . $row['phone']. "</a></td></tr>";
  }
  $tb=$tbh.$tb.$tbf;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $major.$class.'班';?> 通讯录</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="http://www.yun-li.com/wx/js.config.php"></script>
<script>
wx.ready(function(){
  wx.hideAllNonBaseMenuItem();
});
</script>

<nav class="navbar navbar-default navbar-static-top">
    <div class="container"> <a href="index.html"><img src="img/logo.png"></a>
  </div>
</nav>

<div class="container">



<?php
if(empty($phone)||$_REQUEST['action']=="update"){
  $reghtml =  '<form method="POST"><div class="form-group"><input type="tel" class="form-control" name="phone" placeholder="请输入你的手机号码" required></div><div class="text-center"><button type="submit" class="btn btn-primary">保存联系方式</button></div></form>';
  echo $reghtml;
}else
{
  echo $tb;
  echo '<div class="text-right"><a href="welcome.html">邀请加入</a> <a href="?action=update&wx_id='. $wx_id .'">更换号码</a></div>';
}
?>



</div>
<div class="text-center" style="margin-top:20px;"></div>
<div class="text-center" style="color:#CCC; margin:10px;"><small>Copyright &copy; 云力网络科技 All Rights Reserved</small></div>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
