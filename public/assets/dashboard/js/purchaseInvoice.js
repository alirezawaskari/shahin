$(document).ready(function () {

    fetchData(1);
    var first_load = true;
    var do_filter_command = false;
    /*    if checkbox of transfer checked for all requests   */



    /*    click on do filter */
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
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/purchaseInvoice/" + basicUrl + "?page=" + page,

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
            columns: [{
                    "data": "sort_by"
                },
                {
                    "data": "id"
                },
                {
                    "data": "supplier"
                },
                {
                    "data": "requester"
                },
                {
                    "data": "representation"
                },
                {
                    "data": "total_price"
                },
                {
                    "data": "created_at"
                },
                {
                    "data": data,
                    "render": function (data) {
                        return `<span class="badge badge-${data.status_class}">${data.status}</span>`;
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        let display = 'none';
                        if ($('#delete').val() == 1) {
                            display = 'block';
                        }
                        return `<div data-v-15965e3b="" class="dropdown" id="taskDetailModal">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                             <a href="/dashboard/purchaseInvoice/${data.id}/edit"><span class="color-edit dropdown-item pointer">مشاهده و ویرایش فاکتور</span></a>
                             <a href="/dashboard/create-product-purchaseInvoice/${data.id}"><span class="color-edit dropdown-item pointer">ثبت کالا</span></a>
                             <button style="display: ${display}"  data-id="${data.id}" class="purchase_invoice_delete dropdown-item pointer">حذف</button>
                             </div></div>`
                    }
                }
            ],
        });
        //add column count
        table.on('order.dt search.dt', function () {
            table.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }
    $("body").on('click', '.purchase_invoice_delete', function () {
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
                    $.ajax({
                        url: "/dashboard/purchaseInvoice/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": token,
                        },
                        success: function (data, page) {
                            if (data.status == 'success') {
                                fetchData(page)
                                swal({
                                    text: "فاکتور با موفقیت حذف گردید .",
                                    icon: "success",
                                    button: "تایید",
                                    timer: 3000,
                                    allowOutsideClick: true
                                });
                            } else {
                                swal("خطا !", data.message, 'error')
                            }
                        }
                    });
                }
            });
        }
    });
});
ct = 1;

function new_payment_div() {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    var delLink = '<div class="btn badge-red"><a class="text-white" href="javascript:delPayment(' + ct + ')"><i class="fa fa-trash" aria-hidden="true"></i></a></div>';
    div1.innerHTML = document.getElementById('newpaymenttpl').innerHTML;
    document.getElementById('newpayment').appendChild(div1);
    div1.children[0].children[0].children[1].children[2].id = "delete" + ct;
    div1.children[0].children[0].children[1].children[1].children[0].id = "payment_date" + ct;
    document.getElementById('delete' + ct).innerHTML = delLink;
    $("#payment_date" + ct).pDatepicker({
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

function delPayment(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newpayment');
    parentEle.removeChild(ele);
}
