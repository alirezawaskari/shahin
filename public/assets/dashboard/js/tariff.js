
$(document).ready(function () {

    fetchData(1);
    var first_load = true;
    var do_filter_command = false;
    $("body").on('click', '#do_filter', function (event) {
        if ($('#exel').prop('checked') == true) {
            $('#filter_form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command = true;
            fetchData(1)
            event.preventDefault();
        }
    });
    var counter = 1;
    $("body").on('click', '#addReceiver', function () {
        counter++;
        var role_select = $('#role_select');
        var clonedRoleSelect = role_select.clone(true, true);
        var receiver_select = $('#receiver_select');
        var clonedReceiverSelect = receiver_select.clone(true, true);
        var role_id = 'role_select' + counter;
        var receiver_id = 'receiver_select' + counter;
        clonedRoleSelect.attr('id', role_id);
        clonedReceiverSelect.attr('id', receiver_id);
        var receiverSelect = $('<div class="col-12 col-md-6 mb-2"></div>')
            .append($('<label></label>').text('کاربر مربوطه'))
            .append($('<select></select>')
                .attr('required', 'required')
                .attr('name', 'receivers[]')
                .attr('id', receiver_id)
                .addClass('select2 form-control'));
        var roleSelect = $('<div class="col-12 col-md-6 mb-2"></div>')
            .append($('<label></label>').text('نقش مربوطه'))
            .append($('<select></select>')
                .attr('required', 'required')
                .attr('id', role_id)
                .addClass('select2 form-control ' + role_id));
        $.ajax({
            url: '/dashboard/role/get',
            type: 'get',
            success: function (data) {
                $('#receiver').append(roleSelect);
                $('#receiver').append(receiverSelect);
                $("#"+role_id).select2();
                $("#"+role_id).append($('<option></option>')
                    .attr('value', 'all')
                    .text('انتخاب کنید'));
                $("#"+receiver_id).append($('<option></option>')
                    .attr('value', 'all')
                    .text('نقش را انتخاب کنید'));
                $.each(data.data, function(index, data) {
                    $("#"+role_id).append($('<option></option>')
                        .attr('value', data.id)
                        .text(data.display_name));
                });
                var selectBox = clonedRoleSelect.find('select');
                selectBox.select2();
            }
        });
    });

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                //fetch content and render here
                if (!first_load && !do_filter_command) {
                    fetchData(page);
                }
                first_load = false
                do_filter_command = false
            }
        });
    }

    function fetchData(page) {
        $('#loading').css('display', 'block');
        var form = $('#filter_form');
        var formData = form.serialize();
        $.ajax({
            url: "/dashboard/tariff"+"?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });
    }

    /* create table */
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
                {"data": "service"},
                {"data": "created_at"},
                {
                    "data": data,
                    "render": function (data) {
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                             <a href="/dashboard/tariff/${data.id}/edit"><span class="color-edit dropdown-item pointercolor-edit dropdown-item pointer">ویرایش</span></a>
                            <button data-id="${data.id}" class="tariff_delete dropdown-item pointer">حذف</button>
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
    $("body").on('click','.tariff_delete',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        submit();
        function submit() {
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
                            url: "/dashboard/tariff/"+id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data){
                                console.log('test');
                                if (data.status=='success'){
                                    fetchData(1)
                                    swal({
                                        text: "تعرفه با موفقیت حذف گردید .",
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

