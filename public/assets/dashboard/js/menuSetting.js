$(document).ready(function () {

    let isInitialLoad = true;
    let isFilterAction = false;

    fetchData(1);

    // 🔎 Filter button click
    $("body").on('click', '#do_filter', function (event) {

        if ($('#excel').prop('checked')) {
            $('#filter_form').submit();
            return;
        }

        event.preventDefault();

        $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");

        isFilterAction = true;
        fetchData(1);
    });


    // 📄 Pagination
    function pagination(totalPages) {

        if ($('#pagination-demo').data("twbs-pagination")) {
            $('#pagination-demo').twbsPagination('destroy');
        }

        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',

            onPageClick: function (event, page) {

                if (!isInitialLoad && !isFilterAction) {
                    fetchData(page);
                }

                isInitialLoad = false;
                isFilterAction = false;
            }
        });
    }


    // 📦 Fetch Data
    function fetchData(page) {

        $('#loading').show();

        let formData = $('#filter_form').serialize();

        $.ajax({
            url: "/dashboard/menuSetting/filter/?page=" + page,
            type: "GET",
            data: formData,

            success: function (response) {
                createTable(response);
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
    function createTable(response) {

        pagination(response.meta.last_page);

        $('#page_text').html(
            `نمایش ${response.meta.from} تا ${response.meta.to} از ${response.meta.total} آیتم`
        );

        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();

        let table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: response.data,

            columns: [
                { data: "sort_by" },
                { data: "name" },
                {
                    data: "icon",
                    render: function (data, type, row) {
                        return `<i class="icon ${data}"></i>`;
                    }
                },
                { data: "updated_at" },

                {
                    data: null,
                    render: function (data, type, row) {

                        return `
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown" class="btn btn-dark btn-sm btn-icon">
                                    <i class="fa fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu">
                                    <a href="/dashboard/menuSetting/${row.id}/edit">
                                        <span class="dropdown-item">ویرایش</span>
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                }
            ]
        });

        // 🔢 Row numbering
        table.on('order.dt search.dt', function () {

            table.column(0, { search: 'applied', order: 'applied' })
                .nodes()
                .each(function (cell, i) {
                    cell.innerHTML = response.meta.from + i;
                });

        }).draw();
    }

});
