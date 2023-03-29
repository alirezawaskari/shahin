
$('.globalDiscount').keyup(function () {
    var discount = $(this).val();
    $('.discount').val(discount);
    $("input[name='price[]']").each(function () {
        var id = $(this).attr('id');
        var price = $(this).val().replace(/,/g, "");
        if (discount != null) {
            var amount = price * discount / 100;
            var priceByDiscount = price - amount;
            $('#' + id + '.priceByDiscount').html(priceByDiscount.toLocaleString());
        } else {
            $('#' + id + '.priceByDiscount').html(price.toLocaleString());
        }
    });
});

$('.discount').keyup(function () {
    var discount = $(this).val();
    var id = $(this).attr('id');
    var price = $('#' + id + '.price').val().replace(/,/g, "");
    if (discount != null) {
        var amount = price * discount / 100;
        var priceByDiscount = price - amount;
        $('#' + id + '.priceByDiscount').html(priceByDiscount.toLocaleString());
    } else {
        $('#' + id + '.priceByDiscount').html(price.toLocaleString());
    }
});

$('.globalCapacity').keyup(function () {
    $('.capacity').val($(this).val());
});

$(document).on('click', '.deleteProductAttribute', function () {
    var id = $(this).attr('id');
    var coupon_id = $(this).attr('data-title');
    $.ajax({
        type: "post",
        url: '/dashboard/periodicPrices/deleteProductAttribute',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id, coupon_id: coupon_id},
        success: function (result) {
            if (result['status'] == 'true') {
                $('#' + id + '.trPeriodicPrices').remove();
                toastr.success('اطلاعات با موفقیت حذف شد.');
            }
            if (result['status'] == 'false') {
                toastr.warning('خطا در ارسال اطلاعات');
            }
        }
    });
});

$(document).on('click', '.periodicUpdate', function () {
    var p_a_id = $(this).attr('id');
    var purchase_type = $('select[name="periodic[' + p_a_id + '][purchase_type]"]').val();
    var pay_type = $('select[name="periodic[' + p_a_id + '][pay_type]"]').val();
    var capacity = $('input[name="periodic[' + p_a_id + '][capacity]"]').val();
    var start_date = $('input[name="periodic[' + p_a_id + '][start_date]"]').val();
    var end_date = $('input[name="periodic[' + p_a_id + '][end_date]"]').val();
    var discount = $('input[name="periodic[' + p_a_id + '][discount]"]').val();
    $.ajax({
        type: "patch",
        url: '/dashboard/periodicPrices/update',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {
            p_a_id: p_a_id, purchase_type: purchase_type, pay_type: pay_type, capacity: capacity,
            start_date: start_date, end_date: end_date, discount: discount
        },
        success: function (result) {
            if (result['status'] == true) {
                toastr.success('اطلاعات با موفقیت ویرایش شد');
            }
        }
    });
});
var date = new Date();
var tomorrow = new Date(date.getFullYear(), date.getMonth(), (date.getDate() + 1));
$(".date").pDatepicker({
    initialValueType: "gregorian",
    format: 'YYYY-MM-DD',
    startDate: tomorrow,
    calendar: {
        'persian': {
            'locale': 'fa',
            'showHint': false
        },
        'gregorian': {
            'locale': 'en',
            'showHint': false
        }
    }
});
