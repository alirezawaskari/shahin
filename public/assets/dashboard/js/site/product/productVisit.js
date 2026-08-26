
$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});

//insert an validation error of field
$(document).on('click', '.insertValidation', function () {
    var field = $(this).attr('id');
    var validation_id = $('#' + field + '.validation_id').children("option:selected").val();
    var product_id = $('#' + field + '.product_id').val();
    var productvalidationable_type = $('#' + field + '.validationable_type').val();
    var productvalidationable_id = $('#' + field + '.validationable_id').val();
    $.ajax({
        type: "post",
        url: '/dashboard/productValidation',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            validation_id: validation_id,
            field: field,
            product_id: product_id,
            productvalidationable_id: productvalidationable_id,
            productvalidationable_type: productvalidationable_type
        },
        success: function (result) {
            if (result['status'] === 'true') {
                $('.buttonsConfirm').css({'display': 'none'});
                $('.hasErrorFields').css({'display': 'inline-block'});
                toastr.success('ثبت خطا با موفقیت انجام شد.');
            }

            if (result['status'] === 'false') toastr.warning('امکان ثبت خطا وجود ندارد.');

            $(".insertValidationModal .close").click();
        }
    });
});


//Found an validation error of field
$(document).on('click', '.insertValidationBtn', function () {
    var field = $(this).attr('id');
    var product_id = $('#' + field + '.product_id').val();
    var validationable_type = $('#' + field + '.validationable_type').val();
    var validationable_id = $('#' + field + '.validationable_id').val();
    $.ajax({
        type: "post",
        url: '/dashboard/getValidation',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            field: field,
            product_id: product_id,
            validationable_id: validationable_id,
            validationable_type: validationable_type
        },
        success: function (result) {
            if (result['product_validation_id'] != null)
                $('#' + field + '.product_validation_id option[value="' + result['product_validation_id'] + '"]').attr("selected", "selected");

        }
    });
});

//assign Similar Product id to other product
$('.assignSimilarProduct').click(function () {
    var id = $(this).attr('id');
    var similar_id = $(this).attr('data-title');
    $.ajax({
        type: "post",
        url: '/dashboard/assignSimilarProduct',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id, similar_id: similar_id},
        success: function (result) {
            if (result['status'] === '200') {
                $('.similarProductsList').css({'display': 'none'});
                $('.confirmSimilarProductsMsg').html(result['msg']);
                $('.confirmSimilarProducts').css({'display': 'block'});
            }
        }
    });
});


$('.backSimilarProductsList').click(function () {
    $('.similarProductsList').css({'display': 'block'});
    $('.confirmSimilarProducts').css({'display': 'none'});
});

$('.centerCategory').select2();

$('.saveChangeVisitProduct').click(function () {
    var id = $(this).attr('id');
    var reference_price = $('.reference_price').val();
    var isShow = 0;
    var is_sale = 0;
    var internal_use = 0;
    if ($(".isShow:checked").val()) isShow = $(".isShow:checkbox").val();
    if ($(".is_sale:checked").val()) is_sale = $(".is_sale:checkbox").val();
    if ($(".internal_use:checked").val()) internal_use = $(".internal_use:checkbox").val();
    $.ajax({
        type: "post",
        url: '/dashboard/saveChangeVisitProduct',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            id: id,
            isShow: isShow,
            is_sale: is_sale,
            internal_use: internal_use,
            reference_price: reference_price
        },
        success: function (result) {
            if (result['status'] === 'true') {
                toastr.success('اطلاعات با موفقیت ذخیره شد');
            }
        }
    });
});


