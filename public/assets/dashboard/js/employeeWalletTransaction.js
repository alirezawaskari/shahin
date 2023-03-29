$(document).ready(function () {
    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
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
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();

        $.ajax({
            url: $('#filter_form').attr('action') +"/?page="+page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });

    }

    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');        if ($.fn.DataTable.isDataTable('#datatable-json')) {
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
                { "data": "price" },
                { "data": data,
                   "render":function (data){
                    switch (data.type){
                        case 0:
                            return `<span class="badge badge-danger">برداشت از کیف پول</span>`
                        case 1:
                            return `<span class="badge badge-success">واریز به کیف پول</span>`
                        case 2:
                            return `<span class="badge badge-primary">واریز به حساب بانکی</span>`
                    }

                   }
                },
                { "data": "date" },
                { "data": "description" }
            ],
        });
        //add column count
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }

    /*    click on do filter */
    $("body").on('click','#do_filter',function (event) {
        if ($('#excel').prop('checked')==true){
            $('form').submit();
        }else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });
});


