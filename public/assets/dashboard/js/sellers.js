$(document).ready(function () {
    $('#role').select2({
        placeholder: "انتخاب نقش"
    });
    $('#parents').select2({
        placeholder: "انتخاب سرپرست"
    });

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
            url: "/dashboard/seller/filter?page="+page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        if(data.meta.total)
        {
            document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        }
        else
        {
            document.getElementById("page_text").innerHTML = null;
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
                { "data": "id" },
                { "data": "name" },
                { "data": "phone" },
                { "data": "representation" },
                { "data": "role_title" },
                { "data": "parent_fullname" },
                { "data": "created_at" },
                {
                    "targets":1,
                    "data": "status",
                    "render": function (data){
                     //   return data==1?'فعال':'غیر فعال'
                        return data==1?'<i class="fa fa-check" aria-hidden="true"></i>':'<i class="fa fa-times" aria-hidden="true"></i>'

                    }
                },
                {
                    "targets":1,
                    "data": "show_status",
                    "render": function (data){
                        return data==1?'<i class="fa fa-check" aria-hidden="true"></i>':'<i class="fa fa-times" aria-hidden="true"></i>'
                    }
                },
                {
                    "targets": 1,
                    "data": "id",
                    "render": function ( data ) {
                        return '<div data-v-15965e3b="" class="dropdown"><a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a><div class="dropdown-menu"><a href="../../dashboard/employee/'+data+'/edit" target="_blank"><button type="button" class="dropdown-item pointer">مشاهده</button></a></div></div>';
                    }
                },

            ],

        });
        //add column count
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }
    $("form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            type: "GET",
            url: "/dashboard/seller/filter",
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
