<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
      <meta name="description" content="">
      <meta name="author" content="">
      <link rel="shortcut icon" type="image/x-icon" href="<?php echo HTTP_PATH; ?>/img/favicon.ico">
      <title><?php echo isset($title_for_layout)?$title_for_layout:SITE_TITLE; ?></title>
      <!-- Chrome, Firefox OS and Opera -->
      <meta name="theme-color" content="#3f562a;">
      <!-- Windows Phone -->
      <meta name="msapplication-navbutton-color" content="#3f562a;">
      <!-- iOS Safari -->
      <meta name="apple-mobile-web-app-status-bar-style" content="#3f562a;">
      <!-- CSS -->
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
	  
	  <?php echo $this->Html->css('front/font-awesome.min.css'); ?>
	  <?php echo $this->Html->css('front/bootstrap.min.css'); ?>
	  <?php echo $this->Html->css('front/style_front.css'); ?>
	  <?php echo $this->Html->css('front/forms.css'); ?>
	  <?php echo $this->Html->css('front/responsive.css'); ?>
       <?php echo $this->Html->css('front/owl.theme.default.min.css'); ?>
       <?php echo $this->Html->css('front/owl.carousel.min.css'); ?>
	  
	  <link rel="stylesheet" href="<?php echo HTTP_PATH; ?>/webroot/js/front/mobile-menu/mobile-menu.css" type="text/css">
	  <link rel="stylesheet" href="<?php echo HTTP_PATH; ?>/webroot/js/front/slick/swiper-bundle.min.css" type="text/css">
      
      <!--<link rel="stylesheet" href="js/mobile-menu/mobile-menu.css" type="text/css">
      <link rel="stylesheet" href="js/slick/swiper-bundle.min.css" type="text/css">
	  -->
	  
      <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
      <?php echo $this->Html->script('front/jquery.min.js'); ?>
      <?php echo $this->Html->script('front/validate.js'); ?>
   </head>
   <body>
       
       <div class="maincontents">
      <!-- Header Start -->
      <?php echo $this->element('header'); ?>
      <!-- Header End -->
	  
      
	<?php echo $this->fetch('content'); ?>
       </div>
     
      <!--  Footer Section Start -->
      <?php echo $this->element('footer'); ?>
      <!-- JavaScript
         ================================================== -->
      <!-- Placed at the end of the document so the pages load faster -->
	  
	  
	  
	  
	  <?php echo $this->Html->script('front/popper.min.js'); ?>
	  <?php echo $this->Html->script('front/bootstrap.min.js'); ?>
	  <?php echo $this->Html->script('front/mobile-menu/mobile-menu.js'); ?>
	  <?php echo $this->Html->script('front/animation/gsap.min.js'); ?>
	  <?php echo $this->Html->script('front/animation/ScrollTrigger.min.js'); ?>
	  <?php echo $this->Html->script('front/animation/cstanimation.js'); ?>
	  <?php echo $this->Html->script('front/owl.carousel.js'); ?>
	  
	  <?php echo $this->Html->script('front/slick/swiper-bundle.min.js'); ?>
	  <?php //echo $this->Html->script('front/custom.js'); ?>
	  
	  
	  
	  <script type="text/javascript">
		window.onload = function() {
			setTimeout("hideSessionMessage()",8000);
		};
		function hideSessionMessage(){
			$('.ersu_message').fadeOut("slow");
		}
	</script>
	
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
	</style>
	 
	  
   </body>
</html>