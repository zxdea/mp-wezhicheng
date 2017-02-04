<?php
require_once('./FaceScore.php');
require_once('../userManager.php');
$openid = $_GET['openid'];
$user = new userManager(NULL,NULL,$openid);
if(!$user->isBinded()){
  header("location: ../open_bind.php?openid=$openid&jump=face/?openid=$openid&msg=登陆查看学籍照颜值评分"); 
}
$img_url = "http://jwb.fdzcxy.com/xszp/{$user->stu}.jpg";
$FaceScore = new FaceScore();
$data = $FaceScore->getScore($img_url);
?>

<!DOCTYPE html>
<html lang="zh-cn">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>至诚颜值评分</title>
    <style>
      body {
        background-image: url(img/8687012_155336088342_2.jpg);
        margin: 0;
        padding: 0;
      }
      #info{
        position:fixed;
        bottom:0;
        width: 100%;
        background: #FFCC00;
        opacity: 0.75;
        text-align: center;
      }
    </style>
    <link rel="stylesheet" href="../style/weui.min.css"/>
    <link rel="stylesheet" href="../style/custom.css"/>
  </head>
  <body>
  <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script src="http://www.yun-li.com/wx/js.config.php"></script>
  <script>
  wx.ready(function(){
    wx.showAllNonBaseMenuItem();

    wx.onMenuShareTimeline({
      title: '你的学籍照有多丑？我得到<?php echo $data['score']; ?>分，敢不敢来拼颜值？', // 分享标题
      link: '', // 分享链接
      imgUrl: '<?php echo $data['img_url']; ?>', // 分享图标
      success: function () { 
          // 用户确认分享后执行的回调函数
          //document.getElementById('des').style.display = '';
          //document.getElementById('share').style.display = 'none';
      },
      cancel: function () { 
          // 用户取消分享后执行的回调函数
      }
    });

    wx.onMenuShareAppMessage({
      title: '你的学籍照有多丑？我得到<?php echo $data['score']; ?>分，敢不敢来拼颜值？', // 分享标题
      desc: '<?php echo $data['text']; ?>', // 分享描述
      link: '', // 分享链接
      imgUrl: '<?php echo $data['img_url']; ?>', // 分享图标
      type: '', // 分享类型,music、video或link，不填默认为link
      dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
      success: function () { 
          // 用户确认分享后执行的回调函数
          //document.getElementById('des').style.display = '';
          //document.getElementById('share').style.display = 'none';
      },
      cancel: function () { 
          // 用户取消分享后执行的回调函数
      }
    });
  });
  </script>


    <h1 style="text-align:center;color:#FFFFFF;margin:20px;">学籍照颜值评分</h1>
    <div id="share" style="text-align:center;position:fixed;top:0;width:100%;background:#000000;opacity:0.5;color:#FFFFFF;padding:5px;<?php echo empty($_GET['from'])?'':'display:none' ?>">分享给朋友一起拼颜值吧↗</div>
    <div style="text-align:center">
      <img src="<?php echo $data['img_url']; ?>" width="75%"/>
    </div>
    <div id="info">
      <h3 id="des" style="padding:5px;margin-bottom:15px;"><?php echo $data['text']; ?></h3>
      <a><button style="width:95%;height:50px;margin:5px;border-radius:5px;<?php echo empty($_GET['from'])?'display:none':'' ?>" onclick="javascript:document.getElementById('qrcode').style.display = ''">我也要测</button></a>
      <!-- <a href="../index.html"><img src="../img/wx_zs_logo.png" height="25" /></a> -->
    </div>

        <div class="weui_dialog_alert" style="display:none" id="qrcode">
    <div class="weui_mask"></div>
    <div class="weui_dialog">
        <div class="weui_dialog_hd"><strong class="weui_dialog_title">关注微至诚查看颜值评分</strong></div>
        <div class="weui_dialog_bd">
          <img src="../img/qrcode.jpg" width="100%" />
          <div style="color:#CCC">长按二维码关注</div>
        </div>
        <div class="weui_dialog_ft">

        </div>
    </div>
</div>
  </body>
</html>
