$(document).ready(function () {

    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    /*    if checkbox of transfer checked for all requests   */



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

    $("body").on('click', '.contact_delete', function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        $('#excel').prop('checked', false);
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
                    $.ajax({
                        url: "/dashboard/contact/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": token,
                        },
                        success: function (data, page) {
                            if (data.status == 'success') {
                                fetchData(page)
                                swal({
                                    text: "پیام با موفقیت حذف گردید .",
                                    icon: "success",
                                    button: "تایید",
                                    timer: 3000,
                                    allowOutsideClick: true
                                });
                            } else {
                                swal("خطا !", data.message, 'error')
                            }
                        }
                    });
                }
            });
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
        $('#loading').css('display' , 'block');
        var form = $('#filter_form');
        var formData = form.serialize();
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/contact/" + basicUrl+"?page=" + page,

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
                {"data":"name"},
                {"data":"phone"},
                {"data":"text"},
                {"data":"card"},
                {"data":"created_at"},
                {"data":data,
                  "render":function (data){
                    switch (data.status) {
                        case 0:
                            return `<span class="badge badge-danger">جدید</span>`;
                            break;
                        case 1:
                            return `<span class="badge badge-dark">خوانده شده</span>`;
                            break;
                        case 2:
                            return `<span class="badge badge-warning">تعلیق شده</span>`;
                            break;
                        default:
                            return data.status;
                    }
                  }
                },
                {
                    "data": data,
                    "render": function (data) {
                        let display = 'none';
                        if ($('#delete').val() == 1) {
                            display = 'block';
                        }
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                             <a href="/dashboard/contact/${data.id}/edit"><span class="color-edit dropdown-item pointer">ویرایش</span></a>
                             <button style="display: ${display}"  data-id="${data.id}" class="contact_delete dropdown-item pointer">حذف</button>
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

