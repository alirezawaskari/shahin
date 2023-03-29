$(document).ready(function () {
    if ($('#phoneModal').length) {
        $('#phoneModal').modal('show');
    }

    if ($('#newsletter_success').length){
        swal({
            text: "ثبت نام شما در خبرنامه با موفقیت انجام شد",
            icon: "success",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#newsletter_exist').length){
        swal({
            text: "این ایمیل از قبل عضو می باشد!",
            icon: "error",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#newsletter_error').length){
        swal({
            text: "لطفا ایمیل صحیح وارد نمایید!",
            icon: "warning",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#register_success').length){
        swal({
            text: "ثبت نام شما با موفقیت انجام شد",
            icon: "success",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#contact_success').length){
        swal({
            text: "پیام شما با موفقیت ثبت گردید",
            icon: "success",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#contact_error').length){
        swal({
            text: "لطفا ایمیل خود را صحیح وارد نمایید!",
            icon: "error",
            button: "تایید",
            allowOutsideClick: true
        });
    }

    if ($('#resetpass_success').length){
        swal({
            text: "رمز جدید به موبایلتان ارسال شد!",
            icon: "success",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#resetpass_error').length){
        swal({
            text: "لطفا موبایل صحیح وارد نمایید!",
            icon: "error",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#wallet_success').length){
        swal({
            text: "کیف پول شما با موفقیت شارژ شد!",
            icon: "success",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#wallet_success').length){
        swal({
            text: "کیف پول شما با موفقیت شارژ شد!",
            icon: "success",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }
    if ($('#wallet_error').length){
        swal({
            text: "لطفا مبلغ صحیح وارد نمایید!",
            icon: "error",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#wallet_payment').length){
        swal({
            text: "عملیات پرداخت با شکست مواجه شد!",
            icon: "error",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#profile_update').length){
        swal({
            text: "اطلاعات پروفایل شما آپدیت شد!",
            icon: "success",
            button: "تایید",
            timer:3000,
            allowOutsideClick: true
        });
    }

    if ($('#factor_success').length){
        swal({
            text: "اطلاعات با موفقیت ثبت گردید!",
            icon: "success",
            button: "تایید",
            timer:4000,
            allowOutsideClick: true
        });
    }

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
        }
    });

    $('#province_id').on('change', function () {
        var id = $(this).val();
        if (id == 0) {
            $('#city_id')
                .find('option')
                .remove();

        }
        if (id != 0) {
            $.ajax({
                url: '/city/select',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (data) {
                    if (data.count == 'available') {
                        $('#city_id')
                            .find('option')
                            .remove();

                        $('#city_id').append('<option value="">انتخاب شهر</option>');
                        for (var i = 0; i < data.cities.length; i++) {
                            $('#city_id').append('<option value="' + data.cities[i]['id'] + '">' + data.cities[i]['name'] + '</option>');
                        }
                    }
                }
            });
        }
    });


    $('#uploadAvatar').change(function() {
        $(this).closest('form').submit();
    });

    $('#city_id').on('change', function() {
        if (this.value == 117){
            $('#area_id').show();
        }else {
            $('#area_id').hide();
        }
    });


    // add to cart
    $(".add-to-cart").click(function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var representation = $(this).data('representation');
        var type = $(this).data('type');

        axios.post('/addToCart', {
            "id" : id,
            "representation" : representation,
            "type" : type,
        }).then(response => {
            if (response.data.operator){
                $('#cart-count').text(parseInt($('#cart-count').text()) + 1)
                iziToast.success({
                    rtl: true,
                    titleColor: '#1f1f1f',
                    messageColor: '#000',
                    title: '',
                    message: 'به سبد خرید اضافه گردید',
                });
                window.location = 'http://lalecard.ir/cart'
            } else{
                iziToast.warning({
                    rtl: true,
                    titleColor: '#1f1f1f',
                    messageColor: '#000',
                    title: '',
                    message: 'در سبد خرید شما ثبت شده است!',
                });
            }
        }).catch(error => {
            iziToast.error({
                rtl: true,
                titleColor: '#1f1f1f',
                messageColor: '#000',
                title: '',
                message: 'خطایی رخ داد مجدد تلاش کنید!',
            });
        })
    });
    // delete cart
    $(".delete_cart").click(function (e) {
        var id = $(this).data('id');
        var type = $(this).data('type');

        axios.post('/deleteCart', {
            "id" : id,
            "type" : type,
        }).then(response => {
            if (response.data.operator){

                window.location.reload();

            }
        }).catch(error => {
            iziToast.error({
                rtl: true,
                titleColor: '#1f1f1f',
                messageColor: '#000',
                title: '',
                message: 'خطایی رخ داد مجدد تلاش کنید!',
            });
        })

    });
});
