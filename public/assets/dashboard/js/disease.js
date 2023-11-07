$(document).ready(function () {

    $('.check-date').persianDatepicker({
        observer: true,
        format: 'YYYY/MM/DD',
        initialValueType: 'gregorian'
    });

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
        // var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: $('#filter_form').attr('action')+"?page=" + page,
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
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');
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
                {"data": "number_amount"},
                {"data": "jalali_date"},
                {
                    "data": data,
                    "render": function (data){
                        switch (data.title) {
                            case 'installment':
                                return '<span class="badge badge-primary">قسط</span> ';
                            case 'guaranty':
                                return '<span class="badge badge-info">ضمانت</span> ';
                            case 'prepayment':
                                return '<span class="badge badge-purple">پیش پرداخت</span> ';
                            default:
                                return ''
                        }
                    }
                },
                {"data":data,
                    "render":function (data){
                        if (data.front_image!=''){
                            return  `<a href="${data.front_image}" target="_blank"> <img src="${data.front_image}" width="70px" alt=""></a>`
                        }
                        if (data.back_image!='') {
                            return  `<a href="${data.back_image}" target="_blank"> <img src="${data.back_image}" width="70px" alt=""></a>`
                        }
                        return '';
                    }
                },
                {"data": "description"},
                {
                    "data": data,
                    "render": function (data){
                        switch (data.status) {
                            case 0:
                                return '<span class="badge badge-warning">در انتظار وصول</span> ';
                            case 1:
                                return '<span class="badge badge-success">وصول شده</span> ';
                            case 2:
                                return '<span class="badge badge-danger">برگشت خورده</span>';
                            case 3:
                                return '<span class="badge badge-info">عودت داده شده</span>';
                            default:
                                return ''
                        }
                    }
                },

                {
                    "data": data,
                    "render": function (data) {
                        let status_display='none';
                        if($('#can-update-status-check').val()==1){
                            status_display='block';
                        }
                        return `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">

                        </div>
                        </div>`
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



$(document).ready(function () {
    //----------------------store check ---------------
    $("body").on('click', '#submit-disease-btn', function () {

    })
    // ---------------------end store check -------------

    // ----------------------remove check ---------------------

    $("body").on('click','.delete-disease',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        submit();
        function submit(event) {
            swal({
                title: "آیا مطمئن هستید؟",
                text: "",
                icon: "warning",
                buttons: {
                    confirm: 'بله',
                    cancel: 'خیر'
                },
                dangerMode: true
            }).then(res => {
                if (res) {
                    $.ajax(
                        {
                            url: "/dashboard/disease/"+id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data,page){
                                if (data.status=='success'){
                                    swal({
                                        text: "چک با موفقیت حذف گردید .",
                                        icon: "success",
                                        button: "تایید",
                                        timer:3000,
                                        allowOutsideClick: true
                                    });
                                     location.reload();
                                }else{
                                    swal("خطا! ", data.messages.join('\n'), "error");
                                }
                            }
                        });
                }
            });
        }
    });
})
