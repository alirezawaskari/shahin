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
            url: "/dashboard/agency/filter?page="+page,
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
                { "data": "sort_by" },
                { "data": "name" },
                { "data": "phone" },
                { "data": "province" },
                { "data": "city" },
                { "data": "agency_type" },
                { "data": "created_at" },
                { "data": "updated_at" },
                {
                    "targets": 2,
                    "data": "status",
                    "render": function ( data, type, row, meta ) {
                        if(data == "جدید")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "بررسی شده")
                        {
                            return '<button class="badge badge-success border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "تایید شده")
                        {
                            return '<button class="badge badge-success border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "تعلیق شده")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "اعطا شده")
                        {
                            return '<button class="badge badge-dark border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "نیاز به تماس")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                    }
                },
                {
                    "targets": 2,
                    "data": "id",
                    "render": function ( data, type, row, meta ) {
                        return '<div data-v-15965e3b="" class="dropdown"><a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a><div class="dropdown-menu"><a href="../../dashboard/agency/'+data+'/edit" target="_blank"><button type="button" class="dropdown-item pointer">ویرایش</button></a></div></div>';
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
        if ($('#exel').prop('checked') == false) {
            $.ajax({
                type: "GET",
                url: "/dashboard/agency/filter",
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function (data) {
                $('#pagination-demo').remove();
                $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
                createTable(data);
            });
        }else {
            form.submit();
        }
        event.preventDefault();
    });
});
