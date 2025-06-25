var basic_doc = $('#basic_documnet_div').html();
var orginal_doc = $('#original_document_div').html();
var registrationInitialConsultation_doc = $('#registrationInitialConsultation').html();
var basic_document_executive_doc = $('#basic_document_executive').html();
//basicDocumentExecutiveAppend
var elements = document.querySelectorAll('div[id^="basic_document_executive_collection_"]');
var basic_document_executive_collection_doc = [];
elements.forEach(element => {
    basic_document_executive_collection_doc[element.id] = $('#' + element.id).html();
});

function basicDocumentExecutiveAppend(basic_document_executive_collection_id) {
    var elements = document.querySelectorAll('div[id^="basic_document_executive_collection_"]');
    elements.forEach(element => {
        $('#' + element.id).empty();
    });
    var basic_document_executive_collection_div = document.createElement('div');
    basic_document_executive_collection_div.innerHTML = basic_document_executive_collection_doc[basic_document_executive_collection_id];
    $('#' + basic_document_executive_collection_id).append(basic_document_executive_collection_div);
};
//consultationReferralAppend
var elements = document.querySelectorAll('div[id^="consultation_referral_collection_"]');
var consultation_referral_collection_doc = [];
elements.forEach(element => {
    consultation_referral_collection_doc[element.id] = $('#' + element.id).html();
});

function consultationReferralAppend(consultation_referral_collection_id) {
    var elements = document.querySelectorAll('div[id^="consultation_referral_collection_"]');
    elements.forEach(element => {
        $('#' + element.id).empty();
    });
    var consultation_referral_collection_div = document.createElement('div');
    consultation_referral_collection_div.innerHTML = consultation_referral_collection_doc[consultation_referral_collection_id];
    $('#' + consultation_referral_collection_id).append(consultation_referral_collection_div);
    document.getElementById("defaultOpen").click();
};
$("body").on('click', '#orginal-document-panel', function () {
    $("#basic_documnet_div").empty();
    $("#original_document_div").empty();
    var original_div = document.createElement('div');
    original_div.innerHTML = orginal_doc;
    $("#original_document_div").append(original_div);
});

$("body").on('click', '#basic-document-tab', function () {
    $("#original_document_div").empty();
    $("#basic_documnet_div").empty();
    var basic_div = document.createElement('div');
    basic_div.innerHTML = basic_doc;
    $("#basic_documnet_div").append(basic_div);
});

$("body").on('click', '#consultation-referral-tab', function () {
    $("#basic_document_executive").empty();
    $("#registrationInitialConsultation").empty();
    var registrationInitialConsultation_div = document.createElement('div');
    registrationInitialConsultation_div.innerHTML = registrationInitialConsultation_doc;
    $("#registrationInitialConsultation").append(registrationInitialConsultation_div);
});

$("body").on('click', '#basic-document-tab', function () {
    $("#registrationInitialConsultation").empty();
    $("#basic_document_executive").empty();
    var basic_document_executive_div = document.createElement('div');
    basic_document_executive_div.innerHTML = basic_document_executive_doc;
    $("#basic_document_executive").append(basic_document_executive_div);
});

$("body").on('change', '#title', function (event) {
    if ($('#title').val() == 'price_difference') {
        $('#factor_item_price_diff').css('display', 'block');
    } else {
        $('#factor_item_price_diff').css('display', 'none');
    }
});

$("#transfer_type").change(function () {
    if (this.value == 'representation') {
        $('#representation_list_box').css('display', 'block');
        $('#supporter_list_box').css('display', 'none');
        $('#center_list_box').css('display', 'none');
    } else if (this.value == 'supporter') {
        $('#supporter_list_box').css('display', 'block');
        $('#representation_list_box').css('display', 'none');
        $('#center_list_box').css('display', 'none');
    } else if (this.value == 'center') {
        $('#center_list_box').css('display', 'block');
        $('#representation_list_box').css('display', 'none');
        $('#supporter_list_box').css('display', 'none');
    }
});

var ct = 0;


function setMobile() {
    if ($('#customCheck').is(':checked')) {
        $('#mobile_supervisor').show();
    } else {
        $('#mobile_supervisor').hide();
    }
}

var count = 3;

