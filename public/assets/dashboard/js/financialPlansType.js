$('#payment_type').on('change', function () {
    if ($('#payment_type').val() == 'check') {
        $('#type_number_pay_steps').css('display', 'block');
        if ($('#select_type_number_pay_steps').val() == 'limit') {
            $('#number_pay_steps').css('display', 'block');
        } else {
            $('#number_pay_steps').css('display', 'none');
        }
    } else {
        $('#type_number_pay_steps').css('display', 'none');
        $('#number_pay_steps').css('display', 'none');
    }
});
$('#select_type_number_pay_steps').on('change', function () {
    if ($('#select_type_number_pay_steps').val() == 'limit') {
        $('#number_pay_steps').css('display', 'block');
    } else {
        $('#number_pay_steps').css('display', 'none');
    }
});
