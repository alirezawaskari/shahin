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
            url: "/dashboard/accounting/filter-cash-debtors/?page=" + page,

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
        let sum_debts = 0;
        $.each(data.data, function (index, value) {
            $.each(value.total_debts, function (index, value) {
                sum_debts += value.debt
            });
        });
        $('#total-price-page').empty()
        $('#total-price-page').append(separate(` _ جمع بدهی کل این صفحه : (${Math.round(sum_debts  )} ریال) `))
        if (data.meta.all_users_debt_except_penalty != 0) {
            $('#total-price-page').append(separate(` _ جمع بدهی کل  : (${Math.round(data.meta.all_users_debt_except_penalty )} ریال) `))
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
                    "data": data,
                    "render": function (data) {
                        var extra_work = false;
                        $.each(data.total_debts, function (index, value) {
                            if (value.extra_work > 0) {
                                extra_work = true;
                            }
                        });
                        if (extra_work) {
                            return '<i class="fa fa-check text-danger" aria-hidden="true"></i>';
                        } else {
                            return ''
                        }
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        var prepayment_debt = 0;
                        $.each(data.total_debts, function (index, value) {
                            prepayment_debt = prepayment_debt + value.prepayment_debt;
                        });
                        return separate(Math.round(prepayment_debt));
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        var price_difference_debt = 0;
                        $.each(data.total_debts, function (index, value) {
                            price_difference_debt = price_difference_debt + value.price_difference_debt;
                        });
                        return separate(Math.round(price_difference_debt));
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        var expire_installments_price = 0;
                        $.each(data.total_debts, function (index, value) {
                            expire_installments_price = expire_installments_price + value.expire_installments_price;
                        });
                        // return separate(debt);
                        return separate(Math.round(expire_installments_price));
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        let start_amount = $('#start-amount').val();
                        let end_amount = $('#end-amount').val();
                        var debt = 0;
                        $.each(data.total_debts, function (index, value) {
                            debt = debt + value.debt;
                        });
                        if ($('#by_amount').prop('checked') == true) {
                            if (debt > start_amount && debt < end_amount) {
                                return `<i class="fa fa-search ml-2" aria-hidden="true" ></i>` + separate(Math.round(debt));
                            } else {
                                return separate(Math.round(debt));
                            }
                        } else {
                            return separate(Math.round(debt));
                        }
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        let debt = '';
                        if (data['debt']['factorItems_prepayment_debt_count'] > 0) {
                            debt = debt.concat(`<span class="badge badge-danger">
                                   پیش پرداخت
                            </span>`)
                        }
                        if (data['debt']['factorItems_price_difference_debt_count'] > 0) {
                            debt = debt.concat(`
                              <span class="badge badge-primary"> اختلاف حساب</span>`)
                        }
                        if (data['debt']['factorItems_installments_debt_count'] > 0) {
                            debt = debt.concat(`
                             <span class="badge badge-warning">
                             اقساط
                            </span>`)
                        }
                        return debt;
                    }
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
    $("body").on('click', '#do_filter', function (event) {
        let error = false;
        if ($('#by_amount').is(':checked')) {
            if ($('#start-amount').val() >= $('#end-amount').val() && !($('#start-amount').val() == '' || $('#end-amount').val() == '')) {
                swal({
                    icon: 'error',
                    title: 'خطا',
                    text: 'لطفا پارامتر تا مبلغ را بزرگتر از پارامتر از مبلغ وارد نمایید!'
                })
                error = true;
                event.preventDefault();
            } else if ($('#start-amount').val() == '' || $('#end-amount').val() == '') {
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
            if ($('#excel').prop('checked') == true) {
                $('form').submit();
            } else {
                do_filter_command = true;
                $('#pagination-demo').remove();
                $('#pagination-box').html("<ul id='pagination-demo' class='pagination'></ul>");
                fetchData(1)
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

function startAmountOnChange() {
    $('#start-amount-badge').empty()
    var start_amount = $('#start-amount').val();
    start_amount = start_amount.replace (/,/g, "");
    $('#start-amount-badge').append(' از ' + separate(Math.round(start_amount / 10)) + ' تومان ')
}

function endAmountOnChange() {
    $('#end-amount-badge').empty()
    var end_amount = $('#end-amount').val();
    end_amount = end_amount.replace (/,/g, "");
    $('#end-amount-badge').append(' تا ' + separate(Math.round(end_amount / 10)) + ' تومان ')
}

function selectStatusOnChange() {
    if ($('#select-status').val() == 3) {
        $('#by_amount').prop("checked", false);
    }
}
