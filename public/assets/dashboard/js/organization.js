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
        var form = $('#form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/organization/filter?page="+page,
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

        var table = $('#datatable-json').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: data.data,
            order:[[0,"desc"]],
            columns: [
                { "data": "id" },
                { "data": "archive_number" },
                { "data": "name" },
                { "data": "type" },
                { "data": "children_count" },
                { "data": "created_at" },
                {
                    "data": data,
                    "render": function (data) {
                        return `<span class="badge badge-${data.archive_status_class}">${data.archive_status}</span>`;
                    }
                },

                {
                    "targets": 1,
                    "data": data,
                    "render": function ( data ) {
                        let delete_display='none';
                        let make_child_display='none';
                        let items_display='none';
                        if($('#delete').val()==1){
                            delete_display='block';
                        }
                        if(data.parent_id==null){
                            make_child_display='block';
                        }
                        if(data.children_count>0){
                            items_display='block';
                        }

                            return `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                 <a href="../../dashboard/organization/${data.slug}/edit" target="_blank"><button type="button" class="dropdown-item pointer">مشاهده  </button></a>
                                 <a style="display: ${make_child_display}" href="../../dashboard/organization/create?parent_id=${data.id}" target="_blank"><button type="button" class="dropdown-item pointer">افزودن زیر سازمان</button></a>
                                 <a style="display: ${items_display}" href="../../dashboard/organization/items?id=${data.id}" target="_blank"><button type="button" class="dropdown-item pointer"> زیر سازمانها</button></a>
                                <button style="display: ${delete_display}"  data-id="${data.id}" data-slug="${data.slug}" class="organization_delete dropdown-item pointer">حذف</button>
                                </a></div></div>`;
                    }
                }
            ],

        });
        //add column count
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = data.meta.from+i;
            } );
        } ).draw();
    }
    $("form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            type: "GET",
            url: "/dashboard/organization/filter",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            do_filter_command=true;
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            createTable(data);
        });

        event.preventDefault();
    });



    $("body").on('click','.organization_delete',function () {
        var slug = $(this).data("slug");
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
                            url: "/dashboard/organization/"+slug,
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
