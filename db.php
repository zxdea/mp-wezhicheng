<?php
  include_once('config.inc.php');
  include_once('safe.inc.php');
  class DB{
    protected $conn;
    public function __construct(){
      global $mysql_host, $mysql_port, $mysql_username, $mysql_password, $mysql_database;

   
      $this->conn = new mysqli($mysql_host, $mysql_username, $mysql_password,$mysql_database);

      $this->SQL("set names utf8");
    }

    public function SQL($sql){
      $res = $this->conn->query($sql);
      return $res;
    }

    public function getJson($sql){
      $data = array();
      $res = $this->SQL($sql);
      while($row = $res->fetch_assoc()){
        $data[] = $row;
      }
      return json_encode($data);
    }

    public function getValue($table,$column,$condition="1=1"){
      $sql ="SELECT * FROM `$table` WHERE $condition";
      $res = $this->conn->query($sql);
			$row = $res->fetch_assoc();
			if(!empty($row))return $row["$column"];else return NULL;
    }

    public function setValue($table,$content,$condition="1=1"){
        $sql ="UPDATE `$table` SET $content WHERE $condition";
        $res =  $this->conn->query($sql);
    }

    public function isExist($table,$condition="1=1"){
      $sql ="SELECT * FROM $table WHERE $condition";
      $res = $this->conn->query($sql);
      if($this->conn->affected_rows>0)return TRUE;else return FALSE;
    }

    public function addValue($table,$column,$value){
      $sql ="INSERT INTO $table($column) values ($value)";
      $res = $this->conn->query($sql);
    }

    public function getRow($table,$condition="1=1"){
      $sql ="SELECT * FROM `$table` WHERE $condition";
      $res = $this->conn->query($sql);
      $row = $res->fetch_assoc();
      return $row;
    }

    public function delRows($table,$condition="1=1"){
      $sql ="DELETE FROM `$table` WHERE $condition";
      $res =  $this->conn->query($sql);
      if($this->conn->affected_rows>0)return TRUE;else return FALSE;
    }

    public function getAffectedRows(){
      return $this->conn->affected_rows;
    }

    public function getNumRows($res){
      return $res->num_rows;
    }



  }

?>
