<?php
function gbk2utf8($value){
  // $value_1= $value;
  // $value_2   =   @iconv( "gb2312", "utf-8//IGNORE",$value_1);
  // $value_3   =   @iconv( "utf-8", "gb2312//IGNORE",$value_2);
  // if   (strlen($value_1)   ==   strlen($value_3))
  // {
  //  return   $value_2;
  // }else
  // {
  //  return   $value_1;
  // }
    return @mb_convert_encoding($value, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
 }

 ?>
