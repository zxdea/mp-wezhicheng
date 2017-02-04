<?php
function stupid_pw_encrypt($id,$pw,$code){
    $code = strtolower($code);
    $pw_md5 = md5($pw);
    $new_pw = md5($pw_md5 . 'zcjw' . $code) . md5(substr($pw_md5,8,16) . 'zcjw' . $code) . md5($pw . $id) . '1';
    return $new_pw;
}

echo stupid_pw_encrypt($_GET['id'],$_GET['pw'],$_GET['code']);
//var_dump(substr('bff963d7fe0a3f916fb4ff68bdbc301d',8,16));