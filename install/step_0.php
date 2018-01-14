<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $html_title;?></title>
<link href="css/install.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../Public/resource/common/js/jquery.js"></script>
<link href="../Public/resource/common/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../Public/resource/common/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="../Public/resource/common/js/jquery.mousewheel.js"></script>
</head>

<body>
<?php defined('IN_SHOPDA') || exit;?>
<?php echo $html_header;?>
<div class="main">
  <div class="text-box" id="text-box">
    <div class="license">
      <h1>大商城安装协议</h1>
      <p>
用户须知：本协议是您与大商城之间关于您安装使用大商城shopda.cn提供的大商城版电商系统及服务的法律协议。
未经授权禁止传播，复制，分发软件，ShopDa大商城版权所有，侵权必究！</p>
      <p></p>
      <p align="right">ShopDa大商城技术中心</p>
    </div>
  </div>
  <div class="btn-box"><a href="index.php?step=1" class="btn btn-primary">同意协议进入安装</a><a href="javascript:window.close()" class="btn">不同意</a></div>
</div>
<div class="footer">
 <h6><a href="http://www.shopda.cn" target="_blank">程序来源于 bbs.shopda.cn</a></h6>
</div>
<script type="text/javascript">
$(document).ready(function(){
	//自定义滚定条
	$('#text-box').perfectScrollbar();
});
</script>
</body>
</html>
