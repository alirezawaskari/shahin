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

CKEDITOR.replace('description', {
    filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token()])}}",
    filebrowserUploadMethod: 'form'
});

$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});

if (($(".expire_date").length > 0)) {
    $('.expire_date').persianDatepicker({
        observer: true,
        format: 'YYYY-MM-DD HH:mm:ss',
        timePicker: {enabled: true, meridiem: {enabled: true}
        }
    });
}

var center_id = $('.center_id').val();
var type = 'center';
$(document).ready(function () {
    if(center_id.length > 0) {
        $(".productsPack option").remove();
        $.ajax({
            type: "post",
            url: '/dashboard/getProductsPack',
            data: {type:type,ids: center_id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                var select = $('.productsPack');
                $(result['products']).each(function (index, val) {
                    select.append($("<option>").attr('value', val.id).text(val.name));
                });
                $('.productsPackBox').html('').html(select);
                select.select2();
            }
        });
    }
});

$('.productPack').change(function () {
    if($('.productPack').length > 0) {
        $(".productsPack option").remove();
        var type = $(this).attr('id');
        $.ajax({
            type: "post",
            url: '/dashboard/getProductsPack',
            data: {type:type,ids: $(this).val()},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                var select = $('.productsPack');
                $(result['products']).each(function (index, val) {
                    select.append($("<option>").attr('value', val.id).text(val.name));
                });
                $('.productsPackBox').html('').html(select);
                select.select2();
            }
        });
    }
});
