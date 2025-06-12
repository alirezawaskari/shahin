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
        var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: "/dashboard/session/"+basicUrl+"/?page="+page,
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
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');        if ($.fn.DataTable.isDataTable('#datatable-json')) {
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
                { "data": "phone" },
                { "data": "discription" },
                { "data": data,
                    "render": function (data){
                        return data.histories.length != 0 ?data.histories[0].description+`<button type="button" class="btn   mr-1 user-histories" data-toggle="modal" data-user_id="${data.user_id}"  data-phone="${data.phone}" data-user_fullname="${data.name}" data-target="#exampleModalLong">
                          <i class="fa fa-list text-dark" aria-hidden="true"></i>
                                </button>`:''
                    }
                },
                // {
                //     "data": data,
                //     "render": function (data){
                //         if (data.entry_start == null && data.status == 'در انتظار انجام کار' ){
                //             return `<button type="button" class="btn btn-sm  start-user-session-entry" data-id="${data.id}" data-date="${data.date}">
                //             <i class="fa fa-play" aria-hidden="true"></i> </button>`
                //         }else if(data.entry_start != null && data.entry_end == null && false) {
                //             return `<button type="button" class="btn btn-sm  end-user-session-entry"  data-id="${data.id}">
                //                 <span><i class="fa fa-stop" aria-hidden="true"></i>
                //                 </span></button>`
                //         }else {
                //             return ``
                //         }

                //     }
                // },
                { "data": "user" },
                { "data": "confirmer" },
                { "data": "date" },
                // {
                //     "data": data,
                //     "render": function (data){
                //         if (data.entry_start != null && data.entry_end == null){
                //             return `<i class="fa fa-certificate" style="color: #08f108;  text-shadow: 0 0 10px #10e8e1;" aria-hidden="true"></i>`
                //         }else if (data.entry_start != null && data.entry_end != null){
                //             return `<span role="button" data-id="${data.id}" class="fa fa-caret-square-o-down entry-inserted" aria-hidden="true" style=" cursor: pointer;">
                //                 <div class="entry-data-box-${data.id}" style="display: none;width: 200px" >
                //                 <span class="text-dark">ورود: </span><span >${data.entry_start} </span>
                //                         <hr>
                //                     <span class="text-dark">خروج: </span><span >${data.entry_end}</span>
                //                 </div>
                //                 </span>
                //                 `
                //         }else {
                //             return ``;
                //         }
                //     }
                // },
                { "data": "center" },
                {
                    "targets": 2,
                    "data": "status",
                    "render": function ( data ) {
                        if(data == "در انتظار انجام کار")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "انجام شده")
                        {
                            return '<button class="badge badge-success border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "در انتظار مشاوره اولیه")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "ویزیت تایید شده")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "در انتظار مشاوره اصلی")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "مشاوره شد")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "ویزیت لغو شد")
                        {
                            return '<button class="badge badge-dark border-0">'+data+'</button><a h_blank">';
                        }
                    }
                },
                {
                    "targets": 1,
                    "data": data,
                    "render": function ( data ) {
                        let display='none';
                        if($('#delete').val()==1){
                            display='block';
                        }
                        if(document.getElementById("type").value == 'confirm')
                        {
                            return `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                <a href="../../dashboard/factorItem/${data.factor_item}/edit"><button type="button" class="dropdown-item pointer">امور اجرایی پرونده</button></a>
                                <a href="../../dashboard/session/confirm/${data.id}">
                                <button type="button" class="dropdown-item pointer">تایید و واریز به کیف پول</button></a>
                                <button style="display: ${display}"  data-id="${data.id}" class="session_delete dropdown-item pointer">حذف</button>
                                </a></div></div>`;
                        }
                        else
                        {
                            return `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                <a href="../../dashboard/factorItem/${data.factor_item}/edit"><button type="button" class="dropdown-item pointer">امور اجرایی پرونده</button></a>
                                ${(data.status == 'در انتظار مشاوره اولیه')? `<button data-id="${data.id}" type="button" class="dropdown-item pointer visit-confirm">تایید برای ویزیت </button>`:''}
                                <button style="display: ${display}"  data-id="${data.id}" class="session_delete dropdown-item pointer">حذف</button>
                                </div>
                                </div>`;
                        }
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
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });

    /*    click on do get user histories */
    $("body").on('click','.user-histories',function (event) {
        $('.user-fullname').text('');
        $('.user-fullname').text($(this).data('user_fullname')+'        '+$(this).data('phone'));
        $.ajax({
            type: "GET",
            url: "/dashboard/user/user-histories/"+$(this).data('user_id'),
            success: function (data) {
                generateDataTableUserHistories(data.data)
            }
        });
    });



    $("body").on('click','.session_delete',function () {
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
                            url: "/dashboard/session/"+id,
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
// visit change status to confirm
    $("body").on('click','.visit-confirm',function () {
        var id = $(this).data("id");
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
                $.ajax({
                    type: "GET",
                    url:  "/dashboard/session/visit/"+id+"/confirm",
                    dataType: "json",
                    encode: true,
                }).done(function (data,page) {
                    if (data.status=='success'){
                        fetchData(page)
                        swal({
                            text: "عملیات موفق ",
                            icon: "success",
                            button: "تایید",
                            timer:3000,
                            allowOutsideClick: true
                        });
                    }else{
                        swal("خطا !", data.message,'error')
                    }
                });
            }
        });
    });

