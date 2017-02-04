<?php
include_once('config.inc.php');
require_once('safe.inc.php');
$conn = new mysqli("{$mysql_host}:{$mysql_port}", $mysql_username, $mysql_password,$mysql_database);
?>
