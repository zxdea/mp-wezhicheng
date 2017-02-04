<?php
include_once('../safe.inc.php');
include_once('../config_fix.php');
include_once('../dbx.php');
$db = new DB();
//查询次数
//$query_num = $db->value('config','value',"name='query_num'");
$db->query('select count(*) as num from log where to_days(time) = to_days(now())');
$query_num = $db->array_result()[0]['num'];
//用户总数
$sql="SELECT * FROM `user` where wx_id is not null"; 
$db->query($sql);
$user_num = $db->num_rows;
//男生总数
$sql="SELECT * FROM `user` where sex='男'";
$db->query($sql);
$boy_num = $db->num_rows;
//女生总数
$sql="SELECT * FROM `user` where sex='女'";
$db->query($sql);
$girl_num = $db->num_rows;
//年级总数
$sql="SELECT grade FROM `user` group by grade";
$db->query($sql);
$grade_num = $db->num_rows;
//各届人数
$year_num = array();
for($y= date('Y');$y>date('Y')-5;$y--){
  $sql="SELECT * FROM `user` where grade='$y'";
  $db->query($sql);
  $num = $db->num_rows;
  $year_num[] = $num;
}
//通讯录
$sql="SELECT * FROM `user` where phone<>''";
$db->query($sql);
$phone_num = $db->num_rows;
//班级
$sql="SELECT grade,major,class,count(*) FROM `user` GROUP BY grade, major, class";
$db->query($sql);
$class_num = $db->num_rows;