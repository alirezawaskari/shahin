$(document).ready(function () {
    $('#role').on('change', function () {
        var id = $(this).val();
        $.ajax({
            url: '/dashboard/role/get-user-by-role',
            type: 'get',
            data: {
                "id": id,
            },
            success: function (data) {
                $('#users')
                    .empty()
                    .append('<option value="all">انتخاب همکار مربوطه</option>');
                $.each(data.data.users, function (i, d) {
                    $('#users').append('<option value="' + d.id + '">' + d.name + " " + d.family + '</option>');
                });
            }
        });
    });

    fetchData(1);
    var first_load = true;
    var do_filter_command = false;

    function pagination(totalPages) {
        $('#pagination-demo').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel').prop('checked', false);
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
        document.getElementById('loading').style.display = 'block';
        var form = $('#filter_form');
        var formData = form.serialize();

        $.ajax({
            url: $('#filter_form').attr('action') + "/?page=" + page,
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
                    "data": "family"
                },
                {
                    "data": "phone"
                },
                {
                    "data": "type"
                },
                {
                    "data": "item"
                },
                {
                    "data": "percent"
                },
                {
                    "data": "factor_sender"
                },
                {
                    "data": "start_date"
                },
                {
                    "data": "end_date"
                },
                {
                    "data": "agent"
                },
                {
                    "data": "date"
                },
                {
                    "data": data,
                    "render": function (data) {
                        let output = `<div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu">
                                    <a target="_blank" href="../../dashboard/percentageAllocationEmployee/${data.id}/edit">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button></a>
                                    `;
                        if (data.per_delete) {
                            output = output + ` <button  data-id="${data.id}" class="percentage_allocation_employee_delete dropdown-item pointer">حذف</button>
                                </div>
                                </div>`
                        } else {
                            output = output + `</div></div>`;
                        }
                        return output;
                    }
                },
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
    /*    click on do filter */
    $("body").on('click', '#do_filter', function (event) {
        if ($('#excel').prop('checked') == true) {
            $('form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command = true;
            fetchData(1)
            event.preventDefault();
        }
    });
});
$(".checkbox_type").change(function () {
    if (this.checked) {
        $('input[name=' + this.name + '_percent]').css('display', 'block');
        $('select[name=' + this.name + '_factor_sender]').css('display', 'block');
    } else {
        $('input[name=' + this.name + '_percent]').css('display', 'none');
        $('select[name=' + this.name + '_factor_sender]').css('display', 'none');
    }
});
$(".factor_sender").change(function () {
    if (this.value == 'employee_factors') {
        $('select[name=' + this.id + '_limit_type]').css('display', 'block');
    } else {
        $('select[name=' + this.id + '_limit_type]').css('display', 'none');
    }
});
$(".limit_type").change(function () {
    if (this.value == 'count') {
        $('#'+this.name +'_count').css('display', 'block');
        $('#'+this.name +'_price').css('display', 'none');
    }
    else if (this.value == 'price') {
        $('#'+this.name +'_price').css('display', 'block');
        $('#'+this.name +'_count').css('display', 'none');
    }
    else
    {
        $('#'+this.name +'_price').css('display', 'none');
        $('#'+this.name +'_count').css('display', 'none');
    }
});

function checkSelectValue(select_id)
{
    select = document.querySelector("#"+select_id);
    selected_option = select.options[select.selectedIndex].value;
    if(selected_option == 'select_all')
    {
        selectAll(select_id);
    }
    if(selected_option == 'de_select_all')
    {
        deselectAll(select_id);
    }
}
function selectAll(select_id) {
    $("#" + select_id + "> option").prop("selected", true);
    document.getElementById(select_id).options[0].selected=false;
    document.getElementById(select_id).options[1].selected=false;
    document.getElementById(select_id).options[2].selected=false;
    $("#" + select_id).trigger("change");
}

function deselectAll(select_id) {
    $("#" + select_id + "> option").prop("selected", false);
    $("#" + select_id).trigger("change");
}

function selectByCategory() {
    value = $('#service_category_id').val();
    if (value == []) {
        $('#service_id').find('option').remove();
    }

    if (value != []) {
        $.ajax({
            url: '/dashboard/service/selectByCategory',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#service_id').find('option').remove();
                    $('#service_id').append('<option disabled>انتخاب خدمات</option>');
                    $('#service_id').append('<option value="select_all">انتخاب همه</option>');
                    $('#service_id').append('<option value="de_select_all">حذف همه</option>');
                    for (var i = 0; i < data.services.length; i++) {
                        $('#service_id').append('<option value="' + data.services[i]['id'] + '">' + data.services[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}

function selectByService() {
    value = $('#service_id').val();
    if (value == []) {
        $('#center_id').find('option').remove();
    }

    if (value != []) {
        $.ajax({
            url: '/dashboard/center/selectByService',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#center_id').find('option').remove();
                    $('#center_id').append('<option disabled>انتخاب شعبه</option>');
                    $('#center_id').append('<option value="select_all">انتخاب همه</option>');
                    $('#center_id').append('<option value="de_select_all">حذف همه</option>');
                    for (var i = 0; i < data.centers.length; i++) {
                        $('#center_id').append('<option value="' + data.centers[i]['id'] + '">' + data.centers[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('change', '.percent-input', function () {
        const input = $(this);
        const id = input.data('id');
        const percent = input.val();

        $.ajax({
            url: '{{ route("percentageAllocationEmployee.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                percent: percent
            },
            success: function (response) {
                Swal.fire('موفق', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('خطا', xhr.responseJSON?.message || 'خطای نامشخص', 'error');
            }
        });
    });
    //--------------------delete-percentageAllocationEmployee--------------------
    $("body").on('click', '.percentage_allocation_employee_delete', function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");

        swal({
            title: "آیا مطمئن هستید؟",
            icon: "warning",
            buttons: {
                confirm: 'بله',
                cancel: 'خیر'
            },
            dangerMode: true
        }).then(res => {
            if (res) {
                $.ajax({
                    url: "/dashboard/percentageAllocationEmployee/" + id,
                    type: 'DELETE',
                    data: {
                        "_token": token,
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            // حذف ردیف از جدول DataTable
                            var table = $('#datatable-json').DataTable();
                            // پیدا کردن ردیف با id حذف شده
                            var row = table.rows().nodes().to$().filter(function() {
                                return $(this).find('td').eq( /* شماره ستون id یا سلولی که id توش هست */ ).text() == data.id;
                            });

                            if(row.length){
                                table.row(row).remove().draw();
                            }
                            $("#do_filter").click();
                            swal({
                                text: "با موفقیت حذف گردید.",
                                icon: "success",
                                button: false,
                                timer: 2000,
                            });
                        } else {
                            swal("خطا!", data.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal("خطا!", "خطایی در ارتباط با سرور رخ داده.", 'error');
                    }
                });
            }
        });
    });
    document.querySelectorAll('button[data-bs-toggle="collapse"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const selector = btn.getAttribute('data-bs-target');
            const targets = document.querySelectorAll(selector);
            const icon = btn.querySelector('i');

            let anyIsOpen = false;

            targets.forEach(function (el) {
                if (el.classList.contains('show')) {
                    el.classList.remove('show');
                } else {
                    el.classList.add('show');
                    anyIsOpen = true;
                }
            });

            if (anyIsOpen) {
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
        });
    });
});
