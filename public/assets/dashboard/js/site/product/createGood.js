
$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});

//type_good_selected
$type_good_selected = $('.type_good_selected');
if($type_good_selected.prop('checked') && $type_good_selected.attr("value") == 0){
    $(".goodStockBox").css({'display': 'block'});
    $(".goodCenterBox").css({'display': 'none'});
    $('.internal_use').css({'display': 'block'});
    $('.tagBox').css({'display': 'block'});
}

$type_good_selected.click(function () {
    $value=$(this).attr("value");
    if ($value == 0) {
        $(".goodStockBox").css({'display': 'block'});
        $(".goodCenterBox").css({'display': 'none'});
        $('.internal_use').css({'display': 'block'});
        $('.tagBox').css({'display': 'block'});
    }
    if ($value == 1) {
        $(".goodCenterBox").css({'display': 'block'});
        $('.internal_use').css({'display': 'none'});
        $(".goodStockBox").css({'display': 'none'});
        $('.tagBox').css({'display': 'block'});

        $.ajax({
            url: '/dashboard/getCenterCategory',
            type: 'get',
            success: function (result) {
                if (result['centers'] != '') {
                    var select = $('<select id="center_lbl" class="form-control selectMulti centerCategory" name="good[center_id][]" multiple="multiple">');
                    $(result['centers']).each(function (index, val) {
                        select.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.centerCategoryBox').html(select);
                    select.select2();
                }
            }
        });
    }
});

