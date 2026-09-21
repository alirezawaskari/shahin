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
    // The wrapper divs carry Bootstrap's `.d-none { display:none !important }`,
    // which beats an inline `style.display = 'block'`. Toggling the class is the
    // only way to actually reveal them - otherwise the input stays hidden while we
    // flip it to required, and the browser aborts submit with
    // "An invalid form control ... is not focusable".
    var pShow = function (id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('d-none'); el.style.display = 'block'; }
    };
    var pHide = function (id) {
        var el = document.getElementById(id);
        if (el) { el.classList.add('d-none'); el.style.display = 'none'; }
    };
    var pReq = function (id, value) {
        var el = document.getElementById(id);
        if (!el) return;
        // A field can opt out of being forced required (e.g. an edit page where a
        // receipt already exists and the server accepts no new upload).
        if (value && el.dataset && el.dataset.keepOptional) { el.required = false; return; }
        el.required = value;
    };

    payment_type = document.getElementById('payment_type' + title).value;
    if (payment_type == "card_reader" || payment_type == "card") {
        pShow('number_card' + title);
        pShow('payment_code' + title);
        pReq('number_card_input' + title, true);
        pReq('payment_code_input' + title, true);
    } else {
        pHide('number_card' + title);
        pHide('payment_code' + title);
        pReq('number_card_input' + title, false);
        pReq('payment_code_input' + title, false);
    }
    if (payment_type == "cash" || payment_type == "card_reader" || payment_type == "card" || payment_type == "check") {
        pShow('bill_image' + title);
        pReq('bill_image_input' + title, false);
    } else {
        pHide('bill_image' + title);
        pReq('bill_image_input' + title, false);
    }
    if (payment_type == "credit" || payment_type == "wallet") {
        pShow('password' + title);
        pShow('send_password' + title);
        pReq('password_input' + title, true);
    } else if (document.getElementById('password' + title)) {
        pHide('password' + title);
        pHide('send_password' + title);
        pReq('password_input' + title, false);
    }
    if (payment_type == "account") {
        pShow('sheba_number' + title);
        pReq('sheba_number_input' + title, true);
    } else if (document.getElementById('sheba_number' + title)) {
        pHide('sheba_number' + title);
        pReq('sheba_number_input' + title, false);
    }
    if (payment_type == "supervisor") {
        pShow('mobile_supervisor' + title);
        pReq('mobile_supervisor_input' + title, true);
    } else if (document.getElementById('mobile_supervisor' + title)) {
        pHide('mobile_supervisor' + title);
        pReq('mobile_supervisor_input' + title, false);
    }
    if (payment_type == "deduction_price_difference") {
        pShow('factor_item_price_diff' + title);
    } else {
        pHide('factor_item_price_diff' + title);
    }
}

var ct = 1;

// Kept for the callers that add rows to a form. jalaliDatePicker.js already watches the
// DOM for new date fields, so this only has to hand it the ones that are there now.
function date() {
    if (window.initJalaliDatePickers) window.initJalaliDatePickers(document);
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
    // .div_remove_btn -> the shared .remove_media_btn handler (adminJs.js) finds this row.
    div1.className = "col-md-3 div_remove_btn text-center";
    // Same remove control as every other model (btn btn-sm remove_media_btn + fa-close),
    // instead of the old badge-red/fa-trash link that only dropped the row from the DOM
    // and left the uploaded temp file orphaned on the server.
    //
    // No data-id here: the row is built before the upload resolves. The temp media id is
    // written into this row's hidden .path input straight after, and the handler reads it
    // from there — so the file is genuinely deleted server-side.
    // data-temp="1" -> an unsaved upload drops instantly, with no confirm prompt.
    var delLink = '<div class="mt-1"><a href="javascript:void(0)" class="btn btn-sm remove_media_btn"' +
        ' data-temp="1" title="حذف فایل"><i class="fa fa-close" aria-hidden="true"></i></a></div>';
    div1.innerHTML = document.getElementById(file_attr_id + '_newlinktpl').innerHTML + delLink;
    document.getElementById(file_attr_id + '_newlink').appendChild(div1);
    div1.children[0].id = file_attr_id + '_src' + ct;
    div1.children[1].id = file_attr_id + ct;
    date();
}

$(function () {
    $(".submit_btn").click(async function () {
        // Only show the loading overlay if the button's form passes HTML5 validation.
        // Otherwise the browser blocks the submit (e.g. "Please fill out this field" on a
        // required field) while #gif stays up forever, looking frozen.
        var form = this.form || $(this).closest('form')[0];
        if (form && typeof form.checkValidity === 'function' && !form.checkValidity()) {
            return true; // let the browser show its validation bubble; no overlay
        }
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
// Get the element with id="defaultOpen" and click on it (absent on pages without the
// call-details tab strip, e.g. check/promissory management — guard so it doesn't throw
// and halt the rest of this file)
var _defaultOpenTab = document.getElementById("defaultOpen");
if (_defaultOpenTab) _defaultOpenTab.click();
