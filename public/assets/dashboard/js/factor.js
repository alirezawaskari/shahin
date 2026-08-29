$(document).ready(function () {

    $('#product_category').select2({
        placeholder: "دسته بندی کالا "
    });
    $('#province_id').select2();

    $('#service_items').select2({
        placeholder: "انتخاب نوع خدمت"
    });



    fetchData(1);
    var first_load=true;
    var do_filter_command=false;

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
            url: $('#filter_form').attr('action')+"/?page=" + page,
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
        if ($('#datatable-json').data("title") == 'deleted_list') {
        var table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: data.data,
            columns: [
                {"data": "sort_by"},
                {"data": "name"},
                {"data": "phone"},
                {"data": "price"},
                {"data": "delete_user"},
                {"data": "deleted_at"},
                {
                    "data": data,
                    "render": function (data) {
                        return `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                            <span data-id="${data.id}" class="factor-restore dropdown-item pointer">برگرداندن</span>
                        </div></div>`
                    }
                }
            ],
        });
        }
        else
        {
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                columns: [
                    {"data": "sort_by"},
                    ...(Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1 ? [{
                        "data": data,
                        "orderable": false,
                        "render": function (data) {
                            return `<input type="checkbox"
                                class="business-transfer-record factor-business-transfer-record"
                                value="${data.id}"
                                data-current-business-ids="${(data.current_business_ids || []).join(',')}">`;
                        }
                    }] : []),
                    {"data": "name"},
                    {"data": "phone"},
                    {"data": "user"},
                    {"data": "price"},
                    {"data": "representation"},
                    {"data": "items"},
                    {"data": "payment_type"},
                    {"data": "date"},
                    {
                        "data": data,
                        "render": function (data){
                            switch (data.status) {
                                case 0:
                                    return '<span class="badge badge-warning">در انتظار پرداخت</span> ';
                                case 1:
                                    return '<span class="badge badge-success">پرداخت شده</span> ';
                                case 2:
                                    return '<span class="badge badge-danger">عودت پرداخت</span>';
                                case 3:
                                    return '<span class="badge badge-danger">منقضی شده</span>';
                                case 4:
                                    return '<span class="badge badge-success">عودت واریزی</span>';
                                case 5:
                                    return '<span class="badge badge-danger">واریز شده</span>';
                                default:
                                    return ''
                            }
                        }
                    },
                    {
                        "data": data,
                        "render": function (data) {
                            let display='none';
                            if($('#delete').val() == 1){
                                display='block'
                            }
                            let show_factor_display='block';
                            let businessTransferAction = '';
                            if (Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1) {
                                businessTransferAction = `<button type="button" class="dropdown-item pointer"
                                    data-business-transfer-single data-transfer-entity="factor"
                                    data-record-id="${data.id}"
                                    data-current-business-ids="${(data.current_business_ids || []).join(',')}">
                                    انتقال به کسب و کار
                                </button>`;
                            }

                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                <span data-id="${data.resnumber}" class="factor-show dropdown-item pointer"  data-toggle="modal" data-target="#factorShowModal" >جزئیات</span>
                                <span class="factor-show dropdown-item pointer">
                                <a  style="display: ${show_factor_display}" target="_blank" href="/factor/${data.resnumber}" class="text-body">
                                    مشاهده فاکتور
                                 </a>
                                </span>
                                <span data-id="${data.resnumber}" class="factor-sendMe dropdown-item pointer">ارسال لینک به من</span>
                                ${businessTransferAction}
                                <span style="display: ${display}"  data-id="${data.resnumber}" class="factor-delete dropdown-item pointer">حذف</span>

                            </div></div>`
                        }
                    }
                ],
            });
        }
        //add column count
        table.on('order.dt search.dt', function () {
            table.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }


    $('body').on('change', '#delete-transfer-select',function () {
        if ($(this).val()=='transfer'){
            $('#callcenters-box').show();
        }else{
            $('#callcenters-box').hide();
        }
    });

    $('body').on('change','#title-pay',function() {
       if ($(this).val() != 'all'){
           $("#title").val('all');
       }
    });

    $('body').on('change','#title',function() {
       if ($(this).val() != 'all'){
           $("#title-pay").val('all');
       }
    });

// delete factor
$('body').on('click', '.factor-delete',function () {
    var resnumber = $(this).data("id");
    var token = $('meta[name="csrf-token"]').attr('content');
    swal({
        title:  `آیا مطمئن هستید؟`,
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
                    url: "/dashboard/factor/"+resnumber,
                    type: 'DELETE',
                    data: {
                        "_token": token,
                    },
                    success: function (response,page) {
                        if (response.status=='success'){
                            fetchData(page)
                            swal({
                                title: response.message,
                                icon: "success",
                                button: "باشه",
                            });
                        }else {
                            swal({
                                title: "خطا",
                                text: "خطایی رخ داده است . ",
                                icon: "warning",
                                dangerMode: true,
                            })
                        }
                    }
                });
        }
    });
});

//send me factor
$('body').on('click', '.factor-sendMe',function () {
    var resnumber = $(this).data("id");
    var token = $('meta[name="csrf-token"]').attr('content');
    $.ajax(
        {
            url: "/dashboard/factor/sendMe/"+resnumber,
            type: 'post',
            data: {
                "_token": token,
            },
            success: function (response,page) {
                if (response.status=='success'){
                    fetchData(page)
                    swal({
                        title: response.message,
                        icon: "success",
                        button: "باشه",
                    });
                }else {
                    swal({
                        title: "خطا",
                        text: "خطایی رخ داده است . ",
                        icon: "warning",
                        dangerMode: true,
                    })
                }
            }
        });
});

// restore factor
$('body').on('click', '.factor-restore',function () {
    var id = $(this).data("id");
    var token = $('meta[name="csrf-token"]').attr('content');
    swal({
        title:  `آیا مطمئن هستید؟`,
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
                    url: "/dashboard/factor/restore/",
                    type: 'post',
                    data: {
                        "_token": token,
                        "id": id
                     },
                    success: function (data,page){
                        if (data.status=='success'){
                            fetchData(page)
                            swal({
                                text: "تراکنش با موفقیت برگردانده شد .",
                                icon: "success",
                                button: "تایید",
                                timer:3000,
                                allowOutsideClick: true
                            });
                        }else{
                            swal("خطا !", data.message,'error')
                        }
                    }
                });
        }
    });
});
});

