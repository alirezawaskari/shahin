
$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});

$(document).on('click', '.insertValidation', function () {
    var field = $(this).attr('id');
    var product_validation_id = $('#' + field + '.product_validation_id').children("option:selected").val();
    var product_id = $('#' + field + '.product_id').val();
    var validationable_type = $('#' + field + '.validationable_type').val();
    var validationable_id = $('#' + field + '.validationable_id').val();
    $.ajax({
        type: "post",
        url: '/dashboard/productValidation',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            product_validation_id: product_validation_id, field: field, product_id: product_id,
            validationable_id: validationable_id, validationable_type: validationable_type
        },
        success: function (result) {
            if (result['status'] === 'true') toastr.success('ثبت خطا با موفقیت انجام شد.');

            if (result['status'] === '301') toastr.warning('قبلا این خطا ثبت شده است.');

            if (result['status'] === 'false') toastr.warning('امکان ثبت خطا وجود ندارد.');

            $(".insertValidationModal .close").click();
        }
    });
});


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


$(document).on('click', '.remove_image_product', function () {
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImageProduct',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id},
        success: function (result) {
            if (result['status'] == 'true') {
                $('.image_product').remove();
            }
        }
    });
});

