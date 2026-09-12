/**
 * سفارشات کالا - the orders datatable.
 *
 * Same ajax/pagination shape as factor.js: the filter form's own action is the endpoint,
 * so adding a filter input never needs a change here.
 */
$(document).ready(function () {

    var first_load = true;
    var do_filter_command = false;

    $('#product_category').select2({
        placeholder: 'دسته بندی کالا'
    });

    fetchData(1);

    $('body').on('click', '#do_filter', function (event) {
        $('#pagination-demo').remove();
        $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
        do_filter_command = true;
        fetchData(1);
        event.preventDefault();
    });

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                if (!first_load && !do_filter_command) {
                    fetchData(page);
                }
                first_load = false;
                do_filter_command = false;
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');

        $.ajax({
            url: form.attr('action') + '/?page=' + page,
            data: form.serialize(),
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById('page_text').innerHTML =
            ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' سفارش ';
        $('#page_text').append('( جمع کل صفحه : ' + data.meta.sum_price + ' ریال - جمع کل : ' + data.meta.total_price + ' ریال )');

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
                ...(Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1 ? [{
                    "data": data,
                    "orderable": false,
                    "render": function (row) {
                        return `<input type="checkbox"
                            class="business-transfer-record product-order-business-transfer-record"
                            value="${row.id}"
                            data-current-business-ids="${(row.current_business_ids || []).join(',')}">`;
                    }
                }] : []),
                {"data": "resnumber"},
                {"data": "name"},
                {"data": "phone"},
                {"data": "products"},
                {"data": "products_count"},
                {"data": "price"},
                {"data": "shipping_cost"},
                {"data": "total_price"},
                {"data": "address"},
                {"data": "date"},
                {
                    "data": data,
                    "render": function (row) {
                        if (row.shipping_status === 1) {
                            return '<span class="badge badge-success">' + row.shipping_status_text + '</span>';
                        }
                        return '<span class="badge badge-warning">' + row.shipping_status_text + '</span>';
                    }
                },
                {
                    "data": data,
                    "render": function (row) {
                        return `<div class="dropdown">
                            <a href="#" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                                <a href="${row.show_url}" class="dropdown-item pointer">جزئیات سفارش</a>
                                <a href="/factor/${row.resnumber}" target="_blank" class="dropdown-item pointer">مشاهده فاکتور</a>
                            </div></div>`;
                    }
                }
            ],
        });

        //replace the first column with the running row number of the current page
        table.on('order.dt search.dt', function () {
            table.column(0, {search: 'applied', order: 'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }
});
