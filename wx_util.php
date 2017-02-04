<?php
class wx_util{
	private $appid;
	private $secret;
	private $access_token;

	public function __construct($appid,$secret){
	   $this->appid = $appid;
	   $this->secret = $secret;
  	}

  	private function post($url,$data){
  		$curl = curl_init();
  		curl_setopt($curl, CURLOPT_URL, $url);
  		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  		curl_setopt($curl, CURLOPT_POST,true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
  		$result = curl_exec($curl);
  		curl_close($curl);
  		return $result;
  	}

  	public function get_access_token(){
  		$res = $this->post("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->secret}",'');
  		$this->access_token = json_decode($res,true)['access_token'];
  		return $this->access_token;
  	}

  	public function send_message($openid,$msg){
  		$data = '{"touser": "'. $openid . '", "msgtype": "text", "text": {"content": "' . $msg .'"}}';
  		$res = $this->post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$this->access_token",$data);
  		$state =  json_decode($res,true)['errmsg']=='ok';
  		file_put_contents("wx_send_log.txt", date("Y-m-d h:i:s") . "|" . $openid . '|' . $msg . '|' .(string)$state ."\r\n", FILE_APPEND);
  		return $state;
  	}
}