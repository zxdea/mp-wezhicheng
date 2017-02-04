<?php
  include_once('config_fix.php');
  include_once('db1_2.php');
  include_once('userManager.php');

  function compress_html($string) {
      return ltrim(rtrim(preg_replace(array("/> *([^ ]*) *</","/<!--[^!]*-->/","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'),$string)));
  }

  function cleanBlank($str){
    return preg_replace("/\s/", "", $str);
  }

  function diff_date($date1,$date2){
    return strtotime($date1)>=strtotime($date2);
  }

  function kv($type,$bind,$key,$value=null){
      include_once('config_fix.php');
      include_once('db1_2.php'); //新版db
      $db = new DB2();
      $success = false;
      if(isset($value)){
        if($db->exist('kv',"`type`='$type' and `_key`='$key' and `bind`='$bind'")){
          $success = $db->set('kv',array('_value'=>$value),"`type`='$type' and `_key`='$key' and `bind`='$bind'"); 
        }else{
          $success = $db->insert('kv',array('bind'=>$bind,'type'=>$type,'_key'=>$key,'_value'=>$value));
        }
        return $success;
      }else{
        if(isset($key)){
          $res = $db->get('kv',"`type`='$type' and `_key`='$key' and `bind`='$bind'");
          $arr = $db->arr();
          return $arr[0];
        }else{
          $c="`type`='$type'";
          if(isset($bind))$c.= " and `bind`='$bind'";
          $db->get('kv',$c);
          return $db->arr();
        }
        
      }
  }

  function isBind($id){
    $db = new DB2();
    return $db->exist('user',"stu_id='$id' or wx_id='$id'");
  }

  function ulog($openid,$action,$detail=''){
      
      $db = new DB2();
      $user = new userManager(NULL,NULL,$openid);
      $db->insert('log',array(
        "openid"=> $openid,
        "user" => $user->stu_id,
        "action" =>$action,
        "detail"=>$detail
    ));
      //var_dump($db->error);
      //var_dump($db->sql);
  }

