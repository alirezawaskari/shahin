$(document).ready(function () {
    $('#role').on('change', function () {
        var id = $(this).val();
        $.ajax({
            url: '/dashboard/role/get-user-by-role',
            type: 'get',
            data: {
                "id": id,
            },
            success: function (data) {
                $('#users')
                    .empty()
                    .append('<option value="all">انتخاب دریافت کننده</option>')
                ;
                $.each(data.data.users, function (i, d) {
                    $('#users').append('<option value="' + d.id + '">' + d.name + " "+  d.family + '</option>');
                });
            }
        });
    });
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
            url: "/dashboard/ticket/" + basicUrl+"?page=" + page,

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
                {"data":"receivers"},
                {"data":data,
                "render": function (data){
                    return data.sender.name+" "+data.sender.family
                }},
                {"data":"created_at"},
                {"data":data,
                  "render":function (data){
                    if (data.status){
                        return 'پاسخ داده شده '
                    }else{
                        return  'منتظر پاسخ'
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
                             <a href="/dashboard/ticket/${data.id}/edit"><span class="color-edit dropdown-item pointer">مشاهده</span></a>
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