function new_payment() {
    count++;
    var div1 = document.createElement('div');
    div1.id = count;
    div1.innerHTML = document.getElementById('newpaymenttpl').innerHTML;
    div1.classList.add("row");
    document.getElementById('newpayment').appendChild(div1);

    $("div#" + count).children("div#payment_date").children("input.date").pDatepicker({
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

    $("div#" + count).children("div.payment").children("select#payment_type3")[0].id = "payment_type" + count;
    document.getElementById('payment_type' + count).onchange = undefined;
    $('#payment_type' + count).change(function () {
        paymentType(count);
    });
    $("div#" + count).children("div#number_card3")[0].id = "number_card" + count;
    $("div#" + count).children("div#payment_code3")[0].id = "payment_code" + count;
    $("div#" + count).children("div#bill_image3")[0].id = "bill_image" + count;
    $("div#" + count).children("div#number_card" + count).children("input#number_card_input3")[0].id = "number_card_input" + count;
    $("div#" + count).children("div#payment_code" + count).children("input#payment_code_input3")[0].id = "payment_code_input" + count;
    $("div#" + count).children("div#bill_image" + count).children("input#bill_image_input3")[0].id = "bill_image_input" + count;
}

function updateSession(ele) {
    var id = $(ele).attr('data-id');
    var values = $('#factor_item_service_tariff_id' + id).find('option:selected');
    var token = $("meta[name='csrf-token']").attr("content");
    var factorItemServiceTariffId = [];
    values.each(function () {
        factorItemServiceTariffId.push($(this).val());
    });
    document.getElementById('submit' + id).value = "ارسال ...";
    document.getElementById('submit' + id).disabled = true;

    $.ajax({
        url: "../../session/update",
        type: 'POST',
        data: {
            '_token': token,
            'id': id,
            'description': document.getElementById('description' + id).value,
            'type': 'edit',
            'factor_item_service_tariff_id': factorItemServiceTariffId,
            'laboratory_price': document.getElementById('laboratory_price' + id).value,
            'agent_id': document.getElementById('agent_id' + id).value,
            'assistant_id': document.getElementById('assistant_id' + id).value,
        },
        success: function (response) {
            if (response.errors) {
                toastr.error('لطفا تمام مقادیر را پر کنید');
                document.getElementById('submit' + id).value = "ثبت"
                document.getElementById('submit' + id).disabled = false;
            } else {
                document.getElementById('submit' + id).value = "ثبت"
                toastr.success(response.msg);
                location.reload();
            }
        },
        error: function () {
            toastr.error('لطفا تمام مقادیر را پر کنید');
            document.getElementById('submit' + id).value = "ثبت"
            document.getElementById('submit' + id).disabled = false;
        }
    });
}

function printDiv(divName) {
    $('.margin-change-box').hide();
    setInterval(function () {
        $('.margin-change-box').show();
    }, 1000);
    var printContents = $('#' + divName).html();
    var originalContents = $('body').html();
    $('body').html(printContents);
    window.print();
    $('body').html(originalContents);
}

function job() {
    var jobType = $('#jobType').val();
    var typeWorkplace = $('#typeWorkplace').val();

    if (jobType == 'homemaker') {
        $('#divJobName,#divPensionaryType,#divCompanyName,#divProvince,#divOrganName,#divTypeWorkplace').hide();
    }
    if (jobType == 'freelance') {
        $('#divJobName').show();
        $('#divPensionaryType,#divCompanyName,#divProvince,#divOrganName,#divTypeWorkplace').hide();
    }
    if (jobType == 'pensionary') {
        $('#divJobName,#divPensionaryType').show();
        $('#divCompanyName,#divProvince,#divOrganName,#divTypeWorkplace').hide();
    }
    if (jobType == 'employee') {
        $('#divTypeWorkplace').show();
        if (typeWorkplace == 'governmental') {
            $('#divProvince,#divOrganName').show();
            $('#divCompanyName,#divJobName,#divPensionaryType').hide();
        } else {
            $('#divCompanyName').show();
            $('#divJobName,#divPensionaryType,#divProvince,#divOrganName').hide();
        }
    }
}

$("body").on('change', '.job', function (event) {
    job();
})

function catType(catType, id) {
    if (catType == "category") {
        $('#category' + id).show();
        $('#service' + id).hide();
    } else {
        $('#category' + id).hide();
        $('#service' + id).show();
    }
}

function selectByTariff(id, value) {
    id = id.slice(6);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    factor_item_id = document.getElementById('factorItem').value;
    if (value == 0) {
        $('#service_tariff' + id).find('option').remove();
    }

    if (value != 0) {
        $.ajax({
            url: '/dashboard/serviceTariff/selectByTariff',
            type: 'post',
            dataType: 'json',
            data: {
                id: value,
                factor_item_id: factor_item_id,
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#service_tariff' + id).find('option').remove();
                    $('#service_tariff' + id).append('<option value="" selected disabled>انتخاب کنید</option>');
                    for (var i = 0; i < data.service_tariffs.data.length; i++) {
                        $('#service_tariff' + id).append('<option value="' + data.service_tariffs.data[i]['id'] + '">' + data.service_tariffs.data[i]['name'] + ' ( ' + data.service_tariffs.data[i]['selling_price'] + 'ریال  -  تعرفه ' + data.service_tariffs.data[i]['tariff'] + ' ) </option>');
                    }

                    select = $('#service_tariff' + id).select2({
                        placeholder: "انتخاب کنید",
                        allowClear: false,
                        minimumResultsForSearch: 5
                    });
                }
            }
        });
    }
}

function getSessionNumber(id, value) {
    id = id.slice(14);
    if (value != 0) {
        $.ajax({
            url: '/dashboard/serviceTariff/getSessionNumber',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    document.getElementById('session_number' + id).value = data.service_tariff['session_number'];
                    document.getElementById('treatment_duration' + id).value = data.service_tariff['treatment_duration'];
                }
            }
        });
    }
}

