$(document).ready(function () {

    fetchData(1);

    var first_load=true;

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                //fetch content and render here
                if (!first_load){
                    fetchData(page);
                }
                first_load=false
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/totalFactorItemInstallment/filter?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                $('#loading').css('display' ,'none');
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();

            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                order:[[0,"desc"]],
                columns: [
                    { "data": "sort_by" },
                    { "data": "name" },
                    { "data": "family" },
                    { "data": "phone" },
                    { "data": "price" },
                    { "data": "date" },
                    {
                        "targets": 2,
                        "data": data,
                        "render": function ( data ) {
                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                            <a href="../../dashboard/totalFactorItem/${data.total_factor_item_id}/installment" target="_blank">
                            <button type="button" class="dropdown-item pointer">مشاهده اقساط</button></a>
                            </div></div>`;
                        }
                    },
                ],
            });


        table.on('order.dt search.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }

    $("form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            type: "GET",
            url: "/dashboard/totalFactorItemInstallment/filter",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            createTable(data);
        });

        event.preventDefault();
    });
});
