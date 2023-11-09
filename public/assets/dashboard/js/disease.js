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
        // $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');
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
                {"data": "name"},
                {
                    "data": data,
                    "render": function (data) {
                        // let status_display='none';
                        // if($('#can-update-status-check').val()==1){
                        //     status_display='block';
                        // }
                        return `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                 <button type="button" class="dropdown-item pointer" data-disease_id="${data.id}" data-toggle="modal" data-target="#updateDiseaseModal">ویرایش</button>
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
    $('#diseaseModal form').submit(function(e) {
        e.preventDefault();
        var form = $('#store-disease-form');
        var formData = form.serialize();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            }
        });
        $.ajax({
            url: $('#store-disease-form').attr('action'),
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status == 'success'){
                    swal(response.message, {
                        icon: "success",
                        button: "باشه"
                    });
                    $('#do_filter').click();
                    $('#name').val('');
                }else if(response.status == 'error'){
                    swal(response.message, {
                        icon: "error",
                        button: "باشه"
                    });
                }


            },
            error: function(response) {
              alert('خطایی رخ داده')
            }
        });
    });
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
