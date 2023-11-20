$(document).ready(function () {
    fetchData(1);
    var first_load=true;
    var do_filter_command=false;

    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#excel').prop('checked') == true) {
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
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/financialPlansType/filter/?page=" + page,
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
                    {"data": "title"},
                    {"data": "price"},
                    {"data": "payment_type"},
                    {"data": "prepayment_percent"},
                    {"data": "discount_percent"},
                    {"data": "capacity"},
                    {"data": "reserved"},
                    {"data": "start_date"},
                    {"data": "end_date"},
                    {"data": data,
                    "render": function (data){
                           if (data.status){
                               return `<i class="fa fa-check" aria-hidden="true"></i>`;
                           }else {
                               return `<i class="fa fa-closFe" aria-hidden="true"></i>`;
                           }
                    }
                    },
                    {"data": data,
                    "render": function (data){
                           if (data.is_show){
                               return `<i class="fa fa-check" aria-hidden="true"></i>`;
                           }else {
                               return `<i class="fa fa-close" aria-hidden="true"></i>`;
                           }
                    }
                    },
                    {"data": "applicant"},
                    {"data": "created_at"},
                    {
                        "data": data,
                        "render": function (data) {
                            let display='none';
                            if($('#delete').val() == 1){
                                display='block'
                            }
                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                <a href="/dashboard/financialPlansType/${data.id}/edit"><span  data-id="${data.id}" class="color-edit dropdown-item pointer">ویرایش</span></a>
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

//
$('#payment_type').on('change', function () {
    if ($('#payment_type').val() == 'check') {
        $('#type_number_pay_steps').show();
        if ($('#select_type_number_pay_steps').val() == 'limit') {
            $('#number_pay_steps').show();
        } else {
            $('#number_pay_steps').hide();
        }
        $('#prepayment_percent , #guarantee_type').show();
    } else if($('#payment_type').val() == 'installment') {
        $('input[name=number_pay_steps]').val(null);
        $('#type_number_pay_steps, #number_pay_steps').hide();
        $('#prepayment_percent , #guarantee_type').show();
    } else if($('#payment_type').val() == 'cash') {
        $('input[name=number_pay_steps],select[name=guarantee_type]').val(null);
        $('#type_number_pay_steps, #number_pay_steps , #prepayment_percent , #guarantee_type').hide();
    }
});
$('#select_type_number_pay_steps').on('change', function () {
    if ($('#select_type_number_pay_steps').val() == 'limit') {
        $('#number_pay_steps').show();
    } else {
        $('input[name=number_pay_steps]').val(null);
        $('#number_pay_steps').hide()
    }
});