function treatmentPlan() {
    ct++;
    var div1 = document.createElement('div');
    div1.classList.add("row");
    div1.classList.add("treatment_plan");
    div1.id = ct;
    var delLink = '<div class="mr-3 btn badge-red"><a class="text-white" href="javascript:delTreatmentPlan(' + ct + ')"><i class="fa fa-trash" aria-hidden="true"></i></a></div>';
    div1.innerHTML = document.getElementById('treatment_plan').innerHTML + delLink;
    document.getElementById('new_treatment_plan').appendChild(div1);
    div1.children[3].children[1].id = "tariff" + ct;
    div1.children[4].children[1].id = "service_tariff" + ct;
    div1.children[5].children[1].id = "session_number" + ct;
    div1.children[6].children[1].id = "treatment_duration" + ct;
    div1.children[4].children[1].classList.add("select2");
    selectByTariff("tariff" + ct, document.getElementById("tariff" + ct).value);
}

function delTreatmentPlan(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('new_treatment_plan');
    parentEle.removeChild(ele);
}

$('.work-step').click();

function stepWorkDiagram(session_number, id, done_session_count) {
    rotate = 360;
    diff = 360 / session_number;
    percent = Math.round((done_session_count / session_number) * 100) + '%';
    $('#percent_' + id).text(percent);
    switch (session_number) {
        case 3:
            skew_y = 30;
            break;
        case 4:
            skew_y = 0.1;
            break;
        case 5:
            skew_y = -17;
            break;
        case 6:
            skew_y = -30;
            break;
        default:
            skew_y = 43;
            break;
    }

    for (i = 1; i <= session_number; i++) {
        $('#part_' + id + '_' + i).css('transform', 'rotate(' + rotate + 'deg) skewY(' + skew_y + 'deg)');
        if (session_number == 2) {
            $('#part_' + id + '_' + i).css('width', '132%');
            $('#part_' + id + '_' + i).css('height', '170%');
            $('#part_' + id + '_' + i).css('right', '-55px');
            i == 2 ? $('#part_' + id + '_' + i).css('top', '-115px') : '';

        }
        if (session_number == 1) {
            $('#part_' + id + '_' + i).css('width', '194%');
            $('#part_' + id + '_' + i).css('height', '176%');
            $('#part_' + id + '_' + i).css('top', '-104px');
        }
        rotate = rotate - diff;
    }

    for (index = 1; index <= done_session_count; index++) {
        $('#part_' + id + '_' + index).children().css('background', '#75cf33');
    }
}

