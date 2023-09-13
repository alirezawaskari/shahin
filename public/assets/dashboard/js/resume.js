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
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        return `<input type="checkbox" name="id[]" value="${data.id}">`
                    }
                },
                { "data": "name" },
                { "data": "phone" },
                { "data": "date" },
                { "data": data,
                    "render":function (data){
                        if (data.resume == ''){
                            return '<span class="badge badge-dark"> ندارد</span>'
                        }else{
                            return `<a target="_blank" href="${data.resume}"><span class="badge badge-primary">مشاهده</span></a>`
                        }
                    }
                },
                { "data": "job_title" },
                { "data": "representation" },
                { "data": data,
                    "render": function (data){
                        switch(data.status) {
                            case 0:
                                return '<span class="badge badge-danger">جدید</span>';
                            case 1:
                                return '<span class="badge badge-warning">قرار مصاحبه اولیه</span>';
                            case 2:
                                return '<span class="badge badge-warning">تعلیق شده</span>';
                            case 3:
                                return '<span class="badge badge-dark">تایید شده</span>';
                            case 4:
                                return '<span class="badge badge-dark">رد شده</span>';
                            case 5:
                                return '<span class="badge badge-warning">درحال بررسی</span>';
                            default:
                                return data.status
                        }
                    }
                },
                { "data": data,
                    "render": function (data){
                        let output = `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                    <a target="_blank" href="../../dashboard/resume/${data.id}/edit">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button></a>
                                                      <a target="_blank" href="../../dashboard/resume/${data.id}">
                                        <button type="button" class="dropdown-item pointer">مشاهده</button></a>
                                    `;
                        if (data.per_delete){
                            output=output+ ` <button  data-id="${data.id}" class="resume_delete dropdown-item pointer">حذف</button>
                                </div>
                                </div>`
                        }else{
                            output = output + `</div></div>`;
                        }
                        return output;
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

    /*    if checkbox of transfer checked for all requests   */
    $('#transfer_checkbox').change(function () {
        if ($(this).is(':checked')) {
            $('body td input:checkbox').prop('checked', this.checked);
        } else {
            $('body td input:checkbox').prop('checked', false);
        }
    });
/*    click on do transfer */
$("body").on('click', '#do_transfer', function (event) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var form = $('#transfer_form');
    var formData = form.serialize();
    $.ajax(
        {
            url: "/dashboard/resume/transfer",
            type: 'post',
            "_token": token,
            data: formData,
            success: function (response) {
                if (response.status == 'success') {
                    swal(response.messages, {
                        icon: "success",
                        button: "باشه"
                    });
                    fetchData(1);
                } else {
                    $('.transfer_message_box').empty();
                    $.each(response.messages, function (i, val) {
                        $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                    });
                }
            }
        });
});
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
    //--------------------delete-resume--------------------
    $("body").on('click','.resume_delete',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        $('#excel').prop('checked', false);
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
                            url: "/dashboard/resume/"+id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data,page){
                                if (data.status=='success'){
                                    fetchData(page)
                                    swal({
                                        text: "جلسه با موفقیت حذف گردید .",
                                        icon: "success",
                                        button: "تایید",
                                        timer:3000,
                                        allowOutsideClick: true
                                    });
                                }else{
                                    swal("خطا !", data.message,'error')
                                }
                            }
                        });
                }
            });
        }
    });
});
