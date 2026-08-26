//type_good_selected
$('.type_good_selected').click(function () {
    if ($(this).attr("value") == 0) {
        $(".goodStockBox").css({'display': 'block'});
        $(".goodCenterBox").css({'display': 'none'});
        $('.internal_use').css({'display': 'block'});
        $('.tagBox').css({'display': 'block'});
    }
    if ($(this).attr("value") == 1) {
        $(".goodCenterBox").css({'display': 'block'});
        $('.internal_use').css({'display': 'none'});
        $(".goodStockBox").css({'display': 'none'});
        $('.tagBox').css({'display': 'block'});
    }
});

$('.type_good_selected').click();

// Guard: if ckeditor.js ever fails to load, a "CKEDITOR is not defined" error here
// would halt the whole script (so the .productPack handler below never binds and
// انتخاب کالا stays empty). The Blade {{route()}} below never runs in a static .js
// file, so build the upload URL from the csrf-token meta instead.
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('description', {
        filebrowserUploadUrl: '/dashboard/ckeditor/upload?_token=' + $('meta[name="csrf-token"]').attr('content'),
        filebrowserUploadMethod: 'form'
    });
}

$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});

var center_id = $('.center_id').val();
var type = 'center';
$(document).ready(function () {
    if(center_id && center_id.length > 0) {
        var select = $('.productsPack');
        select.empty();
        $.ajax({
            type: "post",
            url: '/dashboard/getProductsPack',
            data: {type:type,ids: center_id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                // .productsPack is already a select2 (adminJs inits .js-example-basic-single
                // on load), so destroy it before rebuilding or the new options won't render.
                if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                select.empty();
                $(result['products']).each(function (index, val) {
                    select.append($("<option>").attr('value', val.id).text(val.name));
                });
                select.select2();
            }
        });
    }
});

$('.productPack').change(function () {
    if($('.productPack').length > 0) {
        var select = $('.productsPack');
        select.empty();
        var type = $(this).attr('id');
        $.ajax({
            type: "post",
            url: '/dashboard/getProductsPack',
            data: {type:type,ids: $(this).val()},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                // .productsPack is already a select2 (adminJs inits .js-example-basic-single
                // on load), so destroy it before rebuilding or the new options won't render.
                if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                select.empty();
                $(result['products']).each(function (index, val) {
                    select.append($("<option>").attr('value', val.id).text(val.name));
                });
                select.select2();
            }
        });
    }
});
