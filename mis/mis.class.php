<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
class Mis{
	private $cookie;
	private $header = true;
	private $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Maxthon)';
	private $server = 'http://210.34.51.164/';
	private $login = false;
	public $reslut;

	public function __construct(){
    	
	}

	private function post($url,$data=''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_HEADER,$this->header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  		curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
  		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
  		curl_setopt($ch,CURLOPT_USERAGENT,$this->agent);
		$res=curl_exec($ch);
		preg_match('/Set-Cookie: (PHPSESSID=.*);/iU',$res,$str);
		if(!$this->login)$this->cookie = $str[1];
		curl_close($ch);
		return $res;
	}

	public function login($user,$passwd){
		if(strlen($user)!=32){
			$user = md5($user);
			$passwd = md5($passwd);
		}
		$res = $this->post($this->server . "index.php?n=login&s=1001","user=$user&passwd=$passwd&");
		$state = (bool)strpos($res, 'true');
		if($state){
			$this->header = false;
			$this->login = true;
		}else $this->login = false;
		return $state;
	}

	public function checkin($id){
		$res = $this->post($this->server . "index.php?n=stuwork-dormcheck-checker-main&c=Checker&s=1004","i=$id");
		$this->reslut = $res;
		$state = (bool)strpos($res, '"result":3');
		return $state;
	}

	public function isChecked(){
		if($this->login == false)return false;
		$leave = true;
		$res = $this->post($this->server . "index.php?n=stuwork-dormcheck-record-student&c=dormcheckrecordstudent","");
		$date = date("Y-m-d");
		preg_match("/<td\s*>$date<\/td>\s*<td\s*>(.*?)<\/td>\s*<td\s*>(.*?)<\/td>/",$res,$m);
		//var_dump($m);
		if($m[1]=='未签'){
			if(empty($m[2])){
				return false;
			}else if($leave){
				return true;
			}else return false;
		}else return true;
	}

	public function getInfo(){
		$res = $this->post($this->server . "index.php?n=person-information-main&c=information");
		preg_match("/edtId\" value=\"(.*?)\"/",$res,$m);
		$mis_id = $m[1];
		preg_match("/edtCellPhone\" value=\"(.*?)\"/",$res,$m);
		$phone = $m[1];
		preg_match("/edtQQ\" value=\"(.*?)\"/",$res,$m);
		$qq = $m[1];
		preg_match("/edtEmail\" value=\"(.*?)\"/",$res,$m);
		$email = $m[1];
		$info = array(
				"mis_id"=>$mis_id,
				"phone"=>$phone,
				"qq"=>$qq,
				"email"=>$email
			);
		return $info;
	}
	//By:Blue
}
