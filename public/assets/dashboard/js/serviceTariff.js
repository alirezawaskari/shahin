$(document).ready(function () {
    //create service tariif
    $("body").on('click', '#createServiceTariff', function () {
        var form = $('#form_data');
        var formData = form.serialize();
        var token = $('meta[name="csrf-token"]').attr('content');
        swal({
            title: `آیا مطمئن هستید؟`,
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res) {
                $.ajax({
                    url: "/dashboard/serviceTariff/store",
                    type: 'post',
                    "_token": token,
                    data: formData,
                    dataType: "json",
                    encode: true,
                }).done(function (data) {
                    if (data.status == 'success') {
                        swal("موفق", data.message, 'success')
                        location.reload();
                    } else {
                        swal("خطا !", data.message, 'error')
                    }
                });
            }
        });
    });
    //update service tariif
    $("body").on('click', '#updateServiceTariff', function () {
        var form = $('#form_data');
        var formData = form.serialize();
        var service_tariff_id = $('#service_tariff_id').val();
        var token = $('meta[name="csrf-token"]').attr('content');
        swal({
            title: `آیا مطمئن هستید؟`,
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res) {
                $.ajax({
                    type: "post",
                    url: "/dashboard/serviceTariff/update/" + service_tariff_id,
                    "_token": token,
                    data: formData,
                    dataType: "json",
                    encode: true,
                }).done(function (data) {
                    if (data.status == 'success') {
                        swal("موفق", data.message, 'success')
                        location.reload();
                    } else {
                        swal("خطا !", data.message, 'error')
                    }
                });
            }
        });
    });

    //delete allocation employee centers
    $("body").on('click', '.allocation_employee_delete', function () {
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
                        url: "/dashboard/allocationEmployeeShareCenter/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": token,
                        },
                        success: function (data, page) {
                            if (data.status == 'success') {
                                swal({
                                    text: " با موفقیت حذف گردید .",
                                    icon: "success",
                                    button: "تایید",
                                    timer: 3000,
                                    allowOutsideClick: true
                                });
                                location.reload();
                            } else {
                                swal({
                                    text: " شما مجاز به انجام این کار نیستید ",
                                    icon: "error",
                                    button: "تایید",
                                    timer: 3000,
                                    allowOutsideClick: true
                                });
                            }
                        }
                    });
                }
            });
        }
    });
    fetchData(1);

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
                //fetch content and render here
                fetchData(page);
            }
        });
    }

    function fetchData(page) {
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();

        $.ajax({
            url: "/dashboard/serviceTariff/filter?page=" + page,
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

        if ($('#access_selling_price').val()) {
            $("#page_text").append('( میانگین نرخ اختلاف خرید : ' + data.meta.average_company_profit + '%)');
        }

        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();
        if($('#access_selling_price').val())
        {
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        "data": "sort_by"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "price"
                    },
                    {
                        "data": "selling_price"
                    },
                    {
                        "data": "company_profit"
                    },
                    {
                        "data": "tariff"
                    },
                    {
                        "data": "jalali_created_at"
                    },
                    {
                        "data": data,
                        "render": function (data) {
                            return `<a href="/dashboard/serviceTariff/${data.slug}/edit"><button  class="btn btn-primary">ویرایش</button></a>`
                        }
                    },
                ],
            });
        }
        else
        {
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,
                order: [
                    [0, "desc"]
                ],
                columns: [{
                        "data": "sort_by"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "price"
                    },
                    {
                        "data": "tariff"
                    },
                    {
                        "data": "jalali_created_at"
                    },
                    {
                        "data": data,
                        "render": function (data) {
                            return `<a href="/dashboard/serviceTariff/${data.slug}/edit"><button  class="btn btn-primary">ویرایش</button></a>`
                        }
                    },
                ],
            });
        }

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

    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#excel').prop('checked') == true) {
            $('form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            fetchData(1)
            event.preventDefault();
        }
    });

    $("body").on('click', '.entry-inserted', function () {
        $(`.entry-data-box-${$(this).data('id')}`).toggle()
    });
});

var ct = 1;

function newAllocationEmployeeShareLink() {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    var delLink = '<a class="text-white" href="javascript:delAllocationEmployeeShareLink(' + ct + ')"><div class="btn badge-red"><i class="fa fa-trash" aria-hidden="true"></i></div></a>';

    div1.innerHTML = document.getElementById('newallocationemployeesharetpl').innerHTML;
    div1.classList.add("row");
    div1.classList.add("card");
    document.getElementById('newallocationemployeeshare').appendChild(div1);
    div1.children[0].children[2].children[2].id = "delete" + ct;
    document.getElementById('delete' + ct).innerHTML = delLink;
}

function delAllocationEmployeeShareLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newallocationemployeeshare');
    parentEle.removeChild(ele);
}
