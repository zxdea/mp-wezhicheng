<?php
  // include_once('db.php');
  // include_once('eas.php');
  // $eas = new EAS('211306416','2015090');
  // $eas->login();
  // $eas->getRetake();
  //echo $eas->getWeek();
  // $db = new DB();
  // $school_week = $db->getRow('school_week');
  // $last_sc = $school_week['week'];
  // $last_date = $school_week['update_date'];
  // if(date('Y-m-d')==date('Y-m-d',strtotime("$last_date"))){
  //   $last_week = intval(date('W',strtotime("$last_date")));
  //   $week = date('W');
  //   $sc = $week-$last_week+$last_sc;
  // }else{
  //   if(date("w")==1){
  //     $sc = $eas->getServerSchoolWeek();
  //     $date = date('Y-m-d');
  //     $db->setValue('school_week',"week=$sc,update_date='$date'");
  //   }
  // }
  // echo  $sc;
// $redis = new Redis();
// $redis->connect('127.0.0.1', 6379);
// echo "Connection to server sucessfully";
//      //查看服务是否运行
// echo "Server is running: " . $redis->ping();
// $redis->setbit('bit','aa');

// echo substr('201606123',0,4);
// 
// include_once('config.inc.php');
// include_once('config_fix.php');
// include_once('db1_2.php'); 
// include_once('wx_util.php');

// $db = new DB2();
// $wx = new wx_util($wx_appid,$wx_secret);
// $wx->get_access_token();

// var_dump($wx->send_message('oD46HwwKF0bA2_DJ0c3PHaWEg5-s','消息测试'));

echo date('Y-m-d',strtotime("2016-9-10"));
