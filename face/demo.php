<?php
require_once './FaceScore.php';
//凤姐的图片
$img_url = 'http://localhost/zctool/face/152217547874783192.jpg';
$FaceScore = new FaceScore();
$data = $FaceScore->getScore($img_url);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo</title>
</head>
<body>
    <p>
        Score:<?php echo $data['score']; ?>
    </p>
    <p>
        Text:<?php echo $data['text']; ?>
    </p>
    <p>
        ImgUrl:<?php echo $data['img_url']; ?>
    </p>
    <p>
        <img width="550px" src="<?php echo $data['img_url']; ?>" alt="">
    </p>
</body>
</html>





