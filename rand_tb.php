
<?php
  include_once('db.php');
  $db = new DB();
  $res = $db->SQL("select * from general where type='full_tb' and content<>'' order by rand() limit 1");
  while($row = $res->fetch_assoc()){
    echo $row['bind'] ."<br/>". $row['content'] . "<br/>";
  }
?>
