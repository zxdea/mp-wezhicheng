<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
include_once('config.inc.php');
include_once('safe.inc.php');
include_once('utils.php');
include_once('db.php');
include_once('userManager.php');
include_once('TimeTableManager.php');
include('Valite.php');
include_once('gbk2utf8.php');
class EAS{
  protected $trytimes=5;
  protected $code_path = '';//SAE_TMP_PATH;
  protected $code_file="code.bmp";
  protected $stu_id,$stu_pw,$cookie;
  //protected $server_url = 'http://210.34.51.135/';
  //protected $server_url = 'http://jwb.fdzcxy.com/';
  protected $server_url = 'http://110.87.168.90/';

  protected $header = array(
    'Origin:http://jwb.fdzcxy.com',
    'Referer:http://jwb.fdzcxy.com/'
  );

  public function __construct($stu_id,$stu_pw){
    $this->stu_id=$stu_id;
    $this->stu_pw=$stu_pw;
  }

public function getWeek(){
  $db = new DB();
  $school_week = $db->getRow('school_week');
  $last_sc = $school_week['week'];
  $last_date = $school_week['update_date'];
  $sc = 0;
  $isLatest = date('Y-m-d')==date('Y-m-d',strtotime("$last_date"));
  if(date("w")==1&&!$isLatest){
    $sc = $this->getServerSchoolWeek();
    $date = date('Y-m-d');
    if($sc>0)$db->setValue('school_week',"week=$sc,update_date='$date'");
  }

    $last_week = intval(date('W',strtotime("$last_date")));
    $week = date('W');
    $sc = $week-$last_week+$last_sc;
  return $sc;
}
public function getData($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
  //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36');
	$result=curl_exec($ch);
	curl_close($ch);
  header("Content-type: text/html; charset=utf-8");
	return gbk2utf8($result);
}

public function postData($url,$data=null,$header=null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	if($header){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
  curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
	$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$result=curl_exec($ch);
	curl_close($ch);
	return gbk2utf8($result);
}

  public function getCookie($url=null){
    if($url==null){
      $url=$this->server_url . "m/default.asp";
    }
    
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_HEADER,1); //将头文件的信息作为数据流输出
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //返回获取的输出文本流
    $content = curl_exec($ch); //执行curl并赋值给$content
    //preg_match('/Set-Cookie:(.*);/iU',$content,$str); //正则匹配
    preg_match_all('/Set-Cookie:(.*);/iU', $content, $str);
    foreach ($str[1] as $cookie) {
      $this->cookie .= $cookie . ';';
    }
    curl_close($ch); //关闭curl
    return $content;
  }

public function getCode(){
     $url = $this->server_url . 'ValidateCookie.asp';
    $imageData = $this->getData($url);
    file_put_contents($this->code_path.$this->code_file,$imageData);
  }

public function recCode(){
  $this->getCode();
  $valite = new Valite();
  $valite->setImage($this->code_path.$this->code_file);
  $valite->getHec();
  $ert = $valite->run();
  @unlink($this->code_file);
  return $ert;
}

public function checkCode($code){
  $url = $this->server_url .'ajax/chkCode.asp?code='.$code;
  $res = $this->getData($url);
  if($res=="ok")return true;else return false;
}

public function stupid_pw_encrypt($id,$pw,$code){
    $code = strtolower($code);
    $pw_md5 = md5($pw);
    $new_pw = md5($pw_md5 . 'zcjw' . $code) . md5(substr($pw_md5,8,16) . 'zcjw' . $code) . md5($pw . $id) . '1';
    return $new_pw;
}

  public function login(){
    $this->getCookie();
    $url=$this->server_url . "loginchk.asp?ver=m";
    $try=0;
    $state_code = 0;
    while($try<$this->trytimes)
    {
      $code = $this->recCode();
      if($this->checkCode($code)==true)
      {
        $passwd = $this->stupid_pw_encrypt($this->stu_id,$this->stu_pw,$code);
        $res = $this->postData($url,"muser=$this->stu_id&passwd=$passwd&code=".$code);

        //var_dump($res);
        if(strstr($res,"验证码不正确"))$state_code = -1;
        if(strstr($res,"密码出错"))$state_code = -2;
        if(strstr($res,"LOGOUT.ASP"))$state_code = 1;

        break;
      }
      $try++;
    }
    return $state_code;
  }

	public function view(){
		echo $this->stu_id.",".$this->stu_pw;
	}

