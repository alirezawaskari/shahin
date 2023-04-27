
$(document).ready(function () {



    $('#select_status').select2({
        placeholder: "انتخاب وضعیت"
    });


    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    /*    if checkbox of transfer checked for all requests   */
    $('#transfer_checkbox').change(function () {
        if ($(this).is(':checked')) {
            $('body td input:checkbox').prop('checked', this.checked);
        } else {
            $('body td input:checkbox').prop('checked', false);
        }
    });

    /*    transfer box changes */
    $("#transfer").change(function () {
        switch ($(this).val()) {
            case 'default':
                $('#transfer_start_date_box').hide()
                $('#transfer_end_date_box').hide()
                $('#destination_box').show()
                $('#count_box').show()
                break;
            case 'date':
                $('#transfer_start_date_box').show()
                $('#transfer_end_date_box').show()
                $('#destination_box').show()
                $('#count_box').show()
                break;
            case 'select':
                $('#transfer_start_date_box').hide()
                $('#transfer_end_date_box').hide()
                $('#destination_box').hide()
                $('#count_box').hide()
                break;
            default:
            // code block
        }
    });

    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#exel').prop('checked') == true) {
            $('#filter_form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });
    /*    click on do transfer */
    $("body").on('click', '#do_transfer', function (event) {
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#transfer_form');
        var formData = form.serialize();
        $.ajax(
            {
                url: "/dashboard/task-transfer",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
                    console.log(response)
                    if (response.status == 'success') {
                        swal(response.messages, {
                            icon: "success",
                            button: "باشه"
                        });
                        fetchData(1);
                    } else {
                        $('.transfer_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                        });
                    }
                }
            });
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
        $('#loading').css('display' , 'block');
        var form = $('#filter_form');
        var formData = form.serialize();
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/callcenter/" + basicUrl+"?page=" + page,
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
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        return `<input type="checkbox" name="id[]" value="${data.id}">`
                    }
                },
                {"data": "name.full_name"},
                {"data": "province.province"},
                {"data": "phone"},
                {"data": "text"},
                {"data": "user"},
                {"data": "representation"},
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        switch (data.type) {
                            case 0:
                                return '<span class="badge badge-primary">سایت</span>'
                            case 1:
                                return '<span class="badge badge-warning">پیامک</span>'
                            case 2:
                                return '<span class="badge badge-success">تماس ورودی</span>'
                            case 3:
                                return '<span class="badge badge-primary">دیجیتال</span>'
                            case 4:
                                return '<span class="badge badge-primary">مراجعه حضوری</span>'

                        }
                    }
                },
                {"data": "service"},
                {
                    "data": data,
                    "render": function (data) {
                        return getBadgeOfCallCenterStatus(data.status)
                    }
                }, {"data": "date"},
                {
                    "data": data,
                    "render": function (data) {
                        return `<span class="badge badge-red">${data.name.vip}</span>`
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        return `<span class="badge badge-red">${data.name.sos}</span>`
                    }
                }, {
                    "data": data,
                    "render": function (data) {
                        let display='none';
                        if($('#delete').val() == 1){
                            display='block'
                        }
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                            <span class="dropdown-item pointer task-status" data-id="${data.id}" data-name="${data.name.name}"  data-family="${data.name.family}" data-province="${data.name.province_id}" data-city="${data.name.city_id}" data-phone="${data.phone}" data-sos="${data.name.sos}"
                            data-vip="${data.name.vip}" data-agent="${data.agent}" data-status="${data.status}"  data-toggle="modal" data-target=".bd-example-modal-lg">وضعیت</span>
                            <span style="display: ${display}"  data-id="${data.id}" class="task-delete dropdown-item pointer">حذف</span>
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

    /*  delete task   */
    $("body").on('click', '.task-delete', function () {
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
                            url: "/dashboard/callcenter/" + id,
                            type: 'DELETE',
                            data: {
                                "_token": token
                            },
                            success: function (data) {
                                if (data.status == 'success') {
                                    $("#do_filter").click();
                                    swal({
                                        icon: "success",
                                        text: data.message,
                                    });
                                }
                                else
                                {
                                    swal({
                                        icon: "error",
                                        text: data.message,
                                    });
                                }
                            }
                        });
                }
            });
        }
    });
    /*      MODAL PART (STATUS BTN)     */
    $("body").on('click', '.task-status', function () {
        //set send link form data
         $('#send-factor').prop('checked', true);
        $('#remember-description').val('');
        $('#custom-check-vip').prop( "checked", false );
        $('#custom-check-sos').prop( "checked", false );
        $("#reason-remember").prop("selectedIndex", 0).val();
        $('#phone').attr('value', $(this).data('phone'));
        if ( $(this).data('vip')=='VIP'){
            $('#custom-check-vip').prop( "checked", true );
            $('#vipCheck').prop( "checked", true );
        }
        if ( $(this).data('sos')=='SOS'){
            $('#custom-check-sos').prop( "checked", true );
            $('#sosCheck').prop( "checked", true );
        }
        $('.modal_status_message_box').empty();
        $('#sendLinkForm').append(`<input type="hidden" name="task" value="${$(this).data('id')}">`);
        $('#phone').prop('disabled', true);
        $('#sendLinkForm').append(`<input type="hidden" name="phone" value="${$(this).data('phone')}">`);
        $('#name').attr('value', $(this).data('name'));
        $('#family').attr('value', $(this).data('family'));
        $("#province").val($(this).data('province'));
        $("#cities").val($(this).data('city')).change();
        $('#exampleModalLabel').empty();
        let status_badge= getBadgeOfCallCenterStatus($(this).data('status'))
        $('#exampleModalLabel').append(`
                               <div class="col-md-3">
                                شماره موبایل:
                                 <span>${$(this).data('phone')}</span>
                                </div>
                               <div class="col-md-3">
                             نام و نام خانوادگی :
                            <span>${$(this).data('name')} ${$(this).data('family')}</span>

                                </div>
                               <div class="col-md-2">
                                vip & sos :
                                <span id="type-badge">
                                   <span class="badge badge-red">${$(this).data('vip')}</span>
                                <span class="badge badge-red">${$(this).data('sos')}</span>
                                </span>

                                </div>
                               <div class="col-md-2">
                                 بازاریاب :
                            <span>${$(this).data('agent')}</span>
                                </div>
                                 <div class="col-md-2">
                                    وضعیت :
                            <span>${status_badge}</span>
                                </div>
                                <hr>
                     `);

        var token = $('meta[name="csrf-token"]').attr('content');
        var task_id = $(this).data('id');
        $.ajax(
            {
                url: "/dashboard/task-detail",
                type: 'post',
                data: {
                    "_token": token,
                    "id": task_id
                },
                success: function (response) {
                    fillSendLinkTasksTableBody(response.data.tasks)
                    $('.history-tbody').empty()
                    let i =1;
                    $.each(response.data.histories, function (index, value) {

                        $('.history-tbody').append(`
                        <tr>

                            <td>
                                ${i++}
                            </td>
                            <td>
                                ${value.user}
                            </td>
                            <td>
                                ${value.callcenter}
                            </td>
                            <td>
                                ${value.date}
                            </td>
                            <td>
                                ${value.description}
                            </td>
                        </tr>
                        `);
                    });
                    $('.factors-tbody').empty()
                    let number_item =1;
                    $.each(response.data.factors, function (index, value) {
                        $('.factors-tbody').append(`
                        <tr>
                            <td>
                                ${number_item++}
                            </td>
                            <td>
                                ${value.resnumber}
                            </td>
                            <td>
                                ${value.price}
                            </td>
                            <td>
                                ${value.type}
                            </td>
                            <td>
                                ${value.representation}
                            </td>
                            <td>
                                ${value.status}
                            </td>
                            <td>
                                ${value.date}
                            </td>
                            <td>
                                ${value.items}
                            </td>
                        </tr>
                        `);
                    });
                }
            });
        $(".task").val($(this).data('id'))

    })

    /*  submit status   in status modal*/
    $("body").on('click', '#submit-status', function () {
        $('.modal_status_message_box').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#modal-status-form');
        var formData = form.serialize();

        swal({
            title: "اطلاعات ارسال گردید !",
            timer: 2000
        });
        $.ajax(
            {
                url: "/dashboard/status-form",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
                    if (response.status == 'success') {
                        $('#type-badge').empty();
                        if ($('#custom-check-vip').is(':checked')) {
                            $('#type-badge').append(`
                            <span class="badge badge-danger">VIP</span>
                            `);
                        }
                        if ($('#custom-check-sos').is(':checked')) {
                            $('#type-badge').append(`
                            <span class="badge badge-danger">SOS</span>
                            `);
                        }
                        swal(response.messages, {
                            icon: "success",
                            button: "باشه"
                        });
                        fetchData(1);
                        $('.history-tbody').empty()
                        $.each(response.data.histories, function (index, value) {
                            $('.history-tbody').append(`
                        <tr>
                            <td>
                                ${value.user}
                            </td>
                            <td>
                                ${value.callcenter}
                            </td>
                            <td>
                                ${value.date}
                            </td>
                            <td>
                                ${value.description}
                            </td>
                        </tr>
                        `);
                        });
                    }
                    else if(response.status == 'error'){
                        swal( {
                            icon : "error",
                            title :response.messages,
                            button: "باشه"
                        });
                        $('.modal_status_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.modal_status_message_box').append(`<span class="mr-1 badge badge-danger">${val}</span>`);
                        });
                    } else {
                        swal( {
                            icon : "error",
                            title :"خطایی رخ داده است , لطفا مقادیر ارسالی را بررسی نمایید !",
                            button: "باشه"
                        });
                        $('.modal_status_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.modal_status_message_box').append(`<span class="mr-1 badge badge-danger">${val}</span>`);
                        });
                    }
                }
            });

    })
    /*  submit status   in status modal*/
    $("body").on('click', '#submit-transfer', function () {
        $('.modal_transfer_message_box').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#modal-transfer-form');
        var formData = form.serialize();
        $.ajax(
            {
                url: "/dashboard/transfer-form",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
                    if (response.status == 'success') {
                        swal(response.messages, {
                            icon: "success",
                            button: "باشه"
                        });
                        fetchData(1);
                    } else {
                        $('.modal_transfer_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.modal_transfer_message_box').append(`<span class="badge badge-danger">${val}</span>`);
                        });
                    }
                }
            });
    })
    /*  submit status   in status modal*/
    $("body").on('click', '#submit-close-task', function () {
        $('.modal_close_task_message_box').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#modal-close-task-form');
        var formData = form.serialize();

        swal({
            title: "اطلاعات ارسال گردید !",
            timer: 2000
        });
        $.ajax(
            {
                url: "/dashboard/callcenter/closeTask",
                type: 'post',
                "_token": token,
                data: formData,
                success: function (response) {
                    if (response.status == 'success') {
                        $('#type-badge').empty();
                        swal(response.messages, {
                            icon: "success",
                            button: "باشه"
                        });
                        fetchData(1);
                        $('.history-tbody').empty()
                        $.each(response.data.histories, function (index, value) {
                            $('.history-tbody').append(`
                        <tr>
                            <td>
                                ${value.user}
                            </td>
                            <td>
                                ${value.callcenter}
                            </td>
                            <td>
                                ${value.date}
                            </td>
                            <td>
                                ${value.description}
                            </td>
                        </tr>
                        `);
                        });
                    }
                    else if (response.status == 'Unauthorized') {
                        swal( {
                            icon : "error",
                            title :response.messages,
                            button: "باشه"
                        });
                        $('.modal_close_task_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.modal_close_task_message_box').append(`<span class="mr-1 badge badge-danger">${val}</span>`);
                        });
                    }
                    else {
                        swal( {
                            icon : "error",
                            title :"خطایی رخ داده است , لطفا مقادیر ارسالی را بررسی نمایید !",
                            button: "باشه"
                        });
                        $('.modal_close_task_message_box').empty();
                        $.each(response.messages, function (i, val) {
                            $('.modal_close_task_message_box').append(`<span class="mr-1 badge badge-danger">${val}</span>`);
                        });
                    }
                }
            });

    })
    $(document).on('click', '#custom-check-vip', function() {
        if ($('#custom-check-vip').is(':checked')) {
            $('#custom-check-sos').prop('checked', false);
        }
    });
    $(document).on('click', '#custom-check-sos', function() {
        if ($('#custom-check-sos').is(':checked')) {
            $('#custom-check-vip').prop('checked', false);
        }
    });
    $(document).on('click', '#sosCheck', function() {
        if ($('#sosCheck').is(':checked')) {
            $('#vipCheck').prop('checked', false);
        }
    });
    $(document).on('click', '#vipCheck', function() {
        if ($('#vipCheck').is(':checked')) {
            $('#sosCheck').prop('checked', false);
        }
    });
});
function getBadgeOfCallCenterStatus(status){
    switch (status) {
        case 0:
            return '<span class="badge badge-red">جدید</span>'
        case 1:
            return '<span class="badge badge-primary">در انتظار پرداخت</span>'
        case 2:
            return '<span class="badge badge-warning">معلق </span>'
        case 3:
            return '<span class="badge badge-success">پرداخت شده </span>'
        case 4:
            return '<span class="badge badge-warning">نیاز به تماس </span>'
        case 5:
            return '<span class="badge badge-dark">بسته شده</span>'
        case 6:
            return '<span class="badge badge-red">در حال انقضا </span>'
        case 7:
            return '<span class="badge badge-red">عودت پرداخت</span>'
        case 8:
            return '<span class="badge badge-dark">بایگانی</span>'
        case 9:
            return '<span class="badge badge-dark">منقضی شده</span>'
        default:
            return ''
    }
}