$(document).ready(function () {
    //Advance Tabs
    $(".next").click(function () {
        const nextTabLinkEl = $(".process .active")
            .closest("li")
            .next("li")
            .find("a")[0];
        const nextTab = new bootstrap.Tab(nextTabLinkEl);
        nextTab.show();
    });

    $(".previous").click(function () {
        const prevTabLinkEl = $(".process .active")
            .closest("li")
            .prev("li")
            .find("a")[0];
        const prevTab = new bootstrap.Tab(prevTabLinkEl);
        prevTab.show();
    });


    /*  submit comment for factor */
    $("body").on('click', '#submit-factor-comment', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#factor-comment-form');
        var formData = form.serialize();
        swal({
            title: "اطلاعات ارسال گردید !",
            timer: 2000
        });
        $.ajax({
            url: "/dashboard/factor-comment",
            type: 'post',
            "_token": token,
            data: formData,
            success: function (response) {
                swal("عملیات موفق", "", "success")
                $('#comment-body').val('');
                $('#commentModal').modal('hide');
                $('.comment-item').prepend(`
                     <div class="bg-light rounded p-3 mb-3" style="background: #e3f8e3!important;border: solid #7fef7f">
                     <span class="alert alert-success"><i class="fa fa-check " aria-hidden="true"></i>
             ثبت گردید</span>
                     <hr>
                        <div class="panel-heading">
                            <span class="d-inline-block">${response.data.user.name + ' ' + response.data.user.family}</span>
                            <span class="text-black-50">${response.data.comment.jalali_created_at}</span>
                        </div>
                        <p class="mt-4">
                            ${response.data.comment.comment}
                        </p>
                    </div>
                    `)
            }
        });
    });
});