  public function getUserInfo($action='save'){
    $res = $this->getData($this->server_url ."JBXX/jxxx_xs/xsxx_view.asp");
    $res = str_replace("&nbsp;","",$res);
    preg_match_all('/<td width="79" align="center">(\S+)<\/td>/', $res, $m);
    $name = $m[1][0];
    if(preg_match('/>男</', $res))$sex='男';else $sex='女';
    preg_match_all('/\d+-\d+-\d+/', $res, $m);
    $birthday = $m[0][0];
    preg_match_all('/2">([^\d<]+)</', $res, $m);
    $major = $m[1][0];
    preg_match_all('/>([0-9X]{18})</', $res, $m);
    $sfz = $m[1][0];
    preg_match_all('/>(\d)</', $res, $m);
    $class = $m[1][0];
    preg_match_all('/>(\d{4})</', $res, $m);
    $grade = $m[1][0];

    $stu = array(
        "id"=>$this->stu_id,
        "name"=>$name,
        "sex"=>$sex,
        "birthday"=>$birthday,
        "major"=>$major,
        "grade"=>$grade,
        "class"=>$class,
        "id_num"=>$sfz
      );

    switch ($action) {
      case 'save':
        $user = new userManager($this->stu_id,NULL,NULL);
        $user->updateUserInfo("full_name",$name);
        $user->updateUserInfo("sex","$sex");
        $user->updateUserInfo("birthday","$birthday");
        $user->updateUserInfo("major","$major");
        $user->updateUserInfo("class","$class");
        $user->updateUserInfo("idNum","$sfz");
        $user->updateUserInfo("grade","$grade");
        break;
    }
    return $stu; //$res
  }

  public function getServerSchoolWeek(){
    $res = $this->getData($this->server_url ."m/default.asp");
    if(strstr($res,"周")){
        preg_match_all('/\d+(?=周)/', $res, $m);
        return $m[0][0];
    }else return 0;

  }

  // public function getExerciseCount($id=null){
  //   $count = 0;
  //   if($id==null)$id=$this->stu_id;
  //   $user = new userManager($this->stu_id,null,null);
  //   $res = $this->getData("http://jinshuju.net/s/g7GBhX?q%5B0%5D%5Bfield_1%5D={$user->getUserInfo('full_name')}&q%5B0%5D%5Bfield_2%5D=$id");
  //   //var_dump($res);
  //   preg_match_all('/次数\s*<\/td>\s*<td>\s*(\d{1,2})/', $res, $m);
  //   $count = $m[1][0];
  //   return $count;
  // }

  //https://jinshuju.net/s/g7GBhX/verify

  public function getExerciseCount($id=null){
    $count = 0;
    if($id==null)$id=$this->stu_id;
    if($id=="211506186")return 0; //奇葩防御补丁
    $this->cookie .= 'rs_token_g7GBhX=0;';
    $res = $this->getData("http://jinshuju.net/s/g7GBhX?q[0][field_2]={$id}");
    preg_match_all('/次数\s*<\/td>\s*<td>\s*(\d{1,2})/', $res, $m);
    $count = intval($m[1][count($m[1])-1]);
    if($count==0){
      $res = $this->getData("http://jinshuju.net/s/g7GBhX?q[0][field_2]=S{$id}");
      preg_match_all('/次数\s*<\/td>\s*<td>\s*(\d{1,2})/', $res, $m);
      $count = intval($m[1][count($m[1])-1]);
    }
    return $count;
  }


public function getExerciseCount_old($id=null,&$out){
    $count = 0;
    if($id==null)$id=$this->stu_id;
    $url = "http://jsform4.com/q/azsuqu";
    $this->getCookie($url);
    $res = $this->getData($url);
    preg_match_all('/\w{24}/', $res, $m);
    $frmid = $m[0][0];
    $data = ['FRMID'=>$frmid,"F2"=>$id];
    $pw = ['FRMID'=>$frmid,"PWD"=>'0'];
    $res = $this->postData("http://jsform4.com/web/pubdata/checkpwd",json_encode($pw),['Content-Type: application/json;']);
    $res = $this->postData("http://jsform4.com/web/pubdata/query",json_encode($data),['Content-Type: application/json;']);
    $json = json_decode($res,true);
    $out = $json['rows'][0];
    return $json['rows'][0]['F3'];
  }

  public function getForumCount(){
    //http://zcxy.sinaapp.com/wxz/display.php
    $count = -1;
    $res = $this->postData("http://zcxy.sinaapp.com/wxz/search.php","no=S$this->stu_id");
    if(strstr($res,"次数：")){
        preg_match_all('/次数：<\/label>\s*(\d+)/', $res, $m);
        $count = $m[1][0];
    }else{
      $res = $this->postData("http://zcxy.sinaapp.com/wxz/search.php","no=$this->stu_id");
      if(strstr($res,"次数：")){
          preg_match_all('/次数：<\/label>\s*(\d+)/', $res, $m);
          $count = intval($m[1][0]);
      }
    }
    return $count;
  }

  public function getExamTime($year='',$term=''){
    $res = compress_html($this->getData($this->server_url . "ksgl/ksap/ksap_xs_list.ASP?xn=$year&xq=$term"));
    preg_match_all('/<td align="center"\s*>(.*?)<\/td>/', $res, $m);
    $num = count($m[1]);
    $ExamTimeArray = array();
    for($i=1,$j=4,$k=5;$i<$num;$i=$i+7,$j=$j+7,$k=$k+7){
      preg_match_all('/\((.{2,}?)\)$/', cleanBlank($m[1][$i]), $m0);
      preg_match_all('/\d+-\d+-\d+/', cleanBlank($m[1][$j]), $m1);
      preg_match_all('/\d+:\d+--\d+:\d+/', cleanBlank($m[1][$j]), $m2);
      $exam_name = $m0[1][0];
      $exam_date = $m1[0][0];
      $exam_time = $m2[0][0];
      $exam_loc = cleanBlank($m[1][$k]);
      $ExamTimeArray[] = array($exam_name,$exam_date,$exam_time,$exam_loc);
    }
    //var_dump($ExamTimeArray);
    return $ExamTimeArray;
  }

