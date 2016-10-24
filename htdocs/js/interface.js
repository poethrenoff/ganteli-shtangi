$(document).ready(function(){
    $('#up-arrow').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 600);
    });   
});

$(document).on('scroll', function() {
    var doc = document.documentElement;
    var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

    if (top > 500) {
        $('#up-arrow').addClass('show');
    } else {
        $('#up-arrow').removeClass('show');
    }
});

function buyItem(id){
    //$.get('/cart/add/' + id + '/',function (response){
    //    $(".basket").html(response);
            
        var image = $("#" + id + " img:visible");
        var cart = $("a.cart");
        
        if(image && cart){
            image
            .clone()  
            .css({
                'position' : 'absolute', 
                'z-index' : '5000',
                'top': image.offset().top,
                'left': image.offset().left,
                'width': image.width(),
                'opacity': 0.7
            })
            .appendTo($('body'))
            .animate({
                opacity: 0.1,  
                top: cart.offset().top, 
                left: cart.offset().left,
                width: 50 
             }, 700, function() {  
                $(this).remove();  
            });
        }
    //});
}