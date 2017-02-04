<?php
include_once('config.inc.php');
include_once('safe.inc.php');
include_once('db.php');
include_once('userManager.php');

class TimeTableManager{
    protected $stu_id;

    public function __construct($stu_id){
      $this->stu_id=$stu_id;
    }

    public function getWeek(){
      global $school_begin_week;
      return intval(date('W',strtotime("now")))-$school_begin_week+1;
    }

    public function cleanTimeTable(){
      $db = new DB();
      $db->SQL("DELETE FROM `timetable` WHERE `stu_id` = $this->stu_id");
    }

    public function getRandTimeTable(){
      $db = new DB();
      $res = $db->SQL("select * from general where type='full_tb' and content<>'' order by rand() limit 1");
      while($row = $res->fetch_assoc()){
        return $row['content'];
      }
    }

    public function saveTimeTable(){
      $db = new DB();
      //从数据库读取课表
      $full_tb = $db->getValue("general","content","type='full_tb' and bind='$this->stu_id'");
      //$full_tb = $this->getRandTimeTable();
      $full_tb = str_replace("&nbsp;","",$full_tb);//处理空白
      preg_match_all('/<tr>(.+?)<\/tr>/', $full_tb, $array_str_cw);//得到上课周数数据
      //var_dump($array_str_cw);
      $course_time = array();
      foreach ($array_str_cw[1] as $str_cw) {
        if(!strstr($str_cw,"～"))continue;//如果没有周数范围则跳过
        preg_match_all('/>(.+?)<\/td>/', $str_cw, $cw_name);
        preg_match_all('/(\d+)～(\d+)/', $str_cw, $cw_week_range);
        //var_dump($cw_week_range);
        $course_name = $this->cleanBlank($cw_name[1][0]);
        $week_range = array();
        //var_dump($week_range);
        for($i=0;$i<count($cw_week_range[0]);$i++){
          //array_push($week_range,array($cw_week_range[1][$i],$cw_week_range[2][$i]));
          $week_range[] = array($cw_week_range[1][$i],$cw_week_range[2][$i]);
        }
        //var_dump($week_range);
        array_push($course_time,array($course_name,$week_range));
      }
      //var_dump($course_time);
      //初始化课程数据
      preg_match_all('/rowspan=(\d+) id="(\d+)">(\S+)<\/td>/', $full_tb, $m2);
      $arr_course = array();
      for($i=0;$i<count($m2[1]);$i++){
        $count = $m2[1][$i];
        $week = $m2[2][$i]%10%7;
        $section = intval($m2[2][$i]/10);
        $content = preg_split('/<br>/',$m2[3][$i]);
        //$b_sec = $section;$e_sec = $section + $count - 1;
        //$course_name='';$sd_week=0;$b_week=0;$e_week=0;$location='';//初始化

        for($j=0;$j<count($content);$j++){
          $b_sec = $section;$e_sec = $section + $count - 1;
          $course_name='';$sd_week=0;$b_week=0;$e_week=0;$location='';//初始化

          if(empty($content[$j]))continue;
          //if($this->is_course_name($content[$j]))echo '*';
          //echo " $content[$j]<br>";
          if($this->is_loc($content[$j])){

            $location = $this->getLoc($content[$j]);//获取地点
            $sd_week = $this->is_sdweek($content[$j]);//获取单双周
            $course_name = $this->cleanBlank($content[$j-1]);//获取课程名
            //
            $last_content='';$next_content='';$next2_content='';
            if($j-1>=0)$last_content = $content[$j-1];
            if($j+1<count($content))$next_content = $content[$j+1];
            if($j+2<count($content))$next2_content = $content[$j+2];

            if($this->is_course_name($next_content)){
              //本次完成
            }else if($this->is_section($next_content)){
              //设置节数
              $secRange = $this->getRange($next_content);
              $b_sec = intval($secRange[0]);
              $e_sec = intval($secRange[1]);
              if($this->is_week($next2_content)){
                //设置周数
                $weekRange = $this->getRange($next2_content);
                $b_week=intval($weekRange[0]);
                $e_week=intval($weekRange[1]);
              }
            }else if($this->is_week($next_content)){
              $weekRange = $this->getRange($next_content);
              $b_week=intval($weekRange[0]);
              $e_week=intval($weekRange[1]);
            }

            //echo $course_name .' '. $b_week . '-' .$e_week . '<br>';

              //补全上课周数
            if($b_week==0){
              $matched=false;
              foreach ($course_time as $ct) {
                 if($ct[0]==$course_name){
                   $matched=true;
                   for($k=0;$k<count($ct[1]);$k++){
                     $b_week = intval($ct[1][$k][0]);
                     $e_week = intval($ct[1][$k][1]);
                     $course = array('course_name'=>  $course_name ,'week'=>$week ,'b_sec' => $b_sec,'e_sec'=>$e_sec,'location'=>$location,'sd_week'=>$sd_week,'week_b'=>$b_week,'e_week'=>$e_week);
                     array_push($arr_course,$course);
                    $sql ="INSERT INTO timetable (`stu_id`, `course_name`, `week`, `b_sec`, `e_sec`, `location`, `sd_week`, `b_week`, `e_week`) VALUES ('$this->stu_id', '$course_name', '$week', '$b_sec', '$e_sec', '$location', '$sd_week', '$b_week', '$e_week')";
                     $db->SQL($sql);
                   }
                  break;
                }
              }

              if(!$matched){
                $course = array('course_name'=>  $course_name ,'week'=>$week ,'b_sec' => $b_sec,'e_sec'=>$e_sec,'location'=>$location,'sd_week'=>$sd_week,'week_b'=>$b_week,'e_week'=>$e_week);
                array_push($arr_course,$course);
                $sql ="INSERT INTO timetable (`stu_id`, `course_name`, `week`, `b_sec`, `e_sec`, `location`, `sd_week`, `b_week`, `e_week`) VALUES ('$this->stu_id', '$course_name', '$week', '$b_sec', '$e_sec', '$location', '$sd_week', '$b_week', '$e_week')";
                $db->SQL($sql);
              }

            }else{
              $course = array('course_name'=>  $course_name ,'week'=>$week ,'b_sec' => $b_sec,'e_sec'=>$e_sec,'location'=>$location,'sd_week'=>$sd_week,'week_b'=>$b_week,'e_week'=>$e_week);
              array_push($arr_course,$course);
              $sql ="INSERT INTO timetable (`stu_id`, `course_name`, `week`, `b_sec`, `e_sec`, `location`, `sd_week`, `b_week`, `e_week`) VALUES ('$this->stu_id', '$course_name', '$week', '$b_sec', '$e_sec', '$location', '$sd_week', '$b_week', '$e_week')";
              $db->SQL($sql);
            }

        }

      }



      }
      //var_dump($arr_course);
    }


    public function cleanBlank($str){
      return preg_replace("/\s/", "", $str);
    }
    public function isBracket($str){
      return preg_match('/[\[\(].{3,}?[\]\)]/', $str);
    }

    public function is_sdweek($str){
      if(strstr($str,"[单]"))return 1;else if(strstr($str,"[双]"))return 2; else return 0;
    }

    public function is_section($str){
      return preg_match('/节\]/', $str);
    }
    public function is_loc($str){
      return preg_match('/\[.+?\]/', $str)&&!$this->is_section($str);

    }

    public function getLoc($str){
      preg_match_all('/\[(.+?)\]/',$str, $arr);
      if(strlen($arr[1][0])>3)return $arr[1][0];else return $arr[1][1];
    }
    public function is_week($str){
      return preg_match('/周\)/', $str);
    }

    public function is_course_name($str){
      return !empty($str)&&!$this->isBracket($str);
    }
    public function getRange($str){
      preg_match_all('/(\d+)[-~～](\d+)/',$str, $m);
      $num = count($m[1]);
      $arr = array(intval($m[1][$num-1]),intval($m[2][$num-1]));
      return $arr;
    }
}
?>
