<?php
//error_reporting(E_ALL ^ E_NOTICE); 
//ini_set('display_errors', '1');
class DB{
  protected $conn;
  protected $result;
  protected $insert_id;
  public $affected_rows; //影响行数
  public $num_rows;      //结果行数

  public function __construct(){
    global $config;

    $this->conn = new mysqli($config['mysql_host'], $config['mysql_username'], $config['mysql_password'],$config['mysql_database']);

    $this->query("set names utf8");
  }

  public function query($sql){
    //查询并返回结果
    $this->result = $this->conn->query($sql);
    $this->insert_id = $this->conn->insert_id;
    $this->affected_rows = $this->conn->affected_rows;
    if(isset($this->result->num_rows)){
      $this->num_rows = $this->result->num_rows;
    }else $this->num_rows = 0;

    return $this->result;
  }

  public function array_result($res=null){
    //将查询结果转换为数组
    if($res == null)$res = $this->result;
    $data = array();
    while($row = $res->fetch_assoc()){
      $data[] = $row;
    }
    return $data;
  }

  public function get($table,$condition='1'){
    //查询指定表并返回结果
    return $this->query("SELECT * FROM `$table` WHERE $condition");
  }

  public function insert($table,$data){
    //向指定表插入数据，数据源为数组
    $keys_str = '';
    $values_str = '';
    $keys = array_keys($data);
    $values = array_values($data);
    foreach ($keys as $key) {
      $keys_str .= "`$key`";
      if($key != end($keys))$keys_str.=',';
    }
    foreach ($values as $value) {
      $values_str .= "'$value'";
      if($value != end($values))$values_str.=',';
    }
    return $this->query("INSERT INTO `$table` ($keys_str) VALUES ($values_str)");
  }

  public function escape($value){
    //数据过滤防注入
    if (get_magic_quotes_gpc())
    {
      $value = stripslashes($value);
      $value = mysql_real_escape_string($value);
    }
    return $value;
  }


  public function json($res=null){
    //格式化为json
    if($res == null)$res = $this->result;
    $array_res = $this->array_result($res);
    return json_encode($array_res);
  }


  public function set($table,$data,$condition){
    //更新数据
    $set_str = '';
    foreach ($data as $key => $value) {
      $set_str .= "`$key`='$value'";
      if($value != end($data))$set_str .= ',';
    }
    return $this->query("UPDATE `$table` SET $set_str WHERE $condition");
  }

  public function exist($table,$condition){
    //查询是否存在
    $res = $this->query("SELECT * FROM `$table` WHERE $condition");
    return $this->num_rows>0;
  }

  public function del($table,$condition='0'){
    //删除符合条件行
    $res =  $this->conn->query("DELETE FROM `$table` WHERE $condition");
    return $this->affected_rows>0;
  }

  public function value($table,$column,$condition='1',$value=null){
    //查询指定值/设置指定值
    if($value==null){
      $this->query("SELECT * FROM `$table` WHERE $condition");
      $row = $this->result->fetch_assoc();
      return $row["$column"];
    }else{
      $this->query("UPDATE `$table` SET `$column`='$value' WHERE $condition");
      return $this->affected_rows>0;
    }
    
  }

  // public function create($table,$type,$length=0,$null=false,$default=null,$attribute=null,$other=''){
  //   //建表
  //   $sql = "CREATE TABLE `$table` (";
    
  //   //CREATE TABLE `test`.`tb` ( `id` INT NOT NULL AUTO_INCREMENT , `str` VARCHAR(10) NULL , `ttt` VARCHAR(5) NOT NULL DEFAULT 'abc' , PRIMARY KEY (`id`)) ENGINE = InnoDB;
  // }

}
//------------DB Class------------
//Version:1.1
//By:Blue(zzx094@gmail.com)
//--------------------------------
