//For Sticky Header

jQuery(window).scroll(function() {
    var width = jQuery(window).width();
      if (jQuery(this).scrollTop() > 1){
        jQuery('header').addClass("sticky");
      }
      else{
        jQuery('header').removeClass("sticky");
      }
    });
    
//Tab Section
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();


//Recent Property Slider 
/* var swiper = new Swiper('.property-container', {
  slidesPerView: 3,
  spaceBetween: 30,
  loop:true,
  autoplay:true,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  breakpoints: {
    315: {
      slidesPerView:1,
    },
    499: {
      slidesPerView:1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  }
}); */

//Recent Property Slider 
var swiper = new Swiper('.property-container', {
  slidesPerView: 3,
  spaceBetween: 30,
  loop:true,
  autoplay:true,
  observer: true,
  observeParents: true,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  breakpoints: {
    315: {
      slidesPerView:1,
    },
    499: {
      slidesPerView:1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
  
});

//Recent Property Slider 
var swiper = new Swiper('.details-container', {
  slidesPerView: 3,
  spaceBetween: 30,
  loop:true,
  autoplay:true,
  observer: true,
  observeParents: true,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  breakpoints: {
    315: {
      slidesPerView:1,
    },
    499: {
      slidesPerView:1,
    },
    768: {
      slidesPerView: 1,
    },
    1024: {
      slidesPerView: 1,
    },
  },
  
});







// Testimonial
var swiper = new Swiper('.testimonial-container', {
  slidesPerView: 3,
  spaceBetween:45,
  loop:true,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  breakpoints: {
    315: {
      slidesPerView:1,
    },
    499: {
      slidesPerView:1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  }
});

// Brand
var swiper = new Swiper('.brand-container', {
  slidesPerView: 5,
  spaceBetween:20,
  loop:true,
  navigation: {
    nextEl: '.swiper-button-next-b',
    prevEl: '.swiper-button-prev-b',
  },
  breakpoints: {
    315: {
      slidesPerView:2,
      spaceBetween: 20,
    },
    499: {
      slidesPerView:2,
      spaceBetween: 20,
    },
    768: {
      slidesPerView: 4,
      spaceBetween: 20,
    },
    1024: {
      slidesPerView: 5,
      spaceBetween: 20,
    },
  }
});



//Drop-Down

function DropDown(el) {
  this.dd = el;
  this.placeholder = this.dd.children('span');
  this.opts = this.dd.find('ul.drop li');
  this.val = '';
  this.index = -1;
  this.initEvents();
}

DropDown.prototype = {
  initEvents: function () {
      var obj = this;
      obj.dd.on('hover', function (e) {
          e.preventDefault();
          e.stopPropagation();
          $(this).toggleClass('active');
      });
      obj.opts.on('click', function () {
          var opt = $(this);
          obj.val = opt.text();
          obj.index = opt.index();
          obj.placeholder.text(obj.val);
          opt.siblings().removeClass('selected');
          opt.filter(':contains("' + obj.val + '")').addClass('selected');
      }).change();
  },
  getValue: function () {
      return this.val;
  },
  getIndex: function () {
      return this.index;
  }
};

$(function () {
  // create new variable for each menu
  var dd1 = new DropDown($('#noble-gases'));
  // var dd2 = new DropDown($('#other-gases'));
  $(document).click(function () {
      // close menu on document click
      $('.wrap-drop').removeClass('active');
  });
});