$(document).ready(function () {
    $("#toBottom").click(function() {
        $('html, body').animate({
            scrollTop: $("#one-section").offset().top
        }, 1000);
    });
    $("#toBottom2").click(function() {
        $('html, body').animate({
            scrollTop: $("#one-section").offset().top
        }, 1000);
    });

    if(window.location.hash) {
        var hash = window.location.hash;

        $('html, body').animate({
            scrollTop: $(hash).offset().top
        }, 1500);
    }

});

