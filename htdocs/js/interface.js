$(document).ready(function(){
    $('#up-arrow').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 600);
    });   
});

// SCROLL TO TOP
$(document).on('scroll', function() {
    var doc = document.documentElement;
    var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

    if (top > 500) {
        $('#up-arrow').addClass('show');
    } else {
        $('#up-arrow').removeClass('show');
    }
});
