$(document).ready(function () {

    $('#select_status').select2({
        placeholder: "انتخاب وضعیت اجرایی پرونده"
    });
    $('#select_status_mali').select2({
        placeholder: "انتخاب وضعیت مالی پرونده"
    });
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
        $('#loading').css('display' , 'block');
        var form = $('#filter_form');
        var formData = form.serialize();
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/totalFactorItem/" + basicUrl + "?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
                $('#sum_price').empty()
                $('#sum_price').append('( جمع کل صفحه : ' + data.meta.sum_price + ' ریال - جمع کل :' + data.meta.total_price + ' ریال )');
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        if (data.meta.total) {
            document.getElementById("page_text").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        }
        else {
            document.getElementById("page_text").innerHTML = null;
        }
        if ($.fn.DataTable.isDataTable('#datatable-json')) {
            $('#datatable-json').DataTable().destroy();
        }

        $('#datatable-json tbody').empty();

        if ($('#report').val() == 'true') {
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
                    { "data": "items" },
                    { "data": "total_price" },
                    { "data": "prepayment_price" },
                    { "data": "installments_price" },
                    { "data": "installments_num" },
                    { "data": "installments_num_paid" },
                    { "data": "extra_pay" },
                    { "data": "start_end_date" },
                    {
                        "targets": 2,
                        "data": "status_mali",
                        "render": function (data, type, row, meta) {
                            switch (data) {
                                case "تایید نشده":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "جاری":
                                    return '<button class="badge badge-success border-0">' + data + '</button>';
                                case "اخطار اول":
                                    return '<button class="badge badge-warning border-0">' + data + '</button>';
                                case "اخطار دوم":
                                    return '<button class="badge badge-warning border-0">' + data + '</button>';
                                case "ارجاع به حقوقی":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "ارجاع به امورقراردادها":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "اتمام اقساط":
                                    return '<button class="badge badge-dark border-0">' + data + '</button>';
                                case "تسویه حساب":
                                    return '<button class="badge badge-dark border-0">' + data + '</button>';
                                case "در انتظار تایید تسویه حساب مالی":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "در انتظار تایید تسویه حساب امور قراردادها":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "در انتظار تایید تسویه حساب مدیر":
                                    return '<button class="badge badge-danger border-0">' + data + '</button>';
                                case "آماده تسویه حساب":
                                    return '<button class="badge badge-dark border-0">' + data + '</button>';
                                case "ارجاع به امور قراردادها":
                                    return '<button class="badge badge-warning border-0">' + data + '</button>';
                                case "منتظر تایید تسویه حساب مالی":
                                    return '<button class="badge badge-primary border-0">' + data + '</button>';
                                default:
                                    return '<button class="badge badge-primary border-0">' + data + '</button>';
                            }
                        }
                    },
                    {
                        "targets": 2,
                        "data": data,
                        "render": function ( data ) {
                            return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                            <a href="../../../dashboard/totalFactorItem/${data.id}/installment" target="_blank">
                            <button type="button" class="dropdown-item pointer">مشاهده اقساط</button></a>
                            <a href="../../../dashboard/totalFactorItem/${data.id}" target="_blank">
                            <button type="button" class="dropdown-item pointer">امور اداری و مالی پرونده</button></a>
                            </div></div>`;
                        }
                    },
                ],
            });
        }
        else {
            var table = $('#datatable-json').DataTable({
                paging: false,
                searching: false,
                retrieve: true,
                data: data.data,

                columns: [
                {"data": "sort_by"},
                {"data": "name"},
                {"data": "phone"},
                {"data": "total_price"},
                {"data": "credit"},
                {"data": "items"},
                {"data": "date"},
                {
                    "targets": 2,
                    "data": "status",
                    "render": function ( data, type, row, meta ) {
                        if(data == "جدید")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "تاییدیه پشتیبان")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "مشاوره")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "تعلیق پشتیبان")
                        {
                            return '<button class="badge badge-warning border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تکمیل مدارک")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید مالی")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "شروع کار")
                        {
                            return '<button class="badge badge-success border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "اتمام کار")
                        {
                            return '<button class="badge badge-dark border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید مدیر")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید امور تسهیلات")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "آماده اجرا")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید مدیر مالی نمایندگی")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید مدیر نمایندگی")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید امور قراردادها")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if(data == "منتظر تایید پشتیبان")
                        {
                            return '<button class="badge badge-danger border-0">'+data+'</button><a h_blank">';
                        }
                        if (data == "پرونده مختومه") {
                            return '<button class="badge badge-dark border-0">' + data + '</button><a h_blank">';
                        }
                    }
                },
                {
                    "targets": 2,
                    "data": "status_mali",
                    "render": function ( data, type, row, meta ) {
                        switch (data) {
                            case "تایید نشده":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "جاری":
                                return '<button class="badge badge-success border-0">' + data + '</button>';
                            case "اخطار اول":
                                return '<button class="badge badge-warning border-0">' + data + '</button>';
                            case "اخطار دوم":
                                return '<button class="badge badge-warning border-0">' + data + '</button>';
                            case "ارجاع به حقوقی":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "ارجاع به امورقراردادها":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "اتمام اقساط":
                                return '<button class="badge badge-dark border-0">' + data + '</button>';
                            case "تسویه حساب":
                                return '<button class="badge badge-dark border-0">' + data + '</button>';
                            case "در انتظار تایید تسویه حساب مالی":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "در انتظار تایید تسویه حساب امور قراردادها":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "در انتظار تایید تسویه حساب مدیر":
                                return '<button class="badge badge-danger border-0">' + data + '</button>';
                            case "آماده تسویه حساب":
                                return '<button class="badge badge-dark border-0">' + data + '</button>';
                            case "ارجاع به امور قراردادها":
                                return '<button class="badge badge-warning border-0">' + data + '</button>';
                            case "منتظر تایید تسویه حساب مالی":
                                return '<button class="badge badge-primary border-0">' + data + '</button>';
                            default:
                                return '<button class="badge badge-primary border-0">' + data + '</button>';
                        }
                   }
                },
                {
                    "data": data,
                    "render" : function (data)
                    {
                        if (data.has_payment){
                            return '<i class="fa fa-check" aria-hidden="true"></i>'
                        }else{
                            return '<i class="fa fa-close" aria-hidden="true"></i>'
                        }
                    }
                },
                {
                    "targets": 2,
                    "data": data,
                    "render": function ( data ) {
                        return `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                        <div class="dropdown-menu">
                        <a href="../../dashboard/totalFactorItem/${data.id}/installment" target="_blank">
                        <button type="button" class="dropdown-item pointer">مشاهده اقساط</button></a>
                        <a href="../../dashboard/totalFactorItem/${data.id}" target="_blank">
                        <button type="button" class="dropdown-item pointer">امور اداری و مالی پرونده</button></a>
                        </div></div>`;
                    }
                },
            ],
            });
        }


        table.on('order.dt search.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = data.meta.from + i;
            });
        }).draw();
    }

    $("body").on('click', '#do_filter', function (event) {
        if ($('#exel').prop('checked') == true) {
            $('#filter_form').submit();
        } else {
            $('#pagination-demo').remove();
            $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
            do_filter_command=true;
            fetchData(1)
            event.preventDefault();
        }
    });
});
