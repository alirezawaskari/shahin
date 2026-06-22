

//attributeIsPrice in price of good
$(".attributeIsPrice").click(function () {
    var id = $(this).attr('id');
    $('.attribute_item_id').attr('value', id);
    $('.selectedAttribute').val($('#' + id + '.attributeIsPrice').html());
});

var sales_price = $('.sales_price');
var reference_price = $('.reference_price');
sales_price.keypress(function () {
    check_price(sales_price, reference_price);
});
sales_price.keyup(function () {
    check_price(sales_price, reference_price);
});

function check_price(sales_price, reference_price) {
    sales_price = parseInt(sales_price.val().replace(/,/g, ''));
    reference_price = parseInt(reference_price.val().replace(/,/g, ''));
    if (sales_price > reference_price) {
        $('.message_sales_price').html('لطفا قیمت را کمتر از قیمت مرجع را وارد نمایید.');
    } else {
        $('.message_sales_price').html('');
    }
}

//send product price to server
$('.productPrice').click(function () {
    var has_attribute = $('.has_attribute').val();

    var product_id = $('.product_id').val();
    var guarantee = $('.guarantee').children("option:selected").val();
    var is_show = 0;
    if ($(".is_show:checked").val()) is_show = $(".is_show:checked").val();
    var posted_by = $(".posted_by:checked").val();
    var submission_time = $('.submission_time').children("option:selected").val();
    var stock = $('.stock').val();
    var seller_code = $('.seller_code').val();
    var price = $('.price_by_attr').val().replace(/,/g, '');
    var reference_price = $('.reference_price').val().replace(/,/g, '');
    var center_id = [];
    if ($('.center_id').val()) center_id = $('.center_id').val();
    var product_store_room_id = [];
    if ($('.product_store_room_id').val()) product_store_room_id = $('.product_store_room_id').val();

    if (center_id.length || product_store_room_id.length) {
        if (price !== '' && price > 0) {
            if (parseInt(price) <= parseInt(reference_price)) {
                if (guarantee !== '' && stock !== '' && price !== '') {
                    if (has_attribute == 1) {
                        var attribute_item_id = $('.attribute_item_id').val();
                        if (attribute_item_id) {
                            var data_title = $('#' + attribute_item_id + '.attributeIsPrice').attr('data-title');
                            if (data_title !== 'selected') {
                                var attribute_id = $('.attribute_id').val();
                                var attribute_item_text = $('.selectedAttribute').val();
                                $.ajax({
                                    type: "post",
                                    url: '/dashboard/product/price',
                                    headers: {"Authorization": localStorage.getItem('token')},
                                    data: {
                                        product_id: product_id,
                                        attribute_id: attribute_id,
                                        attribute_item_id: attribute_item_id,
                                        attribute_item_text: attribute_item_text,
                                        guarantee: guarantee,
                                        is_show: is_show,
                                        posted_by: posted_by,
                                        submission_time: submission_time,
                                        stock: stock,
                                        seller_code: seller_code,
                                        price: price,
                                        center_id: center_id,
                                        product_store_room_id: product_store_room_id
                                    },
                                    success: function (result) {
                                        if (result === 'true') {
                                            $('#' + attribute_item_id + '.attributeIsPrice').attr('data-title', 'selected');
                                            toastr.success('ثبت قیمت گذاری : قیمت گذاری کالا با موفقیت انجام شد.');
                                            location.reload();
                                        }
                                        if (result === '301') {
                                            toastr.warning('کالا با این ویژگی قبلا ثبت شده است. اگر تمامی موارد مانند ویژگی ثبت شده قبلی باشد ، به موجودی این تنوع اضافه می شود. ');
                                        }
                                        if (result === '200') {
                                            toastr.success('کالا با این ویژگی قبلا ثبت شده است، موجودی جدید وارد شده به این تنوع اضافه می شود. ');
                                        }
                                    }
                                });
                            }
                            else {
                                toastr.warning('لطفا ویژگی دیگری را انتخاب نمایید.');
                            }
                        }
                        else {
                            toastr.warning(' لطفا ویژگی دیگری را انتخاب نمایید.');
                        }
                    }

                    if (has_attribute == 0) {
                        $.ajax({
                            type: "post",
                            url: '/dashboard/product/price/storeWithoutAttr',
                            data: {
                                product_id: product_id,
                                guarantee: guarantee,
                                is_show: is_show,
                                posted_by: posted_by,
                                submission_time: submission_time,
                                stock: stock,
                                seller_code: seller_code,
                                price: price,
                                center_id: center_id,
                                product_store_room_id: product_store_room_id
                            },
                            headers: {"Authorization": localStorage.getItem('token')},
                            success: function (result) {
                                if (result == 'true') {
                                    toastr.success('قیمت گذاری کالا با موفقیت انجام شد.');
                                    location.reload();
                                }
                            }
                        });
                    }
                }
                else {
                    toastr.info('لطفا تمام موارد ضروری را وارد نمایید.');
                }
            }
            else {
                toastr.warning('قیمت گذاری : لطفا قیمت را کمتر از قیمت مرجع را وارد نمایید.');
            }
        }
        else {
            toastr.info(' لطفا قیمت وارد نمایید.');
        }
    } else {
        toastr.info(' لطفا شعبه | انبار انتخاب نمایید.');
    }

});


$(document).on('click', '.addRequestAttributeItem', function () {
    var attribute_item_title = $('.attribute_item_title').val();
    var attribute_id = $('.attribute_id').val();
    var color_palette = $('.color_palette').val();
    $.ajax({
        type: "post",
        url: '/dashboard/site/addRequestAttributeItem',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            attribute_item_title: attribute_item_title,
            attribute_id: attribute_id,
            color_palette: color_palette
        },
        success: function (result) {
            if (result['status'] === 'true') {
                $(".attributeItemRequest .close").click();
                toastr.success('درخواست شما ثبت شد و بعد از تایید مدیر می توانید از این ویژگی استفاده نمایید.');
            }
            if (result['status'] === 301) {
                toastr.warning('برای دسته بندی این کالا ، ویژگی درخواستی شما ثبت شده است.');
            }
        }
    });
});


