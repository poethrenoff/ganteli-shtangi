$(document).ready(function(){
    $('#up-arrow').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 600);
    });
    
    $('.vote .star').mouseenter(function(){
        $(this).parent().find('.star').removeClass('active');
        $(this).parent().find('.star:lt(' + $(this).attr('mark') + ')').addClass('active');
    }).click(function(){
        $.post('/product/vote/' + $(this).parent().data('id'), {
                action: 'vote', mark: $(this).attr('mark')
            }, function(data){
                var rating = Math.round(data.rating);
                $(this).parent().attr('rating', rating); $(this).parent().mouseleave();
            }, 'json');
    });
    $('.vote').mouseleave(function(){
        $(this).parent().find('.star').removeClass('active');
        $(this).parent().find('.star:lt(' + $(this).attr('rating') + ')').addClass('active');
    });
}).on('scroll', function() {
    var doc = document.documentElement;
    var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

    if (top > 500) {
        $('#up-arrow').addClass('show');
    } else {
        $('#up-arrow').removeClass('show');
    }
});
								
function setMark(mark) {
    $('.vote .star').removeClass('active');
    $('.vote .star:lt(' + mark + ')').addClass('active');
}

function buyItem(id, image_id){
    //$.get('/cart/add/' + id + '/',function (response){
    //    $(".basket").html(response);
            
        var image = $('[data-image-id=' + image_id + '] img:visible');
        var cart = $('a.cart');
        
        if(image.length && cart.length){
            image.clone().css({
                'position' : 'absolute', 
                'z-index' : '5000',
                'top': image.offset().top,
                'left': image.offset().left,
                'width': image.width(),
                'opacity': 0.7
            }).appendTo($('body')).animate({
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