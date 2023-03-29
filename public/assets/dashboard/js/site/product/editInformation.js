
$(document).on('click', '.updateProductAttribute', function () {
    var product_attribute_id = $(this).attr('data-title');
    var id = $(this).attr('id');
    var stock = $('#' + product_attribute_id + '.stock').val();
    var price = $('#' + product_attribute_id + '.price').val();
    var submission_time = $('#' + product_attribute_id + '.submission_time').children("option:selected").val();
    $.ajax({
        type: "post",
        url: '/dashboard/product/updateProductAttribute',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            id: id, stock: stock, price: price, submission_time: submission_time,
            product_attribute_id: product_attribute_id
        },
        success: function (result) {
            if (result['status'] == 'true') {
                toastr.success('اطلاعات با موفقیت ویرایش شد.');
            }
        }
    });
});

$(document).on('click', '.returnProductAttribute', function () {
    var product_attribute_id = $(this).attr('data-title');
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/product/returnProductAttribute',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id, product_attribute_id: product_attribute_id},
        success: function (result) {
            if (result['status'] == 'true') {
                $('#' + product_attribute_id + '.stock').val(result['stock']);
                $('#' + product_attribute_id + '.price').val(result['price']);
                $('#' + product_attribute_id + '.submission_time').val(result['submission_time']);
            }
        }
    });
});


$(".changeIsShow").on('click', function () {
    var product_attribute_id = $(this).attr('id');
    var is_show = $(this).attr('data-title');
    if (is_show == 0) {
        Swal.fire({
            title: 'هشدار',
            icon: 'info',
            text: 'با غیر فعال کردن تنوع ، همچنان به موارد رزرو شده مشتریان تعهد خواهید داشت.آیا از غیرفعال کردن این تنوع اطمینان دارید؟',
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر',
            showCancelButton: true,
            showCloseButton: true
        })
            .then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/product/updateProductAttribute',
                        headers: {"Authorization": localStorage.getItem('token')},
                        data: {product_attribute_id: product_attribute_id, is_show: is_show},
                        success: function (result) {
                            if (result['status'] === 'true') {
                                if (is_show == 1) {
                                    $('#' + product_attribute_id + '.changeIsShow').attr('data-title', 0).html('غیرفعال');
                                }
                                if (is_show == 0) {
                                    $('#' + product_attribute_id + '.changeIsShow').attr('data-title', 1).html('فعال');
                                }
                            }
                        }
                    });
                }
            });
    } else {
        $.ajax({
            type: "post",
            url: '/dashboard/product/updateProductAttribute',
            headers: {"Authorization": localStorage.getItem('token')},
            data: {product_attribute_id: product_attribute_id, is_show: is_show},
            success: function (result) {
                if (result['status'] === 'true') {
                    if (is_show == 1) {
                        $('#' + product_attribute_id + '.changeIsShow').attr('data-title', 0).html('غیرفعال');
                    }
                    if (is_show == 0) {
                        $('#' + product_attribute_id + '.changeIsShow').attr('data-title', 1).html('فعال');
                    }
                }
            }
        });
    }

});

