<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo HTTP_PATH; ?>/img/favicon.ico" />
  <?php echo $this->Html->css('admin/bootstrap.min.css'); ?>
  <?php echo $this->Html->css('admin/AdminLTE.min.css'); ?>
  <?php echo $this->Html->script('jquery-2.1.0.min.js'); ?>
  <?php echo $this->Html->script('jquery.validate.js'); ?>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <?php echo $this->fetch('content'); ?>
</div>
</body>
</html>
