$(document).ready(function () {

    $('.check-date').persianDatepicker({
        observer: true,
        format: 'YYYY/MM/DD',
        initialValueType: 'gregorian'
    });

    fetchData(1);
    var first_load=true;
    var do_filter_command=false;

    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#excel').prop('checked') == true) {
            $('#filter_form').submit();
        } else {
			$('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });




    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                //fetch content and render here
                if (!first_load && !do_filter_command){
                    fetchData(page);
                }
                first_load=false
                do_filter_command=false
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();
        // var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: $('#filter_form').attr('action')+"?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });
    }

    /*  create table   */
    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');
        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();

        var table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: data.data,
            columns: [
                {"data": "sort_by"},
                {"data": "number_amount"},
                {"data": "jalali_date"},
                {"data": data,
                "render":function (data){
                    return `${data.applicant_fullname}<br>${data.applicant_phone}`

                }},
                {"data": data,
                    "render":function (data){
                        return `${data.exporter_fullname}<br>${data.exporter_phone}`
                    }},
                {"data": data,
                    "render":function (data){
                        return `${data.pay_to_fullname}<br>${data.pay_to_phone}`

                    }},
                {"data": "serial_code"},
                {
                    "data": data,
                    "render": function (data){
                        switch (data.title) {
                            case 'installment':
                                return '<span class="badge badge-primary">قسط</span> ';
                            case 'guaranty':
                                return '<span class="badge badge-info">ضمانت</span> ';
                            case 'prepayment':
                                return '<span class="badge badge-purple">پیش پرداخت</span> ';
                            default:
                                return ''
                        }
                    }
                },
                {"data":data,
                    "render":function (data){
                        if (data.front_image!=''){
                            return  `<a href="${data.front_image}" target="_blank"> <img src="${data.front_image}" width="70px" alt=""></a>`
                        }
                        if (data.back_image!='') {
                            return  `<a href="${data.back_image}" target="_blank"> <img src="${data.back_image}" width="70px" alt=""></a>`
                        }
                        return '';
                    }
                },
                {"data": "description"},
                {"data": data,
                    "render" :function (data){
                        if (data.asset_transaction == null){
                            return 'لاله'
                        }else {
                            return  ` ${data.asset_transaction.receiver_name}<br>${data.asset_transaction.receiver_phone} `
                        }
                    }
                },

                {
                    "data": data,
                    "render": function (data){
                        switch (data.status) {
                            case 0:
                                return '<span class="badge badge-warning">در انتظار وصول</span> ';
                            case 1:
                                return '<span class="badge badge-success">وصول شده</span> ';
                            case 2:
                                return '<span class="badge badge-danger">برگشت خورده</span>';
                            case 3:
                                return '<span class="badge badge-info">عودت داده شده</span>';
                            default:
                                return ''
                        }
                    }
                },

                {
                    "data": data,
                    "render": function (data) {
                        let status_display='none';
                        if($('#can-update-status-check').val()==1){
                            status_display='block';
                        }
                        return `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                             <a href="../../../dashboard/totalFactorItem/${data.total_factor_item_id}" target="_blank">
                            <button type="button" class="dropdown-item pointer">بازدید پرونده</button></a>
                            <button style="display: ${status_display}" type="button" class="dropdown-item pointer list-check-status" data-check_id="${data.id}" data-front_image="${data.front_image}" data-toggle="modal" data-target="#statusCheckModal">وضعیت</button>
                        </div></div>`
                    }
                }
            ],
        });
        //add column count
        table.on('order.dt search.dt', function () {
            table.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }


});
//---------------------on change status -------------------

$("#check-change-status").on("change", function () {
    if($(this).val()==11){
        $('#receiver-box').slideDown();
    }else{
        $('#receiver-box').slideUp();
    }
});

