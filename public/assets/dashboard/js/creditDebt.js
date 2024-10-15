$(document).ready(function () {
    var first_load = true;
    var do_filter_command = false;

    /*    click on create comment form factor_item  */
    $("body").on('click', '.factor-item-create-comment', function () {
        $('#factor-id-input').val($(this).data('id'))
    });

    /*    click on do filter */
    $("body").on('click', '#do-store-comment', function (event) {
        var action = $('#store-comment').attr('action');
        var form = $('#store-comment');
        var formData = form.serialize();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            }
        });
        $.ajax({
            url: action,
            data: formData,
            type: "post",
            success: function (data) {
                if (data.status == 'success') {
                    swal("توضیح با موفقیت ثبت گردید !", "", "success");
                    // prepend to the comment list ;
                    $('.histories-table-body').prepend(`
                <tr>
                <td>
                        <span class="text-dark badge badge-success">جدید</span>
                  </td>
                <td>
                    ${data.data.comment.user.name}  ${data.data.comment.user.family}
                  </td>
                <td>
                    ${data.data.comment.jalali_created_at}
                </td>
                <td>
                    ${data.data.comment.comment}
                </td>
                </tr>
                `)
                }
            }
        });
    });

    fetchDataCreditDebt(1);
    function pagination(totalPages) {
        $('#pagination-demo-creditDebt').twbsPagination({
            totalPages: totalPages,
            visiblePages: totalPages >= 5 ? 5 : totalPages,
            next: '>',
            prev: '<',
            onPageClick: function (event, page) {
                $('#excel-creditDebt').prop('checked', false);
                //fetch content and render here
                if (!first_load && !do_filter_command) {
                    fetchDataCreditDebt(page);
                }
                first_load = false
                do_filter_command = false
            }
        });
    }

    function fetchDataCreditDebt(page) {
        document.getElementById('loading_creditDebt').style.display = 'block';
        var form = $('#filter_form_creditDebt');
        var formData = form.serialize();
        $.ajax({
            url: "/dashboard/accounting/filter-credit-debt/?page=" + page,

            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading_creditDebt').style.display = 'none';
            }
        });
    }

    function createTable(data) {
        pagination(data.meta.last_page);
        document.getElementById("page_text_creditDebt").innerHTML = ' نمایش ' + data.meta.from + ' تا ' + data.meta.to + ' از ' + data.meta.total + ' آیتم ';
        let sum_debts = 0;
        $.each(data.data, function (index, value) {
            $.each(value.total_debts, function (index, value) {
                sum_debts += value.debt
            });
        });
        $('#total-price-page-creditDebt').empty()
        $('#total-price-page-creditDebt').append(separate(` _ جمع بدهی کل این صفحه : (${Math.round(sum_debts  )} ریال) `))
        if (data.meta.all_users_debt_except_penalty != 0) {
            $('#total-price-page-creditDebt').append(separate(` _ جمع بدهی کل  : (${Math.round(data.meta.all_users_debt_except_penalty )} ریال) `))
        }
        if ($.fn.DataTable.isDataTable('#datatable-json-creditDebt')) {
            $('#datatable-json-creditDebt').DataTable().destroy();
        }

        $('#datatable-json-creditDebt tbody').empty();
        var table = $('#datatable-json-creditDebt').DataTable({
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
                    "data": data,
                    "render": function (data) {
                        return data.name + ' ' + data.family
                    }
                },
                {
                    "data": "phone"
                },
                {
                    "data": "user_credit"
                },
                {
                    "data": "user_wallet"
                },
                {
                    "data": "factorItems_credit"
                },
                {
                    "data": "credit_debt"
                },
                {
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        return `<div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                            <a  target="_blank" href="../../dashboard/accounting/show/${data.id}">
                                <button type="button" class="dropdown-item pointer">مشاهده عملکرد </button>
                            </a>
                            </div>
                            </div>`;
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

    //$('#myForm').attr('action');
    /*    click on do filter */
    $("body").on('click', '#do_filter_creditDebt', function (event) {
        let error = false;
        if ($('#by_amount_creditDebt').is(':checked')) {
            if ($('#start-amount-creditDebt').val() >= $('#end-amount-creditDebt').val() && !($('#start-amount-creditDebt').val() == '' || $('#end-amount-creditDebt').val() == '')) {
                swal({
                    icon: 'error',
                    title: 'خطا',
                    text: 'لطفا پارامتر تا مبلغ را بزرگتر از پارامتر از مبلغ وارد نمایید!'
                })
                error = true;
                event.preventDefault();
            } else if ($('#start-amount-creditDebt').val() == '' || $('#end-amount-creditDebt').val() == '') {
                swal({
                    icon: 'error',
                    title: 'خطا',
                    text: 'لطفا هر دو پارامتر مبلغ را وارد نمایید!'
                })
                error = true;
                event.preventDefault();
            }
        }
        if (!error) {
            if ($('#excel-creditDebt').prop('checked') == true) {
                $('form').submit();
            } else {
                do_filter_command = true;
                $('#pagination-demo-creditDebt').remove();
                $('#pagination-box-creditDebt').html("<ul id='pagination-demo-creditDebt' class='pagination'></ul>");
                fetchDataCreditDebt(1)
                event.preventDefault();
            }
        }
    });
});

function separate(Number) {
    Number += '';
    Number = Number.replace(',', '');
    x = Number.split('.');
    y = x[0];
    z = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(y))
        y = y.replace(rgx, '$1' + ',' + '$2');
    return y + z;
}

function startAmountOnChangeCreditDebt() {
    $('#start-amount-badge-creditDebt').empty()
    var start_amount_creditDebt = $('#start-amount-creditDebt').val();
    start_amount_creditDebt = start_amount_creditDebt.replace (/,/g, "");
    $('#start-amount-badge-creditDebt').append(' از ' + separate(Math.round(start_amount_creditDebt / 10)) + ' تومان ')
}

function endAmountOnChangeCreditDebt() {
    $('#end-amount-badge-creditDebt').empty()
    var end_amount_creditDebt = $('#end-amount-creditDebt').val();
    end_amount_creditDebt = end_amount_creditDebt.replace (/,/g, "");
    $('#end-amount-badge-creditDebt').append(' تا ' + separate(Math.round(end_amount_creditDebt / 10)) + ' تومان ')
}
