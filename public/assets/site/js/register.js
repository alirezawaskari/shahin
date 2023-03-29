$(document).ready(function () {
    $('#pass').val('');
    var regexname= /^09[0-9][0-9]{8}$/;
    $("#check_register").submit(function (event) {
         var phone = $('#phone_number').val()
        phone = fixNumbers(phone);
        if (!phone.match(regexname)) {
            alert('لطفا شماره موبایل را درست وارد نمایید !');
        }else{
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            type: "GET",
            url: "/register/check-user-is-register",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
           var userIsRegistered =data.data.userIsRegistered
           var phone =data.data.phone

          if (userIsRegistered ==false){
              $('#register_box').show();
              $('.phone').val(phone);
              $('#check_mobile_box').hide();
          }else{
             $('#check_mobile_box').hide();
              $('#login_box').show();
              $('#pass').val();
              $('.phone').val(phone);
          }

        });
        }
        event.preventDefault();
    });

    $("#user_register_form").submit(function (event) {
        var register_phone=$('#phone_register').val();
        if (!register_phone.match(regexname)) {
            alert('لطفا شماره موبایل را درست وارد نمایید !');
            event.preventDefault();
        }
    });

});
