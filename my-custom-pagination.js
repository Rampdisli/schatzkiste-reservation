jQuery(document).ready(function($) {
    $('body').on('click', '.woocommerce-pagination a', function(e) {
        e.preventDefault();
        console.log('scroll');
        //var url = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $('body').offset().top +200
        }, 700);
        //window.history.pushState('', '', url);
        //window.location.reload();
    });
});
