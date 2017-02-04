<?php
//Code By Safe3
function customError($errno, $errstr, $errfile, $errline)
{
 echo "<b>Error number:</b> [$errno],error on line $errline in $errfile<br />";
 die();
}

set_error_handler("customError",E_ERROR);
$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq){

if(is_array($StrFiltValue))
{
    $StrFiltValue=implode($StrFiltValue);
}
if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
        slog("IP: ".$_SERVER["REMOTE_ADDR"]."\tTIME: ".strftime("%Y-%m-%d %H:%M:%S")."\tURL:".$_SERVER["PHP_SELF"]."\tMETHOD: ".$_SERVER["REQUEST_METHOD"]."\tKEY: ".$StrFiltKey."\tVALUE: ".$StrFiltValue);
        //error_log("����IP: ".$_SERVER["REMOTE_ADDR"]."������ʱ��: ".strftime("%Y-%m-%d %H:%M:%S")."������ҳ��:".$_SERVER["PHP_SELF"]."���ύ��ʽ: ".$_SERVER["REQUEST_METHOD"]."���ύ����: ".$StrFiltKey."���ύ����: ".$StrFiltValue."\n",3, "360safe-".date("Y-m-d", time()).".log");
        //print "notice:Illegal operation!";
        header("location: \?DO_NOT_ATTACK");
        exit();
}
}
//$ArrPGC=array_merge($_GET,$_POST,$_COOKIE);
foreach($_GET as $key=>$value){
	StopAttack($key,$value,$getfilter);
}
foreach($_POST as $key=>$value){
	StopAttack($key,$value,$postfilter);
}
foreach($_COOKIE as $key=>$value){
	StopAttack($key,$value,$cookiefilter);
}
function slog($logs)
{
  file_put_contents("safe_log.txt", "$logs\r\n", FILE_APPEND);
}
header("CODE:BY BLUE");
header("EMAIL:zzx094@gmail.com");

?>
