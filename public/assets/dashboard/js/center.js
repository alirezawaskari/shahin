catType($("input[name='cat_type']:checked").val());
function catType(catType) {
    if (catType == 'category') {
        $(".services-select").next(".select2").hide();
        $("#product_category").removeAttr("style").show();
        $("#category").removeAttr("style").show();
        $("#service_category_id1").removeAttr("style").hide();
        $("#service_id1").removeAttr("style").hide();
    } else {
        $(".services-select").next(".select2").show();
        $("#product_category").removeAttr("style").hide();
        $("#category").removeAttr("style").hide();
        $("#service_category_id1").removeAttr("style").show();
        $("#service_id1").removeAttr("style").show();
    }
}

$(document).ready(function () {
    $('#service-type').click();
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
    $('#loading').removeClass('d-none').addClass('d-flex');

    var form = $('#form');
    var formData = form.serialize();

    $.ajax({
        url: "/dashboard/center/filter/?page=" + page,
        data: formData,
        success: function (data) {
            createTable(data);
        },
        complete: function () {
            $('#loading').removeClass('d-flex').addClass('d-none');
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
                { "data": "title" },
                { "data": "items" },
                { "data": "creator_rpresentation" },
                { "data": "created_at" },
                {
                    "targets": 2,
                    "data": "status",
                    "render": function (data) {
                        return data == 1 ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>'
                    }
                },
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        let delete_display = 'none';
                        if ($('#delete').val() == 1) {
                            delete_display = 'block';
                        } let edit_display = 'none';
                        if ($('#edit').val() == 1) {
                            edit_display = 'block';
                        } let financial_report_detail_display = 'none';
                        if ($('#financial_report_detail').val() == 1) {
                            financial_report_detail_display = 'block';
                        }
                        let services = data.services || [];
                        let serviceLinks = services.map(function (element) {
                            return '<a href="../../dashboard/center/' + data.id + '/' + element.slug + '/serviceTariff">' +
                                '<button type="button" class="dropdown-item pointer">' + element.title + '</button></a>';
                        }).join('');

                        return '<div data-v-15965e3b="" class="dropdown">' +
                            '<a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>' +
                            '<div class="dropdown-menu">' +
                            serviceLinks +
                            '<a style="display: ' + edit_display + '" href="../../dashboard/center/' + data.id + '/edit"><button type="button" class="dropdown-item pointer">ویرایش</button></a>' +
                            '<a style="display: ' + financial_report_detail_display + '" href="../../dashboard/center/financialReport/' + data.id + '"><button type="button" class="dropdown-item pointer">عملکرد مالی</button></a>' +
                            '<button style="display: ' + delete_display + '" data-id="' + data.id + '" class="center_delete dropdown-item pointer">حذف</button>' +
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

    $("body").on('click', '.center_delete', function () {
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
                            url: "/dashboard/center/" + id,
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
