

var purchase_type_online = $('.purchase_type_online');
var purchase_type_presence = $('.purchase_type_presence');

purchase_type_online.click(function () {
    if ($(this).is(':checked')) {
        $('.purchase_type_box').css({'display': 'block'});
        $('.presence_type_box').css({'display': 'none'});
    }
});

if (purchase_type_online.is(':checked')) {
    $('.purchase_type_box').css({'display': 'block'});
    $('.presence_type_box').css({'display': 'none'});
}

purchase_type_presence.click(function () {
    if ($(this).is(':checked')) {
        $('.presence_type_box').css({'display': 'block'});
        $('.purchase_type_box').css({'display': 'none'});
    }
});

if (purchase_type_presence.is(':checked')) {
    $('.presence_type_box').css({'display': 'block'});
    $('.purchase_type_box').css({'display': 'none'});
}