//--------------------SELECT PROVINCE OF EXPORTER--------------------
$('#exporter_province_id').on('change', function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var id = $(this).val();
    $.ajax({
        url: '/city/select',
        type: 'post',
        dataType: 'json',
        data: {
            "_token": token,
            "id": id,
        },
        success: function (data) {
            $('#exporter_city_id')
                .empty()
                .append('<option value="all">انتخاب شهر</option>')
            ;
            $.each(data.cities, function (i, d) {
                $('#exporter_city_id').append('<option value="' + d.id + '">' + d.name + '</option>');
            });
        }
    });
});

$(document).ready(function () {
    //----------------------store check ---------------
    $("body").on('click', '#submit-check-btn', function () {
        $('#check-store-error-box').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var check_form_data = new FormData();
        var front_image= $('#check-front-image').prop('files')[0]
        var back_image= $('#check-back-image').prop('files')[0]
        if (front_image){
            check_form_data.append('check_front_image', front_image);
        }
        if (back_image){
            check_form_data.append('check_back_image', back_image);
        }
        check_form_data.append('amount', $('#check-amount').val());
        //---exporter user data ---
        check_form_data.append('exporter_id', $('#exporter-id').val());
        check_form_data.append('exporter_phone', $('#check-exporter-phone').val());
        check_form_data.append('exporter_name', $('#exporter_name').val());
        check_form_data.append('exporter_family', $('#exporter-family').val());
        check_form_data.append('exporter_gender', $('.exporter_gender').val());
        check_form_data.append('exporter_province_id', $('#exporter_province_id').val());
        check_form_data.append('exporter_city_id', $('#exporter_city_id').val());
        //---pay to user data ---
        check_form_data.append('pay_to_id', $('#pay-to-id').val());
        check_form_data.append('pay_to_phone', $('#check-payto-phone').val());
        check_form_data.append('pay_to_name', $('#pay_to_name').val());
        check_form_data.append('pay_to_family', $('#pay_to_family').val())
        check_form_data.append('pay_to_gender', $('.pay_to_gender').val())
        check_form_data.append('pay_toprovince_id', $('#province_id').val());
        check_form_data.append('pay_to_city', $('#city_id').val());
        //--- other data of store check form ---
        check_form_data.append('date', $('#check-date').val());
        check_form_data.append('description', $('#check-description').val());
        // check_form_data.append('exporter', $('#check-exporter').val());
        // check_form_data.append('pay_to', $('#check-pay-to').val());
        check_form_data.append('total_factor_item_id', $('#total-factor-item-id').val());
        check_form_data.append('title', $('#check-title').val());
        check_form_data.append('serial_code', $('#serial-code').val());
        check_form_data.append('applicant_id', $('#applicant-id').val());
        $.ajax(
            {
                type: "post",
                url: '/dashboard/check',
                data: check_form_data,
                contentType: false,
                processData: false,
                "_token": token,
                headers: {
                    "Authorization": localStorage.getItem('token')
                },
                success: function (response) {
                    $('#submit-check-btn').show()

                    if (response.status=='error'){
                        swal("خطایی رخ داده! ", "لطفا به کادر نارنجی رنگ توجه نمایید !", "error");
                        $('#check-store-error-box').empty();
                        $('#check-store-error-box').append(`<div class="col-md-1 m-2 "> خطا :  </div>`);
                        $.each(response.messages, function (i, val) {
                            $('#check-store-error-box').append(`<div class="col-md-12 alert alert-warning">${val}</div>`);
                        });
                    }else{
                        $("#storeCheckModal").modal('hide');
                        swal("عملیات موفق! ", "چک با موفقیت ثبت گردید !", "success");
                         location.reload();
                    }
                }
            });
    })
    // ---------------------end store check -------------
    //----------------------add new check btn -------------
    $("body").on('click','#add-new-check-btn',function (){
        $('#check-store-error-box').empty();
    })
    // -------------------end add new check btn --------------
    //----------------------status check btn -------------
    $("body").on('click','.list-check-status',function (){
        var status_front_image=$(this).data('front_image')
        $('#check-id').val($(this).data('check_id'))
        $('#status-check-front-image').empty();
        $('#check-update-error-box').empty();
        $('#status-check-front-image').append(
            `<img src="${status_front_image}" alt="." style="width: 100%">`
        );
    })
    // -------------------end status check btn --------------
    // -------------------edit check btn --------------
    $("body").on('click','#edit-check-btn',function (){
        $('#check-update-error-box').empty();
        var check_tag=$(this)
        $('#edit-check-amount').val(  check_tag.data('amount')  )
        $('#edit-check-date').val(check_tag.data('jdate'))
        $('#edit-check-exporter').val(check_tag.data('exporter'))
        $('#edit-check-applicant').val(check_tag.data('applicant'))
        $('#edit-check-pay-to').val(check_tag.data('pay_to'))
        $('#edit-check-description').val(check_tag.data('description'))
        $('#edit-check-id').val(check_tag.data('check_id'))
        $('#edit-serial-code').val(check_tag.data('serial_code'))
        $("#edit-check-title").val(check_tag.data('title')).change();
    })
    //---------------------end edit check btn ---------------------
    //---------------------update check ---------------------
    $("body").on('click', '#update-check-btn', function () {
        $('#check-store-error-box').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var check_form_data = new FormData();
        var front_image= $('#edit-check-front-image').prop('files')[0]
        var back_image= $('#edit-check-back-image').prop('files')[0]
        if (front_image){
            check_form_data.append('check_front_image', front_image);
        }
        if (back_image){
            check_form_data.append('check_back_image', back_image);
        }
        check_form_data.append('amount', $('#edit-check-amount').val());
        check_form_data.append('date', $('#edit-check-date').val());
        check_form_data.append('description', $('#edit-check-description').val());
        check_form_data.append('exporter', $('#edit-check-exporter').val());
        check_form_data.append('pay_to', $('#edit-check-pay-to').val());
        check_form_data.append('title', $('#edit-check-title').val());
        check_form_data.append('serial_code', $('#edit-serial-code').val());

        $.ajax(
            {
                type : 'post',
                url: '/dashboard/check/update/'+$('#edit-check-id').val(),
                data: check_form_data,
                contentType: false,
                processData: false,
                "_token": token,
                headers: {
                    "Authorization": localStorage.getItem('token')
                },
                success: function (response) {
                    if (response.status=='error'){
                        swal("خطایی رخ داده! ", "لطفا به کادر نارنجی رنگ توجه نمایید !", "error");
                        $('#check-update-error-box').empty();
                        $('#check-update-error-box').append(`<div class="col-md-1 m-2 "> خطا :  </div>`);
                        $.each(response.messages, function (i, val) {
                            $('#check-update-error-box').append(`<div class="col-md-12 alert alert-warning">${val}</div>`);
                        });
                    }else{
                          location.reload();
                    }
                }
            });
    })
  //-----------------------end update check----------
  //---------------------update check status -----------
    $("body").on('click', '#update-check-status-btn', function () {
        swal({
            title: "آیا مطمئن هستید؟",
            text: "",
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res) {
                $('#check-update-status-error-box').empty();//develop
                var token = $('meta[name="csrf-token"]').attr('content');
                var form = $('#status-check-form');
                var formData = form.serialize();
                $.ajax(
                    {
                        type : 'post',
                        url: '/dashboard/check/update-status',
                        data: formData,
                        "_token": token,
                        success: function (response) {
                            if (response.status=='error'){
                                swal("خطایی رخ داده! ", "لطفا به کادر نارنجی رنگ توجه نمایید !", "error");
                                $('#check-update-status-error-box').empty();
                                $('#check-update-status-error-box').append(`<div class="col-md-1 m-2 "> خطا :  </div>`);
                                $.each(response.messages, function (i, val) {
                                    $('#check-update-status-error-box').append(`<div class="col-md-12 alert alert-warning">${val}</div>`);
                                });
                            }else{
                                location.reload();
                            }
                        }
                    });
            }})

    })
    //-----------------------end update check status ----------
    // ----------------------remove check ---------------------

    $("body").on('click','.delete-check',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        submit();
        function submit(event) {
            swal({
                title: "آیا مطمئن هستید؟",
                text: "",
                icon: "warning",
                buttons: {
                    confirm: 'بله',
                    cancel: 'خیر'
                },
                dangerMode: true
            }).then(res => {
                if (res) {
                    $.ajax(
                        {
                            url: "/dashboard/check/"+id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data,page){
                                if (data.status=='success'){
                                    swal({
                                        text: "چک با موفقیت حذف گردید .",
                                        icon: "success",
                                        button: "تایید",
                                        timer:3000,
                                        allowOutsideClick: true
                                    });
                                     location.reload();
                                }else{
                                    swal("خطا !", data.message,'error')
                                }
                            }
                        });
                }
            });
        }
    });
    // --------------------end delete check------------------
    $('#add-new-check-btn').on('click',function (){
        searchUser( $("#check-exporter-phone").val(),'exporter')
        searchUser( $("#check-payto-phone").val(),'pay_to')
    });

})

