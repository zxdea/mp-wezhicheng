<?php
include_once('config.inc.php');
include_once('safe.inc.php');
include_once('db.php');
//include_once('db1_2.php');
include_once('userManager.php');
include_once('eas.php');
include_once('mis/mis.class.php');
include_once('utils.php');
#sy_openid:oZE5cwcdmRloyxBjCzMJcoBoqvAU
$jump_dalay = 0;
$jump = $_REQUEST['jump'];
$msg = $_REQUEST['msg'];
$db = new DB();

$openid = empty($_REQUEST['openid'])?$_REQUEST['wx_id']:$_REQUEST['openid'];
$wx_id = $openid;

function refererCheck(){
    //来源检测
    return strstr($_SERVER["HTTP_REFERER"],$_SERVER['SERVER_NAME']);
}

if(refererCheck()&&isset($_POST['stu_id'])&&isset($_POST['stu_pw'])){
    $uid = $_REQUEST['stu_id'];
    $pwd = $_REQUEST['stu_pw'];
    
    
    //if(empty($jump))$jump = $_SERVER["HTTP_REFERER"];

    if(empty($openid))$openid=md5(uniqid() . mt_rand(1,1000000));

    $pre_openid = $db->getValue('user','wx_id',"stu_id='$uid'");
    if(strlen($pre_openid)==28&&strlen($openid)!=28){
        $openid = $pre_openid;
    }
    
    header("openid:".$openid);
    
    if(false){
        //
    }else{
        $eas = new EAS($uid ,$pwd);
        $login = $eas->login();

        if($login==1)
        {
            $user = new userManager($uid,$pwd,$openid);
            $bind = $user->bindID();
            $eas->getUserInfo();
            //get mis
            $mis = new Mis();
            $login = $mis->login($uid,$uid);
            if(!$login)
                $login = $mis->login($uid,$pwd);
            if($login == 1){
                $info = $mis->getInfo();
                $user->updateUserInfo('phone',$info['phone']);
                $user->updateUserInfo('mis_id',$info['mis_id']);
                $user->updateUserInfo('phone',$info['phone']);
                $user->updateUserInfo('qq',$info['qq']);
                $user->updateUserInfo('email',$info['email']);
            }
            //
            ulog($openid,'bind','绑定成功');
            $view = Msg("绑定成功","{openid:$openid}","success");
            header("openid:$openid");
            setcookie("openid", $openid, time()+1000);
            setcookie("success", "true", time()+1000);
            $jump_dalay = 1;
        }else if($login == -1){
            ulog($openid,'bind','验证码识别失败');
            $view = Msg("绑定失败","请检查学号密码是否正确","warn");
            $jump="?openid=".$openid."&id=".$uid;
            $jump_dalay = 1;
            setcookie("success", "false", time()+1000);
        }else if($login == -2){
            ulog($openid,'bind','密码错误');
            $view = Msg("绑定失败","请检查学号密码是否正确","warn");
            $jump="?openid=".$openid."&id=".$uid;
            $jump_dalay = 1;
            setcookie("success", "false", time()+1000);
        }
    }    
}


$bind_form =<<<HTML
           <div class="hd">
                <h1 class="page_title">教务登陆</h1>
            </div>
            <form action="open_bind.php" method="post">
            <div class="weui_cells weui_cells_form">
            
            <div class="weui_cell">
                <div class="weui_cell_hd"><label class="weui_label">学号</label></div>
                <div class="weui_cell_bd weui_cell_primary">
                    <input name="stu_id" class="weui_input" type="tel" placeholder="请输入学号" value="{$_REQUEST['id']}" required>
                </div>
            </div>
            <div class="weui_cell">
                <div class="weui_cell_hd"><label class="weui_label">密码</label></div>
                <div class="weui_cell_bd weui_cell_primary">
                    <input name="stu_pw" class="weui_input" type="password" placeholder="请输入密码" value="{$_REQUEST['pw']}" required>
                </div>
            </div>
            </div>
            
            <div class="weui_cells weui_cells_checkbox">
        <label class="weui_cell weui_check_label">
            <div class="weui_cell_hd">
                <input type="checkbox" class="weui_check" checked="checked" required>
                <i class="weui_icon_checked"></i>
            </div>
            <div class="weui_cell_bd weui_cell_primary">
                <p>接受 <a href="#" target="_blank">用户协议</a></p>
            </div>
        </label>

    </div>

            <div class="weui_btn_area">
                <button type="submit" class="weui_btn weui_btn_primary" href="javascript:" id="showTooltips">登陆</button>
            </div>

            <input type="hidden" name="jump" value="{$_REQUEST['jump']}">
            <input type="hidden" name="openid" value="{$_REQUEST['openid']}">
            </form>

            <div class="weui_extra_area">
                <p style="color:$808080;margin-bottom:10px;">{$msg}</p>
            </div>
HTML;

function Jump($url,$time){
    $time = $time * 1000;
    $js = "<script>setTimeout(\"document.location='$url'\",$time);</script>";
    if($time>0&&!empty($url))return $js;
}

function Msg($title,$des,$type){
    global $jump;
    $jump_des = '';
    if(!empty($jump)){
        $jump_des = '<a href="'. $jump .'">' . '正在跳转...'. '</a>';
    }
	$html=<<<HTML
	<div class="weui_msg">
    <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_{$type}"></i></div>
    <div class="weui_text_area">
        <h2 class="weui_msg_title">{$title}</h2>
        <p class="weui_msg_desc">{$des}</p>
        <p class="weui_msg_desc">{$jump_des}</p>
    </div>
    <div class="weui_opr_area">
        <p class="weui_btn_area">
        </p>
    </div>
    <div class="weui_btn_area">
        <button type="submit" class="weui_btn weui_btn_plain_default" id="closeWindow">关闭窗口</button>
    </div>
    <div class="weui_extra_area">
        <a href=""></a>
    </div>
	</div>
HTML;
    return $html;
}

if(empty($view))$view = $bind_form;

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <title> </title>
        <link rel="stylesheet" href="style/weui.min.css"/>
        <link rel="stylesheet" href="style/custom.css"/>
    </head>
    <body>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="http://www.yun-li.com/wx/js.config.php"></script>
    <script>
    wx.ready(function(){
        wx.hideAllNonBaseMenuItem();
        document.querySelector('#closeWindow').onclick = function () {
            wx.closeWindow();
        };
    });
    </script>
    <?php echo Jump($jump,$jump_dalay);?> 
    <?php echo $view;?>
            
        <div class="weui_extra_area">
            <p></p>
        </div>
    <?php //echo Msg('消息','这是消息','success');?>
    <!--By:Blue zzx094@gmail.com-->
    </body>
</html>

