$(document).ready(function () {
    var minlength = 3;

    $(document).on("change", ".center_id", function (e) {
        e.preventDefault();
        $('.productAttribute').html('');
        $('.productsBox').html('');
        var value = $(".productName").val();
        var center_id = $(this).children("option:selected").val();
        //if (value.length >= minlength) {
        findCategoryByProductName(value,center_id);
        //}
    });


    $(".productName").keyup(function () {
        $('.productAttribute').html('');
        $('.productsBox').html('');
        var value = $(this).val();
        var center_id = $('.center_id').children("option:selected").val();
        if (value.length >= minlength) {
            findCategoryByProductName(value,center_id);
        }
    });

    function findCategoryByProductName(value,center_id) {
        $.ajax({
            url: "/dashboard/site/findCategoryByProductName",
            type: 'post',
            data: {name: value, center_id: center_id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                if (result['status'] == 'true') {
                    $('.productsBox').html('').html(result['view_result']).css({'display': 'block'});
                    $(".productSelect").select2();
                }
            }
        });
    }

});


$(document).on('change', '.productSelect', function () {
    var center_id = $('.center_id').children("option:selected").val();
    $.ajax({
        url: "/dashboard/site/productAttributeInCoupon",
        type: 'post',
        data: {products_ids: $(this).val(), center_id: center_id},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if (result['status'] == 'true') {
                $('.productAttribute').html('').html(result['view_result']).css({'display': 'block'});
            }
        }
    });

});


$(document).on('click', '.saveCouponItem', function () {
    var product_attribute_ids = [];
    $("input:checkbox[name=product_attribute_id]:checked").each(function () {
        product_attribute_ids.push($(this).val());
    });
    $.ajax({
        url: "/dashboard/site/couponItem",
        type: 'post',
        data: {product_attribute_ids: product_attribute_ids, coupon_id: $('.coupon_id').val()},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if (result['status'] == 'true') {
                toastr.success('اطلاعات با موفقیت ثبت شد.');
                $('.productAttribute').html('');
                $('.productsBox').html('');
                $('.productName').val('');
                $('.center_id').val('');
                $("html, body").animate({scrollTop: 0}, "slow");
            }
        }
    });
});

$(document).on("change", ".servicesCategory", function (e) {
    e.preventDefault();
    var id = $(this).children("option:selected").val();
    $('.service_id').remove();
    $('.lbl_service_id').remove();
    $('.service_item_id').remove();
    $('.lbl_service_item_id').remove();
    $.ajax({
        type: "post",
        url: '/dashboard/site/findServiceByCategoryId',
        data: {id: id},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if (result['status'] == 'true' && result['services'] != '') {
                var lbl = "<label class='lbl_service_id' for='service_id'>خدمات</label>";
                var a_sel = $('<select id="service_id" class="form-control service_id" name="service_id" >');
                a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                $(result['services']).each(function (index, val) {
                    a_sel.append($("<option>").attr('value', val.id).text(val.title));
                });
                $('.servicesBox').append(lbl).append(a_sel);
            }
        }
    });

});

$(document).on("change", ".service_id", function (e) {
    e.preventDefault();
    var id = $(this).children("option:selected").val();
    $('.service_item_id').remove();
    $('.lbl_service_item_id').remove();
    $.ajax({
        type: "post",
        url: '/dashboard/site/findServiceItem',
        data: {id: id},
        headers: {"Authorization": localStorage.getItem('token')},
        success: function (result) {
            if (result['status'] == 'true' && result['services_items'] != '') {
                var lbl = "<label class='lbl_service_item_id' for='service_item_id'> زیر مجموعه خدمات</label>";
                var a_sel = $('<select id="service_item_id" class="form-control service_item_id" name="service_item_id" >');
                a_sel.append($("<option>").attr('value', "").attr('data-title', "").text('لطفا انتخاب کنید...'));
                $(result['services_items']).each(function (index, val) {
                    a_sel.append($("<option>").attr('value', val.id).text(val.title));
                });
                $('.serviceItemsBox').append(lbl).append(a_sel);
            }
        }
    });

});
