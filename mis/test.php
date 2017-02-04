<?php
include_once('mis.class.php');
$mis = new Mis();
$mis->login('211306416','2015090');
//var_dump($mis->isChecked());
var_dump($mis->getInfo());