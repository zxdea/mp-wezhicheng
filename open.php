<?php
  error_reporting(E_ALL^E_NOTICE^E_WARNING);
  //ini_set('display_errors', '1');
  header('Access-Control-Allow-Origin: *');
  header("Content-type:text/html;charset=utf-8");
	include_once('config_fix.php');
	include_once('db1_2.php'); //新版db
	include_once('db.php');
	include_once('safe.inc.php');
  include_once('wxHelper.php');
  include_once('userManager.php');
  include_once('TimeTableManager.php');
  include_once('eas.php');

  //if(strstr($_SERVER['HTTP_USER_AGENT'],"iPhone"))echo "<!--By:Blue zzx094@gmail.com-->";

	$uid = $_GET['id'];
  $openid = $_GET['openid'];
	$pw = $_GET['pw'];
	$action = $_GET['action'];
  $db = new DB2();
  if(isset($openid)){
    $user = new userManager(NULL,NULL,$openid);
  }else{
    $user = new userManager($uid,NULL,NULL);
  }
	
  $eas = new EAS($user->stu_id,$user->stu_pw);
  $bind = $user->isBinded();
  $pass = (md5($user->stu_pw) == $pw)||(isset($openid)&&$bind);

  $weekarray=array("日","一","二","三","四","五","六");
  $week = date("w");
  $school_week= $eas->getWeek();
  $sd_week=0;
  if($school_week%2==0)$sd_week=2;else $sd_week=1; //单双周

  switch($action){
    case 'isbind':
      echo strval($bind);
      break;
    case 'state':
      $login = $eas->login();
      $pass = ($login==1);
      $data = array(
        "bind"=>$bind,
        "pass"=>$pass
        );
      echo json_encode($data);
      break;
		case 'week':
    	$data = array(
    		"week"=>$school_week
    		);
    	echo json_encode($data);
    	break;
    case 'table':
      if(!$bind||!$pass)exit();
      switch($_GET['out']){
        case 'all':
          $sql = "SELECT * FROM timetable WHERE stu_id='$user->stu_id'";
          break;
        case 'today':
          $sql = "SELECT * FROM timetable WHERE stu_id='$user->stu_id' and week='$week' and (b_week='0' or b_week<='$school_week' and e_week>='$school_week') and (sd_week='0' or sd_week='$sd_week') order by b_sec";
          break;
        default:
          $sql = "SELECT * FROM timetable WHERE stu_id='$user->stu_id' and week='$week' and (b_week='0' or b_week<='$school_week' and e_week>='$school_week') and (sd_week='0' or sd_week='$sd_week') order by b_sec";
      }
      if($_GET['update']=="true"){
        $eas->login();
        $eas->getTimeTable(0);
      }
      
      $db->query($sql);
      $tb = $db->arr();
      echo json_encode($tb);
      break;
    case 'exam':
      if(!$bind||!$pass)exit();
      $eas->login();
      $et = $eas->getExamTime();
      $data = array();
      for($i=count($et)-1;$i>=0;$i--){
        $date_now = date("y-m-d",strtotime("-1 day"));
        if(diff_date($et[$i][1],$date_now)){
          $data[] = array (
          "name"=>$et[$i][0],
          "date"=>$et[$i][1],
          "time"=>$et[$i][2],
          "loc"=>$et[$i][3]
          );
        }
      }
      echo json_encode($data);
      break;
    case 'ex_count':
      $id = empty($uid)?$user->stu_id:$uid;
      $count = $eas->getExerciseCount($id);
      $data = array(
        "count"=>$count
        );
      echo json_encode($data);
      break;
    case 'credit':
      if(!$bind||!$pass)exit();
      $eas->login();
      echo $eas->getReport().'<div class="text-center" style="color:#CCC; margin:10px;text-align: center;"><small>Copyright &copy; 云力网络科技 All Rights Reserved</small></div>
';
      break;
    case 'user':
      if(!$bind||!$pass)exit();
      $db->get('user',"stu_id='$user->stu_id'");
      $user = $db->arr()[0];
      $data = array(
        "openid"=>$user['wx_id'],
        "id"=>$user['stu_id'],
        "name"=>$user['full_name'],
        "birthday"=>$user['birthday'],
        "sex"=>$user['sex'],
        "grade"=>$user['grade'],
        "major"=>$user['major'],
        "class"=>$user['class'],
        "phone"=>$user['phone']
        );
      echo json_encode($data);
      break;
    case 'contacts':
      if(!$bind||!$pass)exit();
      if(isset($_GET['phone'])){
        $user->updateUserInfo("phone",intval($_GET['phone']));
      }
      $grade = $user->getUserInfo("grade");
      $major = $user->getUserInfo("major");
      $class = $user->getUserInfo("class");
      $db->query("SELECT * FROM `user` WHERE grade='$grade' and major='$major' and class='$class' order by full_name");
      $contacts = $db->arr();
     
      $data = array();
      foreach ($contacts as $c) {
        $data[] = array(
          "name"=>$c['full_name'],
          "phone"=>$c['phone']
          );
      }
      echo json_encode($data);
      break;
    case 'kv':
      $key = $_GET['key'];
      $value = $_GET['value'];
      $type = $_GET['type'];
      $bind = $_GET['bind'];
      $success = false;
      if(isset($value)){
        if($db->exist('kv',"`type`='$type' and `_key`='$key' and `bind`='$bind'")){
          $success = $db->set('kv',array('_value'=>$value),"`type`='$type' and `_key`='$key' and `bind`='$bind'"); 
        }else{
          $success = $db->insert('kv',array('bind'=>$bind,'type'=>$type,'_key'=>$key,'_value'=>$value));
        }
        $res = array('success'=>$success);
        echo json_encode($res);
      }else{
        if(isset($key)){
          $res = $db->get('kv',"`type`='$type' and `_key`='$key' and `bind`='$bind'");
          $arr = $db->arr();
          echo json_encode($arr[0]);
        }else{
          $c="`type`='$type'";
          if(isset($bind))$c.= " and `bind`='$bind'";
          $db->get('kv',$c);
          echo $db->json();
        }
        
      }
      
      break;
    default:
        $host = "http://" . $_SERVER['HTTP_HOST'] . '/zctool/';
        $html=<<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>微至诚开放平台</title>
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>

<div class="container">
  <h1>微至诚开放平台</h1>
  <hr>

  <h2>平台接入标识</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      LOGO
    </blockquote>  
  </div>
  <div class="panel-footer">
    <img src="img/wx_zs_logo.png">
  </div>
  </div>

  <h2>用户绑定</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open_bind.php
    </blockquote>  
  </div>
  <div class="panel-footer">
    为确保用户信息安全，需要用户手动登陆绑定，绑定成功后 header/cookie 均会返回openid
  </div>
  </div>

  <h2>用户状态查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      <p>{$host}open.php?action=state&openid={用户唯一标识}</p>
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"bind":绑定状态,"pass":密码验证状态}
  </div>
  </div>

  <h2>用户信息查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=user&openid={用户唯一标识}
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"openid":用户唯一标识,"id":学号,name":姓名,"birthday":生日,"sex":性别,"grade":年级,"major":专业,"class":班级,"phone":手机}
  </div>
  </div>

  <h2>班级通讯录查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=contacts&openid={用户唯一标识}&[phone={手机号} 可选参数:更新手机号码]
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"name":姓名,"phone":手机}
  </div>
  </div>

  <h2>课程查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=table&openid={用户唯一标识}&out={all:全部|today:当天课程}}
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"id":"课程id","stu_id":"学号","course_name":"课程名称","week":"星期","b_sec":"开始节","e_sec":"结束节","location":"上课地点","sd_week":"单双周","b_week":"开始周数","e_week":"结束周数"}
  </div>
  </div>

  <h2>考试查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=exam&openid={用户唯一标识}
    </blockquote>  
  </div>
  <div class="panel-footer">
   {"name":"考试名称","date":"考试日期","time":"考试时间","loc":"考试地点"}
  </div>
  </div>

  <h2>周数查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=week
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"week":周数}
  </div>
  </div>

  <h2>晚刷次数查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=ex_count&id={学号}
    </blockquote>  
  </div>
  <div class="panel-footer">
    {"count":次数}
  </div>
  </div>

  <h2>成绩查询</h2>
  <div class="panel panel-default">
  <div class="panel-body">
    <blockquote>
      {$host}open.php?action=credit&openid={用户唯一标识}
    </blockquote>  
  </div>
  <div class="panel-footer">
    返回成绩单html
  </div>
  </div>

</div>
<div class="text-center" style="color:#CCC; margin:10px;"><small>Copyright &copy; 云力网络科技 All Rights Reserved</small></div>
</body>
</html>
<!--By:Blue zzx094@gmail.com-->
        
HTML;
        echo $html;
  }
  
if(!empty($action)){
  ulog($openid,$action,'OPEN_API');
}
