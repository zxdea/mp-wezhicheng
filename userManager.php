<?php
	include_once('config.inc.php');
	include_once('safe.inc.php');
	include_once('db.php');
	include_once('eas.php');
	include_once('gbk2utf8.php');
	class userManager{

		public $stu_id,$stu_pw,$wx_id;
		protected $conn;

		public function __construct($stu_id=NULL,$stu_pw=NULL,$wx_id=NULL){
			$this->stu_id = $stu_id;
			$this->stu_pw = $stu_pw;
			$this->wx_id = $wx_id;
			global $mysql_host, $mysql_port, $mysql_username, $mysql_password, $mysql_database;
			$this->conn = new mysqli($mysql_host, $mysql_username, $mysql_password,$mysql_database);
			$this->conn->query("set names utf8");
			
			$this->newUser();
			if(empty($this->stu_id))$this->getUserStuID();
			if(empty($this->stu_pw))$this->getUserPassword();
		}


		public function updateUserInfo($key,$value){
			$sql="UPDATE `user` SET $key='$value' WHERE stu_id='$this->stu_id'";
			$res = $this->conn->query($sql);
			return (bool)$res;
		}

		public function getUserInfo($key){
			$sql="SELECT $key FROM `user` where stu_id='$this->stu_id'";
			$res = $this->conn->query($sql);
			$row = $res->fetch_assoc();
			if(!empty($row))return $row["$key"];else return NULL;
		}

		public function getBindID($id){
			if(strlen($id)>9){
				//微信openid
				$res = $this->conn->query("SELECT * FROM `user` where wx_id='$this->wx_id'");
				$row = $res->fetch_assoc();
				if(!empty($row))return $row['stu_id'];else return NULL;
			}else{
				//学号
				$res =  $this->conn->query("SELECT * FROM `user` where stu_id='$id' and wx_id<>''");
				$row = $res->fetch_assoc();
				if(!empty($row))return $row['wx_id'];else return NULL;
			}
		}

		public function getUserStuID(){
				$sql ="SELECT stu_id FROM `user` where wx_id='$this->wx_id'";
				$res = $this->conn->query($sql);


				$row = $res->fetch_assoc();
				if($row){
					$this->stu_id=$row['stu_id'];
				return $row['stu_id'];
				}

				return NULL;

		}
		public function getUserPassword(){
				$sql="SELECT stu_pw FROM `user` where stu_id='$this->stu_id' or wx_id='$this->wx_id'";
				$res = $this->conn->query($sql);
				$row = $res->fetch_assoc();

			if($row){
				$this->stu_pw=$row['stu_pw'];
				return $row['stu_pw'];
			}
			return NULL;

		}

			public function sqlStatus(){
				if($this->conn->affected_rows>0)return true;else return FALSE;
			}

			public function bindID(){
				$sql = "UPDATE `user` SET wx_id='$this->wx_id',stu_id='$this->stu_id',stu_pw='$this->stu_pw' where stu_id='$this->stu_id'";
				$res =  $this->conn->query($sql);
				//获取课表
				$eas = new EAS($this->stu_id,$this->stu_pw);
				$eas->login();
				$eas->getTimeTable();
				//
				$user_num=$this->getUserNum();
				if($user_num%100==0){
					$db = new DB();
					$db->addValue("general","type,bind,content","'lucky_user','$this->stu_id','$user_num'");
				}
				//
				return (bool)$res;//$this->sqlStatus;
			}

			public function isBinded(){
				$sql = "SELECT * FROM `user` where wx_id='$this->wx_id' or (stu_id='$this->stu_id' and wx_id<>'')";
				$this->isWeekPwd();
				$res =  $this->conn->query($sql);
				return $this->sqlStatus();
				//if($res)return TRUE; else return FALSE;
			}

			public function isWeekPwd(){
				$week_pwds='$stu_id';
				$sql = "SELECT * FROM `user` where stu_pw='$week_pwds'";
				$res =  $this->conn->query($sql);
				if($this->sqlStatus())header("location: /bind.php");
			}

			public function unbindID(){
				$sql ="UPDATE `user` SET wx_id='' where stu_id='$this->stu_id'";
				$res =  $this->conn->query($sql);
			}

			public function userExist(){
				$sql ="SELECT * FROM `user` where stu_id='$this->stu_id'";
				$res =  $this->conn->query($sql);
				return $this->sqlStatus();
			}
			public function newUser(){
				if(!$this->userExist()&&strlen($this->stu_id)==9){
					$sql ="INSERT INTO user(stu_id) values ('$this->stu_id')";
					$res =  $this->conn->query($sql);
					return $this->sqlStatus();
				}
			}

			public function getUserNum(){
				$sql ="SELECT * FROM `user`";
				$res =  $this->conn->query($sql);
				$num = $res->num_rows;
				return $num;
			}
			public function view(){
				echo $this->stu_id ."," , $this->stu_pw.",".$this->wx_id;
			}
	}
?>
