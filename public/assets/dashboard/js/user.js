$(document).ready(function () {

    $('#level').on('change', function() {
        if( this.value == 'user' || this.value == 'all' ){
            $('#role_id').val("all").change();
        }
    });
    $('#role_id').on('change', function() {
        if(  this.value != 'all' ){
            $('#level').val("admin").change();
        }
    });

    fetchData(1)
    var first_load=true;

    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#exel').prop('checked') == true) {
            $('#filter_form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
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
                if (!first_load){
                    fetchData(page);
                }
                first_load=false
            }
        });
    }
    //fetch data
    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/" + basicUrl + "?page=" + page,

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
                ...(Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1 ? [{
                    "data": data,
                    "orderable": false,
                    "render": function (data) {
                        return `<input type="checkbox"
                            class="business-transfer-record user-business-transfer-record"
                            value="${data.id}"
                            data-current-business-ids="${(data.current_business_ids || []).join(',')}">`;
                    }
                }] : []),
                {"data": "name"},
                {"data": "family"},
                {"data": "phone"},
                {"data": "melicode"},
                {"data": "token"},
                {"data": "representation"},
                {"data": "date"},
                {
                    "data": data,
                    "render": function (data) {
                        let display='none';
                        let loginDisplay='none';
                        if($('#delete').val() == 1){
                            display='block'
                        }
                        if($('#can-manual-login').val()==1){
                            loginDisplay='block';
                        }
                        let businessTransferAction = '';
                        if (Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1) {
                            businessTransferAction = `<button type="button" class="dropdown-item pointer"
                                data-business-transfer-single data-transfer-entity="user"
                                data-record-id="${data.id}"
                                data-current-business-ids="${(data.current_business_ids || []).join(',')}">
                                انتقال به کسب و کار
                            </button>`;
                        }
                        let output= `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">`
                                output+=`<a target="_blank" href="/dashboard/user/${data.id}"><span  data-id="${data.id}" class="task-delete dropdown-item pointer">
                            مشاهده
                            </span></a>
                            <a target="_blank" href="/dashboard/accounting/show/${data.id}"><span  data-id="${data.id}" class="task-delete dropdown-item pointer">
                            عملکرد
                            </span></a>
                            <a target="_blank" href="/dashboard/user/${data.id}/edit">
                            <span  data-id="${data.id}" class="task-delete dropdown-item pointer" >
                            ویرایش
                            </span>
                            </a>
                            <a target="_blank" href="/dashboard/user/creditDetail/${data.id}"><span  data-id="${data.id}" class="task-delete dropdown-item pointer">
                            اعتبار
                            </span></a>
                            <a style="display: ${loginDisplay}" target="_blank" href="/dashboard/user/login/${data.id}"><span  data-id="${data.id}" class="task-delete dropdown-item pointer">
                            ورود
                            </span></a>
                            <a href="/dashboard/factorItem/${data.resnumber}/edit"></a>
                            ${businessTransferAction}
                            <span style="display: ${display}" data-taskCount="${data.task_count}"  data-id="${data.id}" class="user_delete dropdown-item pointer">
                            حذف
                            </span>
                        </div></div>`
                        return output;
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

    $("body").on('click','.user_delete',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        $('#excel').prop('checked', false);
            swal({
                title: "آیا مطمئن هستید؟",
                text: "تعداد تسک های کاربر مورد نظر  " + $(this).data('taskcount') ,
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
                            url: "/dashboard/user/"+id,
                            type: 'DELETE',
                            data: {
                                "_token": token,
                            },
                            success: function (data,page){
                                if (data.status=='success'){
                                    fetchData(page)
                                    swal({
                                        text: "کاربر با موفقیت حذف گردید .",
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

    });
});
