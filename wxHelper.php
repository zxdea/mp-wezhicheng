<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
include_once('config.inc.php');
  class wxHelper{
    public function isWeChat(){
      if(strpos($_SERVER["HTTP_USER_AGENT"],"MicroMessenger"))return true;else return false;
    }

    public function txtMsg($userid,$content){
      $time = strtotime('now');
      $text ="<xml><ToUserName>$userid</ToUserName><FromUserName>$wx_appid</FromUserName><CreateTime>$time</CreateTime><MsgType>text</MsgType><Content><![CDATA[$content]]></Content></xml>";
      return $text;
    }

    public function newsMsg($userid,$content){
      global $wx_appid;
      $time = strtotime('now');
      $count = count($content);
      $newsh = "<xml><ToUserName><![CDATA[$userid]]></ToUserName><FromUserName><![CDATA[$wx_appid]]></FromUserName><CreateTime>{$time}</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>{$count}</ArticleCount><Articles>";
      $newsf = "</Articles></xml>";
      foreach ($content as $c) {
        $newb = $newb. "<item><Title><![CDATA[{$c[0]}]]></Title> <Description><![CDATA[{$c[1]}]]></Description><PicUrl><![CDATA[{$c[2]}]]></PicUrl><Url><![CDATA[$c[3]]]></Url></item>";
      }
      return $newsh.$newb.$newsf;
    }

    public function picMsg($userid,$media_id){
        $time = strtotime('now');
        $data = "<xml><ToUserName><![CDATA[$userid]]></ToUserName><FromUserName><![CDATA[fromUser]]></FromUserName><CreateTime>$time</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId><![CDATA[$media_id]]></MediaId></Image></xml>";
        return $data;
    }


  }

  // $x=array(
  //   array('123','456','123','456'),array('789','4564','123','456')
  // );
?>