//---------------------fetch user -----------------------
function searchUser(search,type) {
    if (search.length === 11) {
        $('.loading_send_link').css('display', 'block');
        $.ajax({
            type: "GET",
            url: "/dashboard/callcenter/fetchUser",
            data: {
                phone: search,
            }
        }).done(function (data) {
            if (type == 'exporter'){
                $(".exporter_name").attr('value', data.data.user.name ? data.data.user.name : '');
                $(".exporter_id").attr('value', data.data.user.id ? data.data.user.id : '');
                $(".exporter_family").attr('value', data.data.user.family ? data.data.user.family : '');
                $(".exporter_province_id").val(data.data.user.province_id).change();
                  data.data.user.sex == 1?$("#exporter-female-gender").prop('checked', true):$("#exporter-male-gender").prop('checked', true);
                setTimeout(function(){
                    $(".exporter_city_id").val(data.data.user.city_id).change();
                }, 2000);


                if (!data.data.user.name) {
                    //clear value of exporter_id
                     $(".exporter_id").attr('value','');
                     //refresh city
                     $("#exporter_city_id").empty();
                    $('.exporter_error_user').text('کاربری با این شماره تماس ثبت نام نشده است لطفا فیلدهای مربوط به کاربر را پر کنید.');
                } else {
                    $('.exporter_error_user').text('');
                }
            }else if (type== 'pay_to'){
                $(".pay_to_name").attr('value', data.data.user.name ? data.data.user.name : '');
                $(".pay_to_id").attr('value', data.data.user.id ? data.data.user.id : '');
                $(".pay_to_family").attr('value', data.data.user.family ? data.data.user.family : '');
                $(".pay_to_province_id").val(data.data.user.province_id).change();
                 data.data.user.sex == 1?$("#pay-to-female-gender").prop('checked', true):$("#pay-to-male-gender").prop('checked', true);
                setTimeout(function(){
                    $(".pay_to_city_id").val(data.data.user.city_id).change();
                }, 1000);

                if (!data.data.user.name) {
                    //clear value of exporter_id
                     $(".pay_to_id").attr('value','');
                    //refresh city
                    $("#city_id").empty();
                    $('.pay_to_error_user').text('کاربری با این شماره تماس ثبت نام نشده است لطفا فیلدهای مربوط به کاربر را پر کنید.');
                } else {
                    $('.pay_to_error_user').text('');
                }
            }else if (type== 'receiver'){
                $(".receiver_name").attr('value', data.data.user.name ? data.data.user.name : '');
                $(".receiver_id").attr('value', data.data.user.id ? data.data.user.id : '');
                $(".receiver_family").attr('value', data.data.user.family ? data.data.user.family : '');
                data.data.user.sex == 1?$("#receiver-female-gender").prop('checked', true):$("#receiver-male-gender").prop('checked', true);
                if (!data.data.user.name) {
                    //clear value of exporter_id
                     $(".receiver_id").attr('value','');
                    $('.receiver_error_user').text('کاربری با این شماره تماس ثبت نام نشده است لطفا فیلدهای مربوط به کاربر را پر کنید.');
                } else {
                    $('.receiver_error_user').text('');
                }
            }
        });
    }
}
