$('#payment_type').on('change', function () {
    if ($('#payment_type').val() == 'check') {
        $('#type_number_pay_steps').show();
        if ($('#select_type_number_pay_steps').val() == 'limit') {
            $('#number_pay_steps').show();
        } else {
            $('#number_pay_steps').hide();
        }
    } else {
        $('#type_number_pay_steps, #number_pay_steps').hide();
    }
});
$('#select_type_number_pay_steps').on('change', function () {
    if ($('#select_type_number_pay_steps').val() == 'limit') {
        $('#number_pay_steps').show();
    } else {
        $('#number_pay_steps').hide()
    }
});
