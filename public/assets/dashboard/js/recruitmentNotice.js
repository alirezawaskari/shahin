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
        // var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: $('#filter_form').attr('action')+"/?page=" + page,
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
                    {"data": "title"},
                    {"data": "province"},
                    {"data": data,
                     "render": function (data){
                            if (data.status){
                                return `<i class="fa fa-check" aria-hidden="true"></i>`;
                            }else {
                                return `<i class="fa fa-closFe" aria-hidden="true"></i>`;
                            }
                     }
                    },
                    {"data": "representation"},
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
                                <a href="/dashboard/recruitmentNotice/${data.id}/edit"><span  data-id="${data.id}" class="color-edit dropdown-item pointer">ویرایش</span></a>
                                <span style="display: ${display}"  data-id="${data.id}" class="recruitment-notice-delete dropdown-item pointer">حذف</span>

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



// delete factor
$('body').on('click', '.recruitment-notice-delete',function () {
    var recruitment_notice_id = $(this).data("id");
    var token = $('meta[name="csrf-token"]').attr('content');
    swal({
        title:  `آیا مطمئن هستید؟`,
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
                    url: "/dashboard/recruitmentNotice/"+recruitment_notice_id,
                    type: 'DELETE',
                    data: {
                        "_token": token,
                    },
                    success: function (response,page) {
                        if (response.status=='success'){
                            fetchData(page)
                            swal({
                                title: response.message,
                                icon: "success",
                                button: "باشه",
                            });
                        }else {
                            swal({
                                title: "خطا",
                                text: "خطایی رخ داده است . ",
                                icon: "warning",
                                dangerMode: true,
                            })
                        }
                    }
                });
        }
    });
});

});


