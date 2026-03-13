jQuery(document).ready(function($) {
    $('.header__mobile').click(function() {
        $('.mobnav').fadeIn(200);
        $('.mobnav__header').fadeIn(200);
        $('body').css({
            'overflow-y': 'hidden'
        });
    });
    $('.mobnav__header .icon-close').click(function() {
        $('.mobnav').fadeOut(200);
        $('.mobnav__header').fadeOut(200);
        $('body').css({
            'overflow-y': 'auto'
        });
    });
    $('<span class="arrow"></span>').appendTo('.mobile-navigation>li.menu-item-has-children');
    $('.mobile-navigation>li.menu-item-has-children .arrow').on("click", function() {
        $(this).parent().find("ul.sub-menu").stop().slideToggle();
        $(this).parent().find(".arrow").toggleClass('arrow--open');
    });
});