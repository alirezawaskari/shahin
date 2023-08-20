$(document).ready(function () {
    fetchIranSmsCredit()
    $('.fetch-iran-sms-credit').on('click',function (){
        fetchIranSmsCredit()
    })
});
function fetchIranSmsCredit(){
    var token = $("meta[name='csrf-token']").attr("content");
    $.ajax({
        url: "/dashboard/sms/iran-sms-credit",
        type: 'POST',
        data: {
            '_token': token,
        },
        success: function (response) {
            if (response.data.iran_sms_credit_error == true){
                $('#iran-sms-credit').empty();
                $('#iran-sms-credit').append(
                    `<span class="badge badge-warning"> عدم دسترسی به اعتبار</span>`
                );
            }else {
                $('#iran-sms-credit').empty();
                $('#iran-sms-credit').append(separateNumber(response.data.iran_sms_credit)+' ریال ');
                if (response.data.iran_sms_credit < 200000){

                    $('#tr-iran-sms').removeClass('table-success');
                    $('#tr-iran-sms').addClass('table-danger');

                }else {
                    $('#tr-iran-sms').removeClass('table-danger');
                    $('#tr-iran-sms').addClass('table-success');
                }
                $('.fetch-iran-sms-credit').hide();
                $('.fetch-iran-sms-credit-success').show();
                setTimeout(function() {
                    $('.fetch-iran-sms-credit').show();
                    $('.fetch-iran-sms-credit-success').hide();
                }, 60000);
            }

        }
    });
}
