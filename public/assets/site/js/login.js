
$("#login_form").submit(function (event) {
    var regexname= /^09[0-9][0-9]{8}$/;
    var phone=$('#phone').val();
    phone = fixNumbers(phone);
    alert('phone');
    if (!phone.match(regexname)) {
        alert('لطفا شماره موبایل را درست وارد نمایید !');
        event.preventDefault();
    }
});
