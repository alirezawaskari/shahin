$(document).ready(function () {

    $("#toBottom").click(function () {
        $('html, body').animate({
            scrollTop: $("#one-section").offset().top
        }, 1000);
    });
    $("#toBottom2").click(function () {
        $('html, body').animate({
            scrollTop: $("#one-section").offset().top
        }, 1000);
    });

    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    /* click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        $('#pagination-demo').remove();
        $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
        do_filter_command=true;
        fetchData(1)
        event.preventDefault();
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
        $.ajax({
            url: "/dashboard/role/filter?page=" + page,
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
                {"data":"display_name"},
                {"data":"created_at"},
                {
                    "data": data,
                    "render": function (data) {
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                            <a href="/dashboard/role/${data.id}/edit"><span class="color-edit dropdown-item pointer">ویرایش دسترسی ها</span></a>
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

    if (window.location.hash) {
        var hash = window.location.hash;

        $('html, body').animate({
            scrollTop: $(hash).offset().top
        }, 1500);
    }


    $(function () {
        $('body').on('click', '.pagination a', function (e) {
            e.preventDefault();
            $('.load').append('<img style="position: absolute; left: 50%; top: 0; z-index: 100000;" src="/upload/demo_wait.gif" />');
            var url = $(this).attr('href');
            getDataPagination(url);
            window.history.pushState("", "", url);
        });

        function getDataPagination(url) {
            $.ajax({
                url: url
            }).done(function (data) {
                $('.dataPagination').html(data);
            });
        }
    });
});
