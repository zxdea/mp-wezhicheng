<?php
  error_reporting(E_ALL^E_NOTICE^E_WARNING);

  include_once('config_fix.php');
  include_once('safe.inc.php');
  include_once('wxHelper.php');
  include_once('db.php');
  include_once('db1_2.php');
  include_once('userManager.php');
  include_once('TimeTableManager.php');
  include_once('eas.php');
  include_once('utils.php');
  include_once('mis/mis.class.php');
  include_once('wx_util.php');

  $server_url = "http://" . $_SERVER['HTTP_HOST'] . '/zctool/';
  //
  $openid = empty($_REQUEST['openid'])?$_REQUEST['wx_id']:$_REQUEST['openid'];
  $wx_id = $openid;
  $wx_util = new wx_util($wx_appid,$wx_secret);

  //
  if(file_exists("lock.txt")){
    $wx = new wxHelper();
    $msg = array(array('点击进入绑定学号','','',$server_url."open_bind.php?openid=$openid"));
    echo $wx->newsMsg($openid,$msg);
    exit();
  }
  //
  
  $action = $_REQUEST['action'];

  
  

  $user = new userManager(NULL,NULL,$openid);
  $eas = new EAS($user->stu_id,$user->stu_pw);
  $wx = new wxHelper();
  $weekarray=array("日","一","二","三","四","五","六");
  $week = date("w");
  
  function time_in($time1, $time2){
    $now_date = date("H:i");
    $ts =strtotime($now_date);//获得当前小时和分钟的时间时间戳
    $ts1=strtotime($time1);//获得指定分钟时间戳，00:00
    $ts2=strtotime($time2);//获得指定分钟时间戳，00:29
    if($ts>=$ts1 && $ts<=$ts2){
      return true;
    }else{
      return false;
    }
  }

  switch($action){
    case 'tm':
      $school_week= $eas->getWeek();
      echo $wx->txtMsg($openid, date("Y年m月d日")."\n星期$weekarray[$week]"."(第" . $school_week .  "周)");
      break;
    case 'tb':
      $class_time = array();
      if(date("n")>=10){
        $class_time = array(' 8:00',' 8:55','10:00','10:55','14:00','14:55','16:00','16:55','19:00');
      }else{
        $class_time = array(' 8:00',' 8:55','10:00','10:55','14:30','15:25','16:30','17:25','19:00');
      }

      if(!$user->isBinded()){
        $msg = array(array('点击进入绑定学号','','',$server_url ."open_bind.php?openid=$wx_id&jump=time_table.php?openid=$openid"));
        echo $wx->newsMsg($wx_id,$msg);
      }else{
        $wx_tb = array();
        $school_week = $eas->getWeek();
        $sd_week = 0;
        if($school_week%2==0){
          $sd_week = 2;
        }else{
          $sd_week = 1;
        }

        $h = date("H");
        date_default_timezone_set('PRC');
        ini_set('date.timezone','PRC');
        //var_dump(ini_get('date.timezone'));
        //var_dump($h);
        if($h>=20){
          //第二天
          $week = (date("w")+1)%7;
          if($week==1)
            $school_week+=1;//周一周数+1
          //$des = "明天没有课程哦～ \n[点击查看完整课表]";
          $arr =  array(date(" m月d日",strtotime("+1 day")).' 星期'.$weekarray[$week].' (明天)',$des,'',$server_url ."time_table.php?wx_id=$wx_id");
        }else{
          //$des = "今天没有课程哦～ \n[点击查看完整课表]";
          $arr =  array(date(" m月d日").' 星期'.$weekarray[$week].' (第'.$school_week.'周)',$des,'',$server_url . "time_table.php?wx_id=$wx_id");
        }
        $wx_tb[] = $arr;

        $db = new DB2();
        $sql = "SELECT * FROM timetable WHERE stu_id='$user->stu_id' and week='$week' and (b_week='0' or b_week<='$school_week' and e_week>='$school_week') and (sd_week='0' or sd_week='$sd_week') order by b_sec";
        $res = $db->query($sql);
        $tb_arr = $db->arr($res);

        foreach ($tb_arr as $tb) {
          $b_sec = sprintf("%02d", $tb['b_sec']);
          $e_sec = sprintf("%02d", $tb['e_sec']);
          $arr = array( $tb['course_name'] . "\n" . "$b_sec-$e_sec" . ' | ' . $class_time[$b_sec-1] . ' | ' . $tb['location'] ,'','',$server_url ."time_table.php?wx_id=$wx_id");
          $wx_tb[] = $arr;
        }
        $wx_tb[] = array(' 点击查看完整课表','','',$server_url ."time_table.php?wx_id=$wx_id");
        //$wx_tb[] = array('[推广]电信流量','','',"http://wx.fj.189.cn/wxtj!dotj.action?openid=DF8DD03B3BAABBD450F0C32C9D3F43F5582F1BA74D1E29DAAE52566121549686"); //AD
        echo $wx->newsMsg($wx_id,$wx_tb);
      }

      break;

    case 'ec':
      //晚刷
      if($user->isBinded()){
        $count = $eas->getExerciseCount(null);
        if($count>0)echo $wx->txtMsg($wx_id,"你已经晚刷了 $count 次💪");
        else{
          echo $wx->txtMsg($wx_id,"暂无晚刷记录，尝试<a href='https://jinshuju.net/s/g7GBhX'>手动查询</a>");
          ulog($openid,'EC','WARNING');
        }

      }else{
        $msg = array(array('点击进入绑定学号','','',$server_url."open_bind.php?openid=$openid"));
        echo $wx->newsMsg($wx_id,$msg);
      }
      break;
      case 'fc':
        //论坛
        if($user->isBinded()){
          $count = $eas->getForumCount();
          if($count>0)$msg = "你已经参加了 $count 次论坛";
          else echo $wx->txtMsg($wx_id,"暂无晚刷记录，尝试<a href='https://jinshuju.net/s/g7GBhX'>手动查询</a>");
          $msg = $msg ."\n" .'<a href="http://www.vieasy.cn/mobile.php?act=module&do=list&name=site&cid=1704&weid=1636">论坛报名</a>';
          echo $wx->txtMsg($openid,$msg);

        }else{
          $msg = array(array('点击进入绑定学号','','',$server_url."open_bind.php?openid=$openid"));
          echo $wx->newsMsg($openid,$msg);
        }
        break;
    case 'et':
      if($user->isBinded()){
        $eas->login();
        $et = $eas->getExamTime();
        $wx_news = array();
        $wx_news[] = array ('最近考试安排','','','');
        for($i=count($et)-1;$i>=0;$i--){
          $date_now = date("y-m-d",strtotime("-1 day"));
          if(count($wx_news)<10&&diff_date($et[$i][1],$date_now))$wx_news[] = array ($et[$i][0] . "\n" . $et[$i][1].' '. $et[$i][2] . ' ' .$et[$i][3] ,'','','');
        }

        echo $wx->newsMsg($openid,$wx_news);
        //var_dump($wx_news);
      }else{
        $msg = array(array('点击进入绑定学号','','',$server_url."open_bind.php?openid=$openid"));
          echo $wx->newsMsg($openid,$msg);
      }
      
      break;
    case 'news':
       $news = $eas->getNews();
       $data[] = array(' 教务通知');
       for($i=0;$i<count($news);$i++){
        if($i==5)break;
        $data[] = array($news[$i]['title'],'','',$news[$i]['url']);
       }
       echo $wx->newsMsg($wx_id,$data);
      break;
    case 'retake':
      $eas->login();
      $retake = $eas->getRetake();
      //var_dump($retake);
      $data[] = array(' 重修课程');
      for($i=0;$i<count($retake);$i++){
        if($i==9)break;
        $data[] = array($retake[$i]['name'] . "：" . $retake[$i]['state'],'','',"http://jwb.fdzcxy.com/m/default.asp");
       }
       $data[] = array(' 点击查看课程表','','',$server_url ."time_table.php?wx_id=$wx_id");
       echo $wx->newsMsg($wx_id,$data);
      break;
    case 'contacts':
      header("location:contacts.php?wx_id=$openid");
      break;
    case 'face':
      header("location:face/?openid=$openid");
      break;
    case 'lbs':
      // $access_list = [
      //   '211306416',
      //   '211306433',
      // ];

      // if(!in_array($user->stu_id,$access_list)){
      //   //不在许可名单内
      //   echo $wx->picMsg($wx_id,'0S_Kz47o2GtrGaK7m_2lBmxA10Gm0gkGo62-8TOP41Q');
      //   break;
      // }

      if(time_in('22:00','22:20')){
        $mis = new Mis();
        $mis->login('16246330ff4a1f4d174a4bb8e3343f8e','16246330ff4a1f4d174a4bb8e3343f8e');
        $mis_id = $user->getUserInfo('mis_id');
        $state = $mis->checkin($mis_id);
        if($state){
          echo "success";
        }else{
          echo $wx->picMsg($wx_id,'0S_Kz47o2GtrGaK7m_2lBmxA10Gm0gkGo62-8TOP41Q');
        }
      }else{
        echo $wx->picMsg($wx_id,'0S_Kz47o2GtrGaK7m_2lBmxA10Gm0gkGo62-8TOP41Q');
      }
      
      break;
    case 'msg':
      $msg = $_REQUEST['msg'];
      $to = $_REQUEST['to'];
      $to_user = new userManager($to,NULL,NULL);
      $to_openid = $to_user->getUserInfo('wx_id');
      ulog($openid,'MSG',$to . ':' . $msg);
      if(!empty($to_openid) && !empty($user->stu_id)){
        $wx_util->get_access_token();
        $res = $wx_util->send_message($to_openid, $user->getUserInfo('full_name') . " 的小纸条：\n\n " . $msg . "\n\n@{$user->stu_id}");
      }
      if($res){
        echo "小纸条已发送";
      }else{
        echo "小纸条传丢了-.-";
      }
      break;
  }

  //
  ulog($openid,$action,'WX_API');

  //$db->SQL("update config set value=value+1 where name='query_num'");
?>
