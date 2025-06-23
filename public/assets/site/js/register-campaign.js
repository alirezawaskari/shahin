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
                url: "/register/check-exist-user",
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function (data) {
                console.log(data.data);
                $('#register_box').show();
                $('#check_mobile_box').hide();
                if (data.exist == true){
                    $('.phone').val(data.data.phone);
                    $('.name').val(data.data.name);
                    $('.family').val(data.data.family);
                    $('#gender').val(data.data.gender);
                    $('#province_id').val(data.data.province_id);
                    $('#city_id').val(data.data.city_id);
                }
            });
        }
        event.preventDefault();
    });
    $('#type_id').on('change', function () {
        var id = $(this).val();
        if (id == 0) {
            $('#service_id').find('option').remove();
        }
        if (id != 0) {
            $.ajax({
                url: '/type/category',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function success(data) {
                    if (data.count == 'available') {
                        $('#category_id').removeAttr('disabled');
                        $('#category_id').find('option').remove();
                        $('#category_id').append('<option value="">انتخاب دسته بندی</option>');
                        for (var i = 0; i < data.length; i++) {
                            $('#category_id').append('<option value="' + data.service[i]['id'] + '">' + data.service[i]['title'] + '</option>');
                        }
                        $('#category_id').append('<option value="more" class="description_register">سایر</option>');
                        $('.category_id').removeClass('d-none');
                        $('#register_description').addClass('d-none');
                    }else {
                        $('.service_id').addClass('d-none');
                        $('#register_description').removeClass('d-none');
                    }
                }
            });
        }
    });
    $('#category_id').on('change', function () {
        var id = $(this).val();
        if (id == 0) {
            $('#service_id').find('option').remove();
        }
        if (id != 0) {
            $.ajax({
                url: '/category/service',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function success(data) {
                    if (data.count == 'available') {
                        $('#service_id').removeAttr('disabled');
                        $('#service_id').find('option').remove();
                        $('#service_id').append('<option value="">انتخاب نوع خدمت</option>');
                        for (var i = 0; i < data.length; i++) {
                            $('#service_id').append('<option value="' + data.service[i]['id'] + '">' + data.service[i]['title'] + '</option>');
                        }
                        $('#service_id').removeAttr('disabled');
                        $('#service_id').append('<option value="more" class="description_register">سایر</option>');
                        $('.service_id').removeClass('d-none');
                        $('.service_item_id').removeClass('d-none');
                        $('#register_description').addClass('d-none');
                    }else {
                        $('.service_item_id').addClass('d-none');
                        $('.service_id').addClass('d-none');
                        $('#register_description').removeClass('d-none');
                    }
                }
            });
        }
    });
    $('#service_id').on('change', function () {
        var id = $(this).val();

        if (id == 0) {
            $('#service_item_id').find('option').remove();
        }

        if (id != 0) {
            $.ajax({
                url: '/service/item',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function success(data) {
                    if (data.count == 'available') {
                        $('#service_item_id').removeAttr('disabled');
                        $('#service_item_id').find('option').remove();
                        $('#service_item_id').append('<option value="">انتخاب آیتم</option>');
                        for (var i = 0; i < data.length; i++) {
                            $('#service_item_id').append('<option value="' + data.item[i]['id'] + '">' + data.item[i]['title'] + '</option>');
                        }
                        $('#service_item_id').append('<option value="more" id="description_register">سایر</option>');
                        $('.service_item_id').removeClass('d-none');
                        $('#register_description').addClass('d-none');
                    }
                    else{
                        $('.service_item_id').addClass('d-none');
                        $('#register_description').removeClass('d-none');
                    }
                }
            });
        }
    });
    $('#service_item_id').on('change', function () {
        var id = $(this).val();
        if (id === 'more') {
            $('#register_description').removeClass('d-none');
        }
    });
});
