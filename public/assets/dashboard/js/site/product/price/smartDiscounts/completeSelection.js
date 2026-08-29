
$('.globalDiscount').keyup(function () {
    var discount = $(this).val();
    $('.discount').val(discount);
    $("input[name='price[]']").each(function() {
        var id=$(this).attr('id');
        var price = $(this).val().replace(/,/g, "");
        if(discount != null){
            var amount = price * discount / 100;
            var priceByDiscount = price - amount;
            $('#'+id+'.priceByDiscount').html(priceByDiscount.toLocaleString());
        }else{
            $('#'+id+'.priceByDiscount').html(price.toLocaleString());
        }
    });
});

$('.discount').keyup(function () {
    var discount = $(this).val();
    var id=$(this).attr('id');
    var price = $('#'+id+'.price').val().replace(/,/g, "");
    if(discount != null){
        var amount = price * discount / 100;
        var priceByDiscount = price - amount;
        $('#'+id+'.priceByDiscount').html(priceByDiscount.toLocaleString());
    }else{
        $('#'+id+'.priceByDiscount').html(price.toLocaleString());
    }
});

$('.globalCapacity').keyup(function () {
    $('.capacity').val($(this).val());
});

$(document).on('click', '.removeProductAttribute', function () {
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/periodicPrices/removeProductAttribute',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {id: id},
        success: function (result) {
            if(result['status'] === 'true'){
                $('#'+id+'.trPeriodicPrices').remove();
            }
        }
    });
});

// The two fields are built by jalaliDatePicker.js (their format and the "not before
// tomorrow" bound come from the data-jdp-* attributes in the view), so the overlap check
// that used to hang off pwt-datepicker's `onSelect` now listens for `change` instead.
$(document).on('change', '.start_date, .end_date', function () {
    var start_date = $('.start_date').val();
    var end_date = $('.end_date').val();
    var id = $(this).attr('data-title');

    $.ajax({
        type: "post",
        url: '/dashboard/periodicPrices/checkDate',
        headers: {"Authorization": localStorage.getItem('token')},
        data: {start_date:start_date,end_date:end_date,id:id},
        success: function (result) {
            if(result['status'] == '400'){
                $('#'+result['id']+'.alert_date_message').css({'display':'block'}).html(result['message']);
            }
            if(result['status'] == '200'){
                $('#'+result['id']+'.alert_date_message').css({'display':'none'}).html(' ');
            }
        }
    });
});


