jQuery(document).ready(function($) {
    $('body').on('click', '.woocommerce-pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $('body').offset().top
        }, 500);
        window.history.pushState('', '', url);
        window.location.reload();
    });
});
