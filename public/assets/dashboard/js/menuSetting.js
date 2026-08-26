$(document).ready(function () {

    var currentPage = 1;

    fetchData(1);

    // 🔎 Filter button click
    $("body").on('click', '#do_filter', function (event) {

        if ($('#excel').prop('checked')) {
            $('#filter_form').submit();
            return;
        }

        event.preventDefault();

        fetchData(1);
    });

    // Enter inside the filter box should filter rather than reload the page.
    $("body").on('submit', '#filter_form', function (event) {
        event.preventDefault();
        fetchData(1);
    });


    // 📄 Pagination
    //
    // twbs-pagination fires a `page` event for its start page as soon as it is built, and
    // rebuilding it here after every fetch used to send that straight back into fetchData.
    // Clicking page 2 therefore loaded page 2, rebuilt the widget, and was immediately
    // dragged back to page 1 — in a loop. Two things stop that: the widget starts on the
    // page actually being shown, and it is told not to click it.
    function pagination(totalPages, page) {

        var $pagination = $('#pagination-demo');

        if ($pagination.data("twbs-pagination")) {
            $pagination.twbsPagination('destroy');
        }

        if (!totalPages || totalPages < 1) {
            $pagination.empty();
            return;
        }

        $pagination.twbsPagination({
            totalPages: totalPages,
            startPage: Math.min(page, totalPages),
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            initiateStartPageClick: false,
            next: '>',
            prev: '<',

            onPageClick: function (event, clickedPage) {
                if (clickedPage !== currentPage) {
                    fetchData(clickedPage);
                }
            }
        });
    }


    // 📦 Fetch Data
    function fetchData(page) {

        $('#loading').removeClass('d-none').show();

        currentPage = page;

        $.ajax({
            url: "/dashboard/menuSetting/filter",
            type: "GET",
            data: $('#filter_form').serialize() + "&page=" + page,

            success: function (response) {
                createTable(response, page);
            },

            error: function () {
                alert("Failed to load data");
            },

            complete: function () {
                $('#loading').hide();
            }
        });
    }


    // 📊 Build Table
    function createTable(response, page) {

        pagination(response.meta.last_page, page);

        $('#page_text').html(
            response.meta.total
                ? `نمایش ${response.meta.from} تا ${response.meta.to} از ${response.meta.total} آیتم`
                : 'موردی یافت نشد'
        );

        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();

        var table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            ordering: false,
            retrieve: true,
            data: response.data,
            language: { emptyTable: 'موردی یافت نشد' },

            columns: [
                {
                    // Numbered here rather than from an order.dt handler: ordering is off, so
                    // that event never fires and the cell was left rendering the row object.
                    data: null,
                    render: function (data, type, row, meta) {
                        return (response.meta.from || 1) + meta.row;
                    }
                },

                { data: "name" },

                {
                    data: "icon",
                    render: function (data) {
                        return data ? `<i class="icon ${data}"></i>` : '<span class="text-muted">—</span>';
                    }
                },

                {
                    data: "position",
                    render: function (data) {
                        return data ?? 0;
                    }
                },

                {
                    data: null,
                    render: function (data, type, row) {
                        return `<span title="زیرمنوهای نمایش داده شده از کل">${row.menu_items_count} / ${row.permissions_count}</span>`;
                    }
                },

                {
                    data: "is_active",
                    render: function (data, type, row) {
                        var status = data
                            ? '<span class="badge badge-success">فعال</span>'
                            : '<span class="badge badge-secondary">غیرفعال</span>';

                        // A menu entry that is switched on but has no usable route is the one
                        // failure that is invisible until the sidebar breaks.
                        if (row.broken_links > 0) {
                            status += ` <span class="badge badge-danger" title="زیرمنوی فعال بدون لینک معتبر">${row.broken_links} لینک نامعتبر</span>`;
                        }

                        return status;
                    }
                },

                { data: "updated_at" },

                {
                    data: null,
                    render: function (data, type, row) {
                        return `<a class="btn btn-sm btn-outline-primary" href="/dashboard/menuSetting/${row.id}/edit">ویرایش</a>`;
                    }
                }
            ]
        });

        table.draw();
    }

});
