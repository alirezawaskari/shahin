catType($("input[name='cat_type']:checked").val());
function catType(catType) {
    if (catType == 'category') {
        $("#product_category").removeAttr("style").show();
        $("#category").removeAttr("style").show();
        $("#service_category").removeAttr("style").hide();
        $("#service").removeAttr("style").hide();
    } else {
        $("#product_category").removeAttr("style").hide();
        $("#category").removeAttr("style").hide();
        $("#service_category").removeAttr("style").show();
        $("#service").removeAttr("style").show();
    }
}

$(document).ready(function () {

    fetchData(1);

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                //fetch content and render here
                fetchData(page);
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/centerRequest/filter/?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });

    }

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
            order: [[0, "desc"]],
            columns: [
                { "data": "sort_by" },
                { "data": "subject" },
                { "data": "name" },
                { "data": "phone" },
                { "data": "province" },
                { "data": "category" },
                { "data": "type" },
                { "data": "user" },
                { "data": "created_at" },
                {
                    "targets": 2,
                    "data": "status",
                    "render": function (data) {
                        if (data == "جدید") {
                            return '<button class="badge badge-danger border-0">' + data + '</button><a h_blank">';
                        }
                        if (data == "فعال") {
                            return '<button class="badge badge-success border-0">' + data + '</button><a h_blank">';
                        }
                        if (data == "معلق") {
                            return '<button class="badge badge-warning border-0">' + data + '</button><a h_blank">';
                        }
                        if (data == "رد شده") {
                            return '<button class="badge badge-dark border-0">' + data + '</button><a h_blank">';
                        }
                    }
                },
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        return '<div data-v-15965e3b="" class="dropdown">' +
                            '<a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>' +
                            '<div class="dropdown-menu">' +
                            '<a href="../../dashboard/centerRequest/' + data.id + '/edit"><button type="button" class="dropdown-item pointer">ویرایش</button></a>' +
                            '<button  data-id="' + data.id + '" class="center_request_delete dropdown-item pointer">حذف</button>' +
                            '</div>' +
                            '</div>';
                    }
                },
            ],
        });
        //add column count
        table.on('order.dt search.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }

    /*    click on do filter */
    $("body").on('click', '#filter', function (event) {
        if ($('#exel').prop('checked') == true) {
            $('#form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            fetchData(1)
            event.preventDefault();
        }
    });

    $("body").on('click', '.center_request_delete', function () {
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
                    $.ajax(
                        {
                            url: "/dashboard/centerRequest/" + id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data, page) {
                                if (data.status == 'success') {
                                    fetchData(page)
                                    swal({
                                        text: " با موفقیت حذف گردید .",
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
});
