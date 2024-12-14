$(document).ready(function () {
    $("body").on('click', '.registrationInitialConsultationModal', function () {
        var session_id= $(this).data('id');
        $.ajax(
            {
                url: `/dashboard/factorItem/getForm/${session_id}`,
                type: 'get',
                success: function (response) {
                    let view = response.data.view;
                    $('.modal-body-registration-initial-consultation').html(view);
                    $(".date").pDatepicker({
                        initialValueType: "gregorian",
                        format: "YYYY/MM/DD H:m:s",
                        onSelect: "year",
                        timePicker: {
                            enabled: true,
                            meridiem: {
                                enabled: true
                            }
                        }
                    });
                }
            });
    });
    if ($('#type_consultation_online').is(':checked')) {
        $('.divDate').css('display', 'none');
    }
    $('#select_type_consultation').on('change', function () {
        if ($('#select_type_consultation').val() == 'inPerson') {
            $('.divDate').css('display', 'block');
        } else {
            $('.divDate').css('display', 'none');
        }
    });
    $('#visitRejectModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var getID = button.data('id')
        var modal = $(this)
        modal.find('#sessionID').val(getID)
    });
    fetchData(1);
    var first_load=true;
    var do_filter_command=false;
    function pagination(totalPages) {
        $('#pagination-demo-trun-ratings').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel-trun-ratings').prop('checked', false);
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
        var form = $('#filter_form_trun_ratings');
        var formData = form.serialize();
        var basicUrl = document.getElementById('url').value;

        $.ajax({
            url: "/dashboard/session/trunRatings/"+basicUrl+"/?page="+page,
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
        $("#page_text").append('( جمع کل صفحه : '+data.meta.sum_price+' ریال - جمع کل :'+data.meta.total_price+ ' ریال )');        if ($.fn.DataTable.isDataTable('#datatable-json-trun-ratings')) {
            $('#datatable-json-trun-ratings').DataTable().destroy();
        }

        $('#datatable-json-trun-ratings tbody').empty();
        var table = $('#datatable-json-trun-ratings').DataTable({
            paging: false,
            searching: false,
            retrieve: true,
            data: data.data,
            order:[[0,"desc"]],
            columns: [
                { "data": "sort_by" },
                { "data": "name" },
                { "data": "discription" },
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
                {
                    "targets": 2,
                    "data": "status",
                    "render": function ( data ) {
                        return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                    }
                },
                {
                    "targets": 1,
                    "data": data,
                    "render": function ( data ) {
                        let display='none';
                        if($('#delete-trun-ratings').val()==1){
                            display='block';
                        }

                            return `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm"><i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                ${(data.status == 'در انتظار مشاوره اصلی')? `<a href="../../dashboard/factorItem/${data.factor_item}/edit"><button type="button" class="dropdown-item pointer">امور اجرایی پرونده</button></a>`:''}
                                ${(data.status == 'در انتظار مشاوره اولیه')? `<button data-id="${data.factor_item}" type="button" class="registrationInitialConsultationModal dropdown-item pointer" data-toggle="modal" data-target="#registrationInitialConsultationModal">ثبت مشاوره اولیه</button>`:''}
                                <button data-id="${data.id}" type="button" class="dropdown-item pointer visit-reject" data-toggle="modal" data-target="#visitRejectModal">لغو مشاوره</button>
                                <button style="display: ${display}"  data-id="${data.id}" class="session_delete dropdown-item pointer">حذف</button>
                                </div>
                                </div>`;

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
    $("body").on('click','#do_filter_trun_ratings',function (event) {
        if ($('#excel-trun-ratings').prop('checked')==true){
            $('form').submit();
        }else {
            $('#pagination-demo-trun-ratings').remove();
            $('#pagination-box-trun-ratings').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });

    $("body").on('click','.session_delete',function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
        $('#excel-trun-ratings').prop('checked', false);
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
                        $('#do_filter_trun_ratings').click();
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
                        $('#do_filter_trun_ratings').click();
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


