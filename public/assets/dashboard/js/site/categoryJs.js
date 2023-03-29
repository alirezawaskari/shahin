//searchCategory
$(document).ready(function(){
    var minlength = 3;
    $(".searchCategory").keyup(function() {
        var value = $(this).val();
        var category_center_id = $('.category_center_id').val();
        if (value.length < minlength) $("#category-suggestion-box").html(' ');
        if (value.length >= minlength) {
            $("#category-suggestion-box").html(' ');
            $.ajax({
                type: "post",
                url: '/dashboard/site/searchCategory',
                headers: {"Authorization": localStorage.getItem('token')},
                data: {keyword: value,category_center_id:category_center_id},
                beforeSend: function(){
                    $("#category-suggestion-box").html(' ').css("background","rgb(249 248 248) no-repeat 165px");
                },
                success: function (result) {
                    $("#category-suggestion-box").show().html(result['view_result']);
                }
            });
        }
    });
});


$(document).on('click', '.choose_category_id', function () {
    var id = $(this).val();
    $.ajax({
        type: "post",
        url: '/dashboard/site/findBrand',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id},
        success: function (result) {
            if(result['brand_view_result']){
                $('.brandsBox').html(result['brand_view_result']);
            }
            if(result['view_result']){
                $('.createAttributesBox').html(result['view_result']);
                $(".selectMulti").select2();
            }
            $('.selectSearch').select2();
        }
    });
});

$(".changeCategory").click(function () {
    $('.changeCategoryBox').slideDown("slow");
});


$(document).on("change", ".parent_id", function (e) {
    $('.createAttributesBox').html('');
    e.preventDefault();
    var id = $(this).children("option:selected").val();
    var ind = $(this).attr('id');
    for($f=parseInt(ind)+1;$f<10;$f++){
        $('#'+ $f +'.parent_id').remove();
    }
    if (id != '') {
        $.ajax({
            type: "post",
            url: '/dashboard/site/findChild',
            data: {id: id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                if (result['categories'] != '' && result['depth'] != null) {
                    var a_sel = $('<select id="'+ result['depth'] +'" data-title="'+id+'"  class="form-control parent_id catSelect" name="category_id[]" >');
                    a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                    $(result['categories']).each(function (index, val) {
                        a_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.fieldsBox').append(a_sel);
                }


                if(result['brands'] != ''){
                    $('.brands').remove();
                    var brands_sel = $('<select class="form-control brands" name="good[brand_id]" >');
                    brands_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید'));
                    $(result['brands']).each(function (index, val) {
                        brands_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.brandsBox').append(brands_sel);
                }

                if(result['view_result']){

                    $('.createAttributesBox').html($('.createAttributesBox').html() + result['view_result']);
                    $(".selectMulti").select2();
                }
            }
        });
    }
});

//parent_id on load page
var id = $(".parent_id").children("option:selected").val();
if(id ){
    $.ajax({
        type: "post",
        url: '/dashboard/site/findChild',
        data: {id: id},
        success: function (result) {
            if (id != '') {
                if (result['categories'] != '') {
                    var a_sel = $('<select class="form-control parent_id catSelect" name="category_id[]" >');
                    a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                    $(result['categories']).each(function (index, val) {
                        a_sel.append($("<option>").attr('value', val.id).text(val.title));
                    });
                    $('.fieldsBox').append(a_sel);
                }
            }
        }
    });
}




