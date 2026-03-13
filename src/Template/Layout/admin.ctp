<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo HTTP_PATH; ?>/img/favicon.ico" /> 
  <?php echo $this->Html->css('admin/bootstrap.min.css'); ?>
  <?php echo $this->Html->css('admin/AdminLTE.min.css'); ?>
  <?php echo $this->Html->css('admin/all-skins.min.css'); ?>
  <?php echo $this->Html->css('admin/admin.css'); ?>
  <?php echo $this->Html->script('jquery-2.1.0.min.js'); ?>
  <?php echo $this->Html->script('jquery.validate.js'); ?>
  <?php echo $this->Html->script('app.min.js'); ?>
  <?php echo $this->Html->script('listing.js'); ?>
  
  <?php echo $this->Html->script('timepicker/mdtimepicker.js'); ?>
	<?php echo $this->Html->css('timepicker/mdtimepicker.css'); ?>
  
  <script type="text/javascript" src="https://cdn.ywxi.net/js/1.js" async></script>
  
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
	<style type="text/css">
	.select2-container--default.select2-container--focus .select2-selection--multiple {
	border: solid #ccc 1px;
	outline: 0;
	}
	.select2-container--default .select2-selection--multiple {    
	border: 1px solid #d2d6de;
	border-radius: 0px;     
	}
	.select2-container--default.select2-container--focus .select2-selection--single {
	border: solid #ccc 1px;
	outline: 0;
	}
	.select2-container--default .select2-selection--single {    
	border: 1px solid #d2d6de;
	border-radius: 0px;
	height: 34px;
	padding: 6px 12px;
	}
	.select2-container .select2-selection--single .select2-selection__rendered {
	padding-left: 0px;
	}
	.select2-container--default .select2-selection--multiple .select2-selection__choice {

    color: #000;
}
	</style>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php echo $this->element('Admin/header'); ?>
    <?php echo $this->element('Admin/left_menu'); ?>
    <?php echo $this->fetch('content'); ?>
</div>
    <script type="text/javascript">
            window.onload = function() {
                setTimeout("hideSessionMessage()",30000);
            };
            function hideSessionMessage(){
                $('.ersu_message').fadeOut("slow");
            }
        </script> 
</body>
</html>
