function galleryRun() {
    var cols = 4;
    //css modify
    $('.slider-gallery').fadeIn(1000);
    var margin = 5;
    var width = ($('.slider-gallery .sg-wrapper').innerWidth() - (cols - 1) * margin) / cols;
    var height = width * 0.75;
    $('.slider-gallery .slide').css({'width': width + 'px', 'height': height + 'px', 'margin-left': margin + 'px'});
    $('.slider-gallery .slide').each(function (inx, item) {
        $(item).css({'background-image': 'url(' + $(item).children('.slide-frame').children('img').attr('src') + ')'});
    });
    $('.slider-gallery .sg-nav a').css('bottom', (height * 0.4) + 'px');
    // gallery pagination
    var pages = $('.slider-gallery .sg-pages').attr('data-id');
    var current = jsPagination(window.location.hash.replace("#page", ""), pages);//current js page

    $('.slider-gallery .sg-pages a').click(function () {
        jsPagination(this.hash.replace("#page", ""), pages);
        return false;
    })
    $('.slider-next').click(function (current) {
        var num = parseInt($('#sg-page-marker').attr('data-id')) + 1;
        if (num > pages)
            num = 1;
        jsPagination(num, pages);
        return false;
    })
    $('.slider-prev').click(function (current) {
        var num = parseInt($('#sg-page-marker').attr('data-id')) - 1;
        if (num == 0)
            num = pages;
        jsPagination(num, pages);
        return false;
    })
    function jsPagination(id, pages) {
        var go = parseInt(id);//current js page
        if (!go)
            go = 1;
        //set marker
        $('#sg-page-marker').attr('data-id', go);
        //modify css
        $('.slider-gallery .page' + go).fadeIn();
        $('.slider-gallery .page' + go + ':first').css('margin-left', '0');
        $('.slider-gallery #page' + go).attr('class', 'active');
        for (var i = 1; i <= pages; i++) {
            if (i != go) {
                $('.slider-gallery #page' + i).attr('class', '');
                $('.slider-gallery .page' + i).css({'display': 'none'});
            }
        }
        return go;
    }
    $(".slider-gallery .photo").hover(
        function () {
            $(this).find('span.imagetext').slideDown('fast');
        },
        function () {
            $(this).find('span.imagetext').slideUp('fast');
        }
    );
}