$(document).ready(function () {

    fetchData(1);
    var first_load=true;
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

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/employee/filter?page="+page,
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
        if ($('#type_list').val() == 'financialReport') {
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                order:[[0,"desc"]],
                columns: [
                    { "data": "id" },
                    {
                        "data": data,
                        "render": function (data) {
                            return `<img src="${data.avatar}" alt="" class="border-radius5" width="80px"></img>`
                        }
                    },
                    { "data": "name" },
                    { "data": "phone" },
                    { "data": "work_title" },
                    { "data": "wallet" },
                    {
                        "targets": 1,
                        "data": data,
                        "render": function ( data ) {
                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a><div class="dropdown-menu">
                                <a href="../../dashboard/employee-financial-report/${data.id}" target="_blank"><button type="button" class="dropdown-item pointer">عملکرد ماهانه</button></a></div></div>`;
                        }
                    },

                ],
            });
        }
        else{
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                order:[[0,"desc"]],
                columns: [
                    { "data": "id" },
                    ...(Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1 ? [{
                        "data": data,
                        "orderable": false,
                        "render": function (data) {
                            return `<input type="checkbox"
                                class="business-transfer-record employee-business-transfer-record"
                                value="${data.id}"
                                data-current-business-ids="${(data.current_business_ids || []).join(',')}">`;
                        }
                    }] : []),
                    {
                        "data": data,
                        "render": function (data) {
                            return `<img src="${data.avatar}" alt="" class="border-radius5" width="80px"></img>`
                        }
                    },
                    { "data": "name" },
                    { "data": "phone" },
                    { "data": "representation" },
                    { "data": "work_title" },
                    { "data": "created_at" },
                    {
                        "targets":1,
                        "data": "status",
                        "render": function (data){
                         //   return data==1?'فعال':'غیر فعال'
                            return data==1?'<i class="fa fa-check" aria-hidden="true"></i>':'<i class="fa fa-times" aria-hidden="true"></i>'

                        }
                    },
                    {
                        "targets":1,
                        "data": "show_status",
                        "render": function (data){
                            return data==1?'<i class="fa fa-check" aria-hidden="true"></i>':'<i class="fa fa-times" aria-hidden="true"></i>'
                        }
                    },
                    {
                        "targets": 1,
                        "data": data,
                        "render": function ( data ) {
                            let loginDisplay='none';

                            if($('#can-manual-login').val()==1){
                                loginDisplay='block';
                            }
                            let businessTransferAction = '';
                            if (Number($('#datatable-json').attr('data-business-transfer-enabled')) === 1) {
                                businessTransferAction = `<button type="button" class="dropdown-item pointer"
                                    data-business-transfer-single data-transfer-entity="employee"
                                    data-record-id="${data.id}"
                                    data-current-business-ids="${(data.current_business_ids || []).join(',')}">
                                    انتقال به کسب و کار
                                </button>`;
                            }
                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a><div class="dropdown-menu"><a href="../../dashboard/employee/${data.id}/edit" target="_blank"><button type="button" class="dropdown-item pointer">ویرایش</button></a>
                              <a style="display: ${loginDisplay}" target="_blank" href="/dashboard/user/login/${data.user_id}"><span  data-id="${data.user_id}" class="task-delete dropdown-item pointer">
                                ورود
                                </span></a>
                                ${businessTransferAction}
                                <a href="../../dashboard/employee-financial-report/${data.id}" target="_blank"><button type="button" class="dropdown-item pointer">عملکرد ماهانه</button></a></div></div>`;
                        }
                    },

                ],

            });
        }

        //add column count
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }
    $("#form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            type: "GET",
            url: "/dashboard/employee/filter",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            createTable(data);
        });

        event.preventDefault();
    });
});