  public function getTimeTable($type=0){
    $res = $this->getData($this->server_url . "kb/kb_xs.asp");
    $res = compress_html($res);//压缩html
    $res = preg_replace('/href=".*?"/', "", $res); //清除链接
    $res = preg_replace('/&nbsp;/', "", $res); //清除&nbsp;
    //获取完整课表
    preg_match_all('/<table width="880.*<\/table>/', $res, $m1);
    $full_tb= '<div id="full_tb" class="table-responsive">' . $m1[0][0] . '</div>';
    //获取主要课表
    preg_match_all('/<table width="440" height="400" cellspacing="0" cellpadding="1" align="center" style="border-collapse: collapse" border="1" bordercolor="#111111">.*<\/table>/', $res, $m2);
    preg_match_all('/<\/td>[\s\S]+<\/table>/', $m2[0][0], $m21);
    $main_tb='<table width="100%" align="center" border="1"><tr><td align="center">#</td>' . $m21[0][0];
    //保存主要课表
    if(!empty($main_tb)){
      $db = new DB();
      if(!$db->isExist("general","bind='$this->stu_id'"." and type='main_tb'")){
        $db->addValue("general","type,bind,content","'main_tb','$this->stu_id','$main_tb'");
      }else{
        $db->setValue("general","content='$main_tb'","bind='$this->stu_id' and type='main_tb'");
      }
    }
    //保存完整课表
    if(!empty($full_tb)){
      $db = new DB();
      if(!$db->isExist("general","bind='$this->stu_id'"." and type='full_tb'")){
        $db->addValue("general","type,bind,content","'full_tb','$this->stu_id','$full_tb'");
      }else{
        $db->setValue("general","content='$full_tb'","bind='$this->stu_id' and type='full_tb'");
      }
    }
    //获取单节课表数据
    $tbm = new TimeTableManager($this->stu_id);
    $tbm->cleanTimeTable();//清除旧数据
    $tbm->saveTimeTable();
    //返回类型
    switch($type){
      case 0:
      return $main_tb;
      break;
      case 1:
      return $full_tb;
      default:
      return $main_tb;
    }
  }

  public function getTimeTableArray(){
    $db = new DB();
		$tb = $db->getValue("general","content","type='main_tb' and bind='$this->stu_id'");
    if(empty($tb))$tb = getTimeTable(0);
    $tb = str_replace("<br>","",$tb); //格式化表
    foreach ($m3 as $tb){
      //var_dump($tb);
    }
  }

  public function getReport(){
    $res = $this->getData($this->server_url ."cjgl/cx/teach_list.asp");
    $res = $this->getData($this->server_url . "cjgl/cx/zhcx_xs.asp");
    preg_match_all('/class="altbg1" >[\s\S]+<\/table>/', $res, $m);
    if(!empty($m[0][0]))$report = '<div class="table-responsive">' . '<table class="table table-striped table-bordered"><tr align="center"><td>选课时间</td><td>课程名称</td><td>课程学分</td><td>成绩</td><td>绩点</td><td>获得学分</td><td>考试类型</td><td>选修类型</td><td>备注</td></tr><tr ' . $m[0][0] . '</div>';
    $report = str_replace("以上成绩仅供参考，如有问题请咨询教务部","",$report);
    return $report;
  }

  public function getNews(){
    $res = $this->getData($this->server_url ."m/default.asp");
    preg_match_all("/<a href='(.*?)' title=(.*?) >/", $res, $m1);
    $news = array();
    for($i=0;$i<count($m1[1]);$i++){
      $news[] = array("title"=>$m1[2][$i],"url"=>$m1[1][$i]);
    }
    return $news;
  }

  public function getRetake(){
    $res = $this->getData($this->server_url ."xkgl/cxxk/cxxk.asp?menu_no=0503");
    preg_match_all('/<tr class=\"vzebra-\w*?\">\s*<td align=\"center\">(\S*?)\s*<\/td>\s*<td align=\"center\">.*?<\/td>\s*<td align=\"center\">.*?<\/td>\s*<td align=\"center\">(.*?)<\/td>\s*<td align=\"center\">.*?<\/td>\s*<td align=\"center\">.*?<\/td>\s*<td align=\"center\">.*?<\/td>\s*<td align=\"center\">(.*?)<\/td>/is', $res, $m);
    $retake_list = array();
    //var_dump($m);
    for($i=0;$i<count($m[1]);$i++){
      $state = $m[2][$i];
      if(strpos($m[3][$i],'选课')){
          $state = '可以选课';
      }
      $retake_list[] = array("name"=>$m[1][$i],"state"=>$state);
    }
    //var_dump($retake_list);
    return $retake_list;
  }

}

 // $eas = new EAS();
 // var_dump($eas->getExerciseCount('211206436'));