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
        var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: "/dashboard/factorItemProduct/"+basicUrl+"?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ( جمع کل صفحه : ' + data.meta.sum_price + ' ریال - جمع کل : ' + data.meta.total_price + ' ریال) ';

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
                {"data": "id"},
                {
                    "targets": 2,
                    "data": "image",
                    "render": function ( data, type, row, meta ) {
                        return '<img src="'+data+'" class="card-img-top" height="100px" weight="100px">';
                    }
                },
                {"data": "name"},
                {"data": "product_code"},
                {"data": "diversity_code"},
                {"data": "product_size"},
                {"data": "seller_code"},
                {"data": "order_count"},
                {"data": "stock"},
                {"data": "created_at"},
                {"data": "sender_date"},
                {
                    "targets": 2,
                    "data": "id",
                    "render": function ( data, type, row, meta ) {
                        return '<div data-v-15965e3b="" class="dropdown"><a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a><div class="dropdown-menu"><a href="../../dashboard/totalFactorItem/'+data+'" target="_blank"><button type="button" class="dropdown-item pointer"> جزئیات سفارش</button></a></div></div>';
                    }
                },
            ],
        });

        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }
    $("form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        var basicUrl = document.getElementById('url').value;

        $.ajax({
            type: "GET",
            url: "/dashboard/factorItemProduct/"+basicUrl,
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