// visit change status to waiting
    /*    $("body").on('click','.visit-waiting',function () {
            var id = $(this).data("id");
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
                        $.ajax({
                            type: "GET",
                            url:  "/dashboard/session/visit/"+id+"/waiting",
                            dataType: "json",
                            encode: true,
                        }).done(function (data,page) {
                            if (data.status=='success'){
                                fetchData(page)
                                swal({
                                    text:"عملیات موفق ",
                                    icon: "success",
                                    button: "تایید",
                                    timer:3000,
                                    allowOutsideClick: true
                                });
                            }else{
                                swal("خطا !", data.message,'error')
                            }
                        });
                    }
                });
        });*/

// visit change status to visited
    /*    $("body").on('click','.visit-visited',function () {
            var id = $(this).data("id");
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
                        $.ajax({
                            type: "GET",
                            url:  "/dashboard/session/visit/"+id+"/visited",
                            dataType: "json",
                            encode: true,
                        }).done(function (data,page) {
                            if (data.status=='success'){
                                fetchData(page)
                                swal({
                                    text: "عملیات موفق ",
                                    icon: "success",
                                    button: "تایید",
                                    timer:3000,
                                    allowOutsideClick: true
                                });
                            }else{
                                swal("خطا !", data.message,'error')
                            }
                        });
                    }
                });
        });*/

    $("body").on('click','.start-user-session-entry',function () {
        var id = $(this).data("id");
        var turn_date =$(this).data("date");
        swal({
            title:  `آیا مطمئن هستید؟`,
            text: ' تاریخ و ساعت این مراجعه : '+ turn_date ,
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res) {
                $.ajax({
                    type: "GET",
                    url:  "/dashboard/session/user-start-entry/"+id ,
                    dataType: "json",
                    encode: true,
                }).done(function (data) {
                    if (data.status=='success'){
                        $('#do_filter').click();
                    }else{
                        swal("خطا !", data.message,'error')
                    }
                });
            }
        });
    });


    $("body").on('click','.end-user-session-entry',function () {
        var id = $(this).data("id");
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
                $.ajax({
                    type: "GET",
                    url:  "/dashboard/session/user-end-entry/"+id ,
                    dataType: "json",
                    encode: true,
                }).done(function (data) {
                    if (data.status=='success'){
                        $('#do_filter').click();
                    }else{
                        swal("خطا !", data.message,'error')
                    }
                });
            }
        });
    })

    $("body").on('click','.entry-inserted',function () {
        $(`.entry-data-box-${$(this).data('id')}`).toggle()
    });
});


