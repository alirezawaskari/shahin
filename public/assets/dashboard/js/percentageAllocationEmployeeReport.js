$(document).ready(function () {

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
                    "data": "price"
                },
                {
                    "data": "description"
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
                                    `;
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
    } else {
        $('input[name=' + this.name + '_percent]').css('display', 'none');
    }
});

function selectAll(select_id) {
    $("#" + select_id + "> option").prop("selected", true);
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
                    $('#center_id').append('<option disabled>انتخاب مرکز</option>');
                    for (var i = 0; i < data.centers.length; i++) {
                        $('#center_id').append('<option value="' + data.centers[i]['id'] + '">' + data.centers[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}
