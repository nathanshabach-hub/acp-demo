<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo HTTP_PATH; ?>/img/favicon.ico" />
	<title>
		<?php echo isset($title_for_layout) ? $title_for_layout : SITE_TITLE; ?>
	</title>
	<?php //echo $this->Html->css('front/font-awesome.min.css'); ?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
		integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<?php echo $this->Html->css('front/bootstrap.min.css'); ?>
	<?php echo $this->Html->css('front/style_front.css'); ?>
	<?php echo $this->Html->css('front/responsive.css'); ?>
	<?php echo $this->Html->css('front/forms.css'); ?>
	<?php echo $this->Html->css('front/font-awesome.min.css'); ?>
	<?php echo $this->Html->script('front/jquery.min.js'); ?>
	<?php echo $this->Html->script('front/validate.js'); ?>
</head>

<body>
	<!-- Header Start -->
	<?php echo $this->element('header'); ?>
	<!-- Header End -->

	<!-- Middle content Start -->
	<?php echo $this->fetch('content'); ?>
	<!-- Middle content End -->

	<!-- Footer Start -->
	<?php echo $this->element('footer'); ?>
	<!-- Footer End -->

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
		crossorigin="anonymous"></script>

	<script type="text/javascript">
		window.onload = function () {
			setTimeout("hideSessionMessage()", 8000);
		};
		function hideSessionMessage() {
			$('.ersu_message').fadeOut("slow");
		}
	</script>
	<script>
		$(document).ready(function () {
			$(".fa-bars").click(function () {
				$(".sidebar").slideToggle();
			});
		});
	</script>

	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

	<style type="text/css">
		.select2 .selection {
			display: block;
			color: #248cc9 !important;
		}

		.select2-container {
			display: block !important;
			width: auto !important;
			margin-bottom: -24px;
		}

		.select2-container--default .select2-selection--single {
			display: block;
			border-radius: 10px;
			color: #248cc9 !important;
			border: solid 2px #000 !important;
			padding: 6px;
			height: 40px !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 26px;
			position: absolute;
			top: 6px;
			right: 6px;
			width: 20px;
		}

		.select2-container--default .select2-selection--multiple {
			background-color: white;
			border: 2px solid #000;
			border-radius: 10px;
			cursor: text;
		}
		
		#goTop {
		  position: fixed;
		  bottom: 40px;
		  right: 40px;
		  display: none;
		  background-color: #333;
		  color: white;
		  padding: 10px 15px;
		  border-radius: 5px;
		  text-decoration: none;
		  font-size: 16px;
		  z-index: 999;
		  transition: opacity 0.3s;
		}

		#goTop:hover {
		  background-color: #555;
		}
	</style>

<script>
  $(document).ready(function() {
    // Show/hide button on scroll
    $(window).scroll(function() {
      if ($(this).scrollTop() > 200) {
        $('#goTop').fadeIn();
      } else {
        $('#goTop').fadeOut();
      }
    });

    // Scroll to top on click
    $('#goTop').click(function(e) {
      e.preventDefault();
      $('html, body').animate({scrollTop: 0}, 600);
    });
  });
</script>
<a href="#" id="goTop" title="Go to top">↑ Top</a>

</body>

</html>