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

//select province
$('#province_id').on('change', function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var id = $(this).val();
    $.ajax({
        url: '/city/select',
        type: 'post',
        dataType: 'json',
        data: {
            "_token": token,
            "id": id,
        },
        success: function (data) {
            $('#city_id')
                .empty()
                .append('<option value="all">انتخاب شهر</option>');
            $.each(data.cities, function (i, d) {
                $('#city_id').append('<option value="' + d.id + '">' + d.name + '</option>');
            });
        }
    });
});

function paymentType(title) {
    payment_type = document.getElementById('payment_type' + title).value;
    if (payment_type == "card_reader" || payment_type == "card") {
        document.getElementById('number_card' + title).style.display = 'block';
        document.getElementById('payment_code' + title).style.display = 'block';
        document.getElementById('number_card_input' + title).required = true;
        document.getElementById('payment_code_input' + title).required = true;
    } else {
        document.getElementById('number_card' + title).style.display = 'none';
        document.getElementById('payment_code' + title).style.display = 'none';
        document.getElementById('number_card_input' + title).required = false;
        document.getElementById('payment_code_input' + title).required = false;
    }
    if (payment_type == "cash" || payment_type == "card_reader" || payment_type == "card" || payment_type == "check") {
        document.getElementById('bill_image' + title).style.display = 'block';
        document.getElementById('bill_image_input' + title).required = true;
    } else {
        document.getElementById('bill_image' + title).style.display = 'none';
        document.getElementById('bill_image_input' + title).required = false;

    }
    if (payment_type == "credit" || payment_type == "wallet") {
        document.getElementById('password' + title).style.display = 'block';
        document.getElementById('send_password' + title).style.display = 'block';
        document.getElementById('password_input' + title).required = true;
    } else if (document.getElementById('password' + title)) {
        document.getElementById('password' + title).style.display = 'none';
        document.getElementById('send_password' + title).style.display = 'none';
        document.getElementById('password_input' + title).required = false;
    }
    if (payment_type == "account") {
        document.getElementById('sheba_number' + title).style.display = 'block';
        document.getElementById('sheba_number_input' + title).required = true;
    } else if (document.getElementById('sheba_number' + title)) {
        document.getElementById('sheba_number' + title).style.display = 'none';
        document.getElementById('sheba_number_input' + title).required = false;
    }
    if (payment_type == "supervisor") {
        document.getElementById('mobile_supervisor' + title).style.display = 'block';
        document.getElementById('mobile_supervisor_input' + title).required = true;
    } else if (document.getElementById('mobile_supervisor' + title)) {
        document.getElementById('mobile_supervisor' + title).style.display = 'none';
        document.getElementById('mobile_supervisor_input' + title).required = false;
    }
    if (payment_type == "deduction_price_difference") {
        $('#factor_item_price_diff' + title).css('display', 'block');
    } else {
        $('#factor_item_price_diff' + title).css('display', 'none');
    }
}

var ct = 1;

function date() {
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
var
    persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g],
    englishNumbers = [/0/g, /1/g, /2/g, /3/g, /4/g, /5/g, /6/g, /7/g, /8/g, /9/g],

    convertNumbers = function (str) {
        if (typeof str === 'string') {
            for (var i = 0; i < 10; i++) {
                str = str.replace(persianNumbers[i], i).replace(englishNumbers[i], i);
            }
        }
        return str;
    };

function separatePrices(item) {
    var price = item.value;
    price = convertNumbers(price);
    var temp = price.replace(/\,/g, '');
    item.value = temp.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
}

//upload image
function separateNumber(Number) {
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


//create new input file for upload multiple image
function new_link(file_attr_id) {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    div1.className = "col-md-3";
    // file_attr_id must be passed as a STRING literal (quoted) — otherwise delIt receives
    // an undefined identifier and the remove button throws.
    var delLink = '<div class="btn badge-red mt-2"><a class="text-white" href="javascript:delIt(' + ct + ' , \'' + file_attr_id + '\')"><i class="fa fa-trash" aria-hidden="true"></i></a></div>';
    div1.innerHTML = document.getElementById(file_attr_id + '_newlinktpl').innerHTML + delLink;
    document.getElementById(file_attr_id + '_newlink').appendChild(div1);
    div1.children[0].id = file_attr_id + '_src' + ct;
    div1.children[1].id = file_attr_id + ct;
    date();
}

function delIt(eleId, file_attr_id) {
    // Remove the cloned preview row by its own id (its hidden url<name>[] input goes with
    // it, so the dropped image is not submitted). Robust to how it's called.
    var ele = document.getElementById(eleId);
    if (ele && ele.parentNode) {
        ele.parentNode.removeChild(ele);
    }
}

$(function () {
    $(".submit_btn").click(async function () {
        $('#gif').show();
        return true;
    });
});

$(".delete_submit").each(function () {
    $(this).on("click", function () {
        var id = $(this).attr("id");
        submit(id);

        function submit() {
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
                    $('input[id="' + id + '"]').parent(".delete_form").submit();
                }
            });
        }
    });
});

$(".confirm_submit").each(function () {
    $(this).on("click", function () {
        var id = $(this).attr("id");
        submit(id);

        function submit() {
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
                    $('input[id="' + id + '"]').parent(".confirm_form").submit();
                }
            });
        }
    });
});

function generateDataTableUserHistories(data) {
    if ($.fn.DataTable.isDataTable('#datatable-history')) {
        $('#datatable-history').DataTable().destroy();
    }
    $('#datatable-history tbody').empty();

    $('#datatable-history').DataTable({
        paging: true,
        searching: false,
        retrieve: true,
        data: data,

        columns: [{
                "data": "callcenter"
            },
            {
                "data": "user"
            },
            {
                "data": "date"
            },
            {
                "data": "description"
            },
        ],
    });
}

$("body").on('click', '.send_user_password', function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var phone = $('.phone').val();
    $.ajax({
        url: "/dashboard/user/sendPassword",
        type: 'post',
        data: {
            "_token": token,
            "phone": phone,
        },
        success: function (response) {
            swal("رمز با موفقیت ارسال شده است.", "", "success")
        }
    });
})

function formQuestionConfirm() {
    return confirm('آیا مطمئن هستید ؟')
}
//tab content for call details
function openTab(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
