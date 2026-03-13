$(function() {
$('#pay_slider').owlCarousel({
    rtl:false,
    loop:true,
    nav:true,
    autoplay:true,
    autoplayTimeout:5000,
    smartSpeed: 500,
    slideSpeed : 3000,
    autoplayHoverPause:true,
        goToFirstSpeed: 100,
	responsive:{
        0:{items:1},
        479:{items:1},
        650:{items:1},
        766:{items:1},
	1100:{items:1},
	1280:{items:1}
    }

}) 


$('#product').owlCarousel({
    rtl:false,
    loop:true,
    nav:true,
    autoplay:true,
    autoplayTimeout:3000,
    smartSpeed: 3000,
    slideSpeed : 3000,
    autoplayHoverPause:true,
	responsive:{
        0:{items:1},
        479:{items:1},
        766:{items:3},
	1100:{items:4}, 
	1280:{items:4}
    }

})







$('#deal_slider').owlCarousel({
    rtl:false,
    loop:true,
    nav:true,
    autoplay:true,
    autoplayTimeout:3000,
    smartSpeed: 3000,
    slideSpeed : 3000,
    autoplayHoverPause:true,
	responsive:{
        0:{items:1},
        479:{items:1},
        766:{items:1},
	1100:{items:1}, 
	1280:{items:1}
    }

})

$('#client_says').owlCarousel({
    rtl:false,
    loop:true,
    nav:true,
    autoplay:true,
    autoplayTimeout:3000,
    smartSpeed: 3000,
    slideSpeed : 3000,
    autoplayHoverPause:true,
	responsive:{
        0:{items:1},
        479:{items:1},
        766:{items:2},
	1100:{items:3}, 
	1280:{items:3}
    }

})

		       



$(window).scroll(function () {
	    if ($(this).scrollTop() > 10) {
	        $('#toTop').fadeIn();
	    } else {
	        $('#toTop').fadeOut();
	    }
	});

	$('#toTop').click(function () { 
	    $('body,html').animate({ scrollTop: 0 }, 800);
	});



  });
  
//  $(function () {
//    var top = 1;
//    $(window).scroll(function (event) {
//        // what the y position of the scroll is
//        var y = $(this).scrollTop();
//
//        // whether that's below the form
//        if (y >= top) {
//            // if so, ad the fixed class
//            $('.header, .body_top_fixed').addClass('fixed');
//        } else {
//            // otherwise remove it
//            $('.header, .body_top_fixed').removeClass('fixed');
//        }
//    });
//});
  
	
                              
//      $(window).scroll(function() {
//	$('section').each(function(){
//		var offset = $(this).offset();
//		if(offset.top >= 0){
//			$(this).css('background-attachment','fixed');
//		}
//	});
//	
//	$('.animation').each(function(){
//		var animation = $(this).attr( 'rel' );
//		var imagePos = $(this).offset().top;
//	
//		var topOfWindow = $(window).scrollTop();
//		if (imagePos < topOfWindow+700) {
//			$(this).addClass( animation );
//		}
//	});
//});                          

  





     