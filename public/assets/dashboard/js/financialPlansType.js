$('#payment_type').on('change', function () {
    if ($('#payment_type').val() == 'check') {
        $('#type_number_pay_steps').show();
        if ($('#select_type_number_pay_steps').val() == 'limit') {
            $('#number_pay_steps').show();
        } else {
            $('#number_pay_steps').hide();
        }
        $('#prepayment_percent , #guarantee_type').show();
    } else if($('#payment_type').val() == 'installment') {
        $('input[name=number_pay_steps]').val(null);
        $('#type_number_pay_steps, #number_pay_steps').hide();
        $('#prepayment_percent , #guarantee_type').show();
    } else if($('#payment_type').val() == 'cash') {
        $('input[name=number_pay_steps],select[name=guarantee_type]').val(null);
        $('#type_number_pay_steps, #number_pay_steps , #prepayment_percent , #guarantee_type').hide();
    }
});
$('#select_type_number_pay_steps').on('change', function () {
    if ($('#select_type_number_pay_steps').val() == 'limit') {
        $('#number_pay_steps').show();
    } else {
        $('input[name=number_pay_steps]').val(null);
        $('#number_pay_steps').hide()
    }
});
