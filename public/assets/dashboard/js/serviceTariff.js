$(document).ready(function () {
    fetchData(1);

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
                //fetch content and render here
                fetchData(page);
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/serviceTariff/filter?page="+page,
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
                { "data": "price" },
                { "data": "tariff" },
                { "data": "jalali_created_at" },
                { "data": data,
                    "render": function (data){
                        return `<a href="/dashboard/serviceTariff/${data.slug}/edit"><button  class="btn btn-primary">ویرایش</button></a>`
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

    /*    click on do filter */
    $("body").on('click','#do_filter',function (event) {
        if ($('#excel').prop('checked')==true){
            $('form').submit();
        }else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            fetchData(1)
            event.preventDefault();
        }
    });

    $("body").on('click','.entry-inserted',function () {
        $(`.entry-data-box-${$(this).data('id')}`).toggle()
    });
});

var ct = 1;

function newServiceTariffLink() {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    var delLink = '<a class="text-white" href="javascript:delServiceTariffLink(' + ct + ')"><div class="btn badge-red"><i class="fa fa-trash" aria-hidden="true"></i></div></a>';

    div1.innerHTML = document.getElementById('newservicetarifftpl').innerHTML;
    div1.classList.add("row");
    div1.classList.add("card");
    document.getElementById('newservicetariff').appendChild(div1);
    div1.children[0].children[0].children[5].id = "delete" + ct;
    document.getElementById('delete' + ct).innerHTML = delLink;
}

function delServiceTariffLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newservicetariff');
    parentEle.removeChild(ele);
}
