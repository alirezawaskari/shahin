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
            url: "/dashboard/site/color/filter?page=" + page,
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
                {"data":"title"},
                {"data":data,
                "render":function (data){
                    return `<i class="fa fa-square" style="color: ${data.color_palette};font-size: 20pt"
                              aria-hidden="true"></i>`
                }},
                {"data":"color_palette"},
                {"data":data,
                 "render":function (data){
                    if (data.status==1){
                        return `<i class="fa fa-check" aria-hidden="true"></i>`;
                    }else {
                        return `<i class="fa fa-close" aria-hidden="true"></i>`;
                    }
                 }
                },
                {
                    "data": data,
                    "render": function (data) {
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                             <a href="/dashboard/site/color/${data.id}/edit"><span  data-id="${data.id}" class="color-edit dropdown-item pointer">ویرایش</span></a>
                            <span  data-id="${data.id}" class="color-delete dropdown-item pointer">حذف</span>
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
    /*  delete color   */
    $("body").on('click', '.color-delete', function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
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
                            url: "/dashboard/site/color/" + id,
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

    });
});