//----------------------------------------------------------------------------------------------
//create table & fetch data table & filter
$(document).ready(function () {
    $("body").on('click', '.factor_item_service_tariff_delete', function () {
        var id = $(this).data("id");
        var token = $("meta[name='csrf-token']").attr("content");
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
                        url: "/dashboard/factorItemServiceTariff/" + id,
                        type: 'DELETE',
                        data: {
                            "_token": token,
                        },
                        success: function (response) {
                            if (response.errors) {
                                toastr.error(response.errors);
                            } else {
                                toastr.success(response.msg);
                                location.reload();
                            }
                        }
                    });
                }
            });
        }
    });

    $('#select_status').select2({
        placeholder: "انتخاب وضعیت پرونده"
    });


    /*    if checkbox of transfer checked for all factorItems   */
    $('#transfer_checkbox').change(function () {
        if ($(this).is(':checked')) {
            $('body td input:checkbox').prop('checked', this.checked);
        } else {
            $('body td input:checkbox').prop('checked', false);
        }
    });
    /*    click on do transfer */
    $("body").on('click', '#do_transfer', function (event) {
        var token = $('meta[name="csrf-token"]').attr('content');
        var form = $('#transfer_form');
        var formData = form.serialize();
        $.ajax({
            url: "/dashboard/factorItem/transfer",
            type: 'post',
            "_token": token,
            data: formData,
            success: function (response) {
                if (response.status == 'success') {
                    swal(response.messages, {
                        icon: "success",
                        button: "باشه"
                    });
                    fetchData(1);
                } else {
                    $('.transfer_message_box').empty();
                    $.each(response.messages, function (i, val) {
                        $('.transfer_message_box').append(`<span class="alert alert-danger">${val}</span>`);
                    });
                }
            }
        });
    });
    fetchData(1)
    var first_load = true;
    var do_filter_command = false;
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
    //fetch data
    function fetchData(page) {
        $('#loading').css('display', 'block');
        var form = $('#filter_form');
        var formData = form.serialize();
        var basicUrl = $('#url').val();

        $.ajax({
            url: "/dashboard/factorItem/" + basicUrl + "?page=" + page,
            data: formData,
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
                $('#sum_price').empty()
                $('#sum_price').append('( جمع کل صفحه : ' + data.meta.sum_price + ' ریال - جمع کل :' + data.meta.total_price + ' ریال )');
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
                    "targets": 1,
                    "data": data,
                    "render": function (data) {
                        return `<input type="checkbox" name="id[]" value="${data.id}">`
                    }
                },
                {
                    "data": "name"
                },
                {
                    "data": "phone"
                },
                {
                    "data": "date"
                },
                {
                    "data": "date_time"
                },
                {
                    "data": "supporter"
                },
                {
                    "data": "type"
                },
                {
                    "data": "items"
                },
                {
                    "data": "representation"
                },
                {
                    "data": data,
                    "render": function (data) {
                        switch (data.status) {
                            case 0:
                                return `<span class="badge badge-danger">جدید</span>`;
                                break;
                            case 1:
                                return `<span class="badge badge-warning">تاییدیه پشتیبان</span>`;
                                break;
                            case 2:
                                return `<span class="badge badge-warning">مشاوره شد</span>`;
                                break;
                            case 3:
                                return `<span class="badge badge-warning">تعلیق پشتیبان</span>`;
                                break;
                            case 4:
                                return `<span class="badge badge-danger">منتظر تکمیل مدارک</span>`;
                                break;
                            case 5:
                                return `<span class="badge badge-danger">منتظرتایید مالی</span>`;
                                break;
                            case 6:
                                return `<span class="badge badge-success">شروع کار</span>`;
                                break;
                            case 7:
                                return `<span class="badge badge-dark">اتمام کار</span>`;
                                break;
                            case 8:
                                return `<span class="badge badge-danger">منتظر تایید مدیر</span>`;
                                break;
                            case 9:
                                return `<span class="badge badge-danger">منتظر تایید امور تسهیلات</span>`;
                                break;
                            case 10:
                                return `<span class="badge badge-danger">آماده اجرا</span>`;
                                break;
                            case 11:
                                return `<span class="badge badge-danger">منتظر تایید مالی نمایندگی</span>`;
                                break;
                            case 12:
                                return `<span class="badge badge-danger">منتظر تایید مدیر نمایندگی</span>`;
                                break;
                            case 13:
                                return `<span class="badge badge-danger">منتظر تایید امور قراردادها</span>`;
                                break;
                            case 14:
                                return `<span class="badge badge-danger">منتظر تایید پشتیبان</span>`;
                                break;
                            case 15:
                                return `<span class="badge badge-dark">پرونده مختومه</span>`;
                                break;
                            case 16:
                                return `<span class="badge badge-danger">ارجاع به کارشناسی</span>`;
                                break;
                            case 17:
                                return `<span class="badge badge-warning"> منتظر مشاوره مرکز</span>`;
                                break;
                            case 18:
                                return `<span class="badge badge-purple">منتظر تایید قرارداد توسط کاربر</span>`;
                                break;
                            default:
                                return data.status;
                        }
                    }
                },
                {
                    "data": data,
                    "render": function (data) {
                        if (data.has_payment) {
                            return '<i class="fa fa-check" aria-hidden="true"></i>'
                        } else {
                            return '<i class="fa fa-close" aria-hidden="true"></i>'
                        }
                    }
                },
                {
                    "data": "credit_debt"
                },
                {
                    "data": data,
                    "render": function (data) {
                        let output = `<div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">`
                        if (data.total_factor_item_id) {
                            output += `<a target="_blank" href="/dashboard/totalFactorItem/${data.total_factor_item_id}/installment">
                                <span data-id="${data.total_factor_item_id}" class="task-delete dropdown-item pointer">
                                مشاهده اقساط</span></a>`
                        }
                        output += `
                            <a target="_blank" href="/dashboard/factorItem/${data.resnumber}">
                            <span  data-id="${data.id}" class="task-delete dropdown-item pointer" >
                            امور اداری و مالی پرونده
                            </span></a>
                            <a target="_blank" href="/dashboard/factorItem/${data.resnumber}/edit">
                            <span  data-id="${data.id}" class="task-delete dropdown-item pointer">
                            امور اجرایی پرونده
                            </span></a>
                        </div></div>`
                        return output;
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


});

function marginPlus(thisItem) {
    $(thisItem).parent().parent().next().css('margin-top', '+=10px');
}

function marginMinus(thisItem) {
    $(thisItem).parent().parent().next().css('margin-top', '-=10px');
}

$("body").on('change', 'input[name=type]', function (event) {
    $('#changeFinancialPlansTypeModal').modal('show');
    if (this.value == 0) {
        $('input[name=type][value=1]').prop('checked', false);
        $('input[name=type][value=0]').prop('checked', true);
    } else {
        $('input[name=type][value=0]').prop('checked', false);
        $('input[name=type][value=1]').prop('checked', true);
    }
    factor_item_id = $('#factor_item').val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: '/dashboard/financialPlansType/setByPaymentType',
        type: 'post',
        dataType: 'json',
        data: {
            payment_type: this.value,
            factor_item_id: factor_item_id,
        },
        success: function success(data) {
            if (data.count == 'available') {
                $('#financial_plans_type').find('option').remove();
                $.each(data.financial_plans_types, function (index, value) {
                    $('#financial_plans_type').append('<option value="' + value['id'] + '">' + value['label'] + '</option>');
                });
            }
        }
    });
});

//guarantor checked
$("body").on('click', '.guarantor_checked', function () {
    guarantorChecked();
});

function guarantorChecked()
{
    if ($('.guarantor_checked').prop('checked') == true) {
        $('.guarantor_info').css('display', 'block');
    } else {
        $('.guarantor_info').css('display', 'none');
    }
}
