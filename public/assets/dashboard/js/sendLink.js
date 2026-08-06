var ct = 1;
newServiceLink();
newCategoryLink();
var send_link_var = $('#sendLink').html();
$("#sendLink").empty();

//add new service link to the send link page with self counter
function newServiceLink() {
    ct++;
    var div1 = $('<div>').prop({id: ct}).addClass('row');
    // btn-danger, not btn + badge-red: see the افزودن خدمت button in sendLink.blade.php —
    // a badge class leaves --bs-btn-hover-bg undefined and the button disappears on hover.
    var delLink = '<button type="button" class="btn btn-danger text-white" onclick="delServiceLink(' + ct + ')"><i class="fa fa-trash" aria-hidden="true"></i></button>';

    div1.html($('#newservicetpl').html());
    $('#newservice').append(div1);
    div1.children().eq(5).prop('id', 'delete' + ct).html(delLink);

    div1.children('.service_category').children('#service_category_id1').prop('id', 'service_category_id' + ct).attr('name', 'service_category[' + ct + ']').off('change').on('change', function () {
        selectByCategory(this.id, this.value);
    });

    div1.children('.service').children('#service_id1').prop('id', 'service_id' + ct).attr('name', 'service[' + ct + ']').off('change').on('change', function () {
        getRequiredDataByItem(this.id, this.value, 'service');
    });

    div1.children('.register_price').children('#service_register_price1').prop('id', 'service_register_price' + ct).attr('name', 'service_register_price[' + ct + ']');
    div1.children('.service_item').children('#service_item_id1').prop('id', 'service_item_id' + ct).attr('name', 'service_item[' + ct + ']');
    div1.children('.service_purchase_type').children('#service_purchase_type1').prop('id', 'service_purchase_type' + ct).attr('name', 'service_purchase_type[' + ct + ']');

    getDataRequired(ct);
}

//add new category link to the send link page with self counter
function newCategoryLink() {
    ct++;
    var div1 = $('<div>').prop({id: ct}).addClass('row');
    var delLink = '<button type="button" class="btn btn-danger text-white" onclick="delCategoryLink(' + ct + ')"><i class="fa fa-trash" aria-hidden="true"></i></button>';

    div1.html($('#newcategorytpl').html());
    $('#newcategory').append(div1);
    // children: 0 category, 1 product, 2 variant, 3 register price, 4 purchase type, 5 delete
    div1.children().eq(5).prop('id', 'delete' + ct).html(delLink);

    div1.children('.product_category').children('#product_category_id1').prop('id', 'product_category_id' + ct).attr('name', 'product_category[' + ct + ']').off('change').on('change', function () {
        selectProductsByCategory(this.id, this.value);
    });

    div1.children('.product').children('#product_id1').prop('id', 'product_id' + ct).attr('name', 'product[' + ct + ']').off('change').on('change', function () {
        selectProductAttributes(this.id, this.value);
    });

    div1.children('.product_attribute').children('#product_attribute_id1').prop('id', 'product_attribute_id' + ct).attr('name', 'product_attribute[' + ct + ']').off('change').on('change', function () {
        setRegisterPriceFromAttribute(this.id, this.value);
    });

    div1.children('.register_price').children('#category_register_price1').prop('id', 'category_register_price' + ct).attr('name', 'category_register_price[' + ct + ']');
    div1.children('.category_purchase_type').children('#category_purchase_type1').prop('id', 'category_purchase_type' + ct).attr('name', 'category_purchase_type[' + ct + ']');

    getDataRequired(ct);
}

// delete service link
function delServiceLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newservice');
    parentEle.removeChild(ele);
}

// delete category link
function delCategoryLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newcategory');
    parentEle.removeChild(ele);
}
// The row number is the trailing digits of any of that row's element ids.
function rowNumber(id) {
    return String(id).replace(/^\D+/, '');
}

function csrfAjaxSetup() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

function getRequiredDataByItem(id, value, type)
{
    getServiceItemByItem(id, value, type);
    getServiceRegisterPrice(id, value);
}

// Default registration fee of the chosen service (services.price). The operator can edit the
// input afterwards; the edit applies to this request only.
function getServiceRegisterPrice(id, value) {
    var row = rowNumber(id);
    var input = $('#service_register_price' + row);

    if (!value || value == 0 || value == 'all') {
        input.val('');
        return;
    }

    csrfAjaxSetup();
    $.ajax({
        url: '/dashboard/callcenter/serviceRegisterPrice',
        type: 'post',
        dataType: 'json',
        data: {id: value},
        success: function (data) {
            input.val(data.price);
        }
    });
}

// Reset a select to just its placeholder.
// These selects carry BOTH an inline onchange (the only handler that survives the raw-HTML
// re-injection done for the quick-action modal) and a jQuery change binding added when the
// row is cloned, so every change fires twice. Rebuilding the list inside each success
// handler — the same shape selectByCategory()/getServiceItemByItem() use — keeps the second
// response from appending a duplicate set instead of replacing the first.
function resetSelect(select, placeholder) {
    select.find('option').remove();
    select.append('<option selected disabled>' + placeholder + '</option>');
}

// کالا tab cascade: category -> product -> variant, the variant supplying the default fee.
function selectProductsByCategory(id, value) {
    var row = rowNumber(id);
    var products = $('#product_id' + row);

    resetSelect(products, 'انتخاب کالا');
    resetSelect($('#product_attribute_id' + row), 'انتخاب تنوع کالا');
    $('#category_register_price' + row).val('');

    if (!value || value == 0) {
        return;
    }

    csrfAjaxSetup();
    $.ajax({
        url: '/dashboard/callcenter/productsByCategory',
        type: 'post',
        dataType: 'json',
        data: {id: value},
        success: function (data) {
            resetSelect(products, 'انتخاب کالا');
            for (var i = 0; i < data.products.length; i++) {
                products.append(`<option value="${data.products[i].id}">${data.products[i].label}</option>`);
            }
        }
    });
}

function selectProductAttributes(id, value) {
    var row = rowNumber(id);
    var attributes = $('#product_attribute_id' + row);

    resetSelect(attributes, 'انتخاب تنوع کالا');
    $('#category_register_price' + row).val('');

    if (!value || value == 0) {
        return;
    }

    csrfAjaxSetup();
    $.ajax({
        url: '/dashboard/callcenter/productAttributes',
        type: 'post',
        dataType: 'json',
        data: {id: value},
        success: function (data) {
            resetSelect(attributes, 'انتخاب تنوع کالا');
            for (var i = 0; i < data.product_attributes.length; i++) {
                attributes.append(`<option value="${data.product_attributes[i].id}" data-price="${data.product_attributes[i].price}">${data.product_attributes[i].label}</option>`);
            }
        }
    });
}

function setRegisterPriceFromAttribute(id, value) {
    var row = rowNumber(id);
    var price = $('#product_attribute_id' + row).find('option[value="' + value + '"]').data('price');

    $('#category_register_price' + row).val(price === undefined ? '' : price);
}
function getServiceItemByItem(id, value, type) {
    var row = rowNumber(id);
    var items = $('#service_item_id' + row);

    // Same reason as selectByCategory: a service with no items leaves the populate branch
    // unexecuted, so the reset has to happen here too.
    resetSelect(items, 'انتخاب نوع خدمت');

    if (!value || value == 0) {
        return;
    }

    csrfAjaxSetup();
    $.ajax({
        url: '/dashboard/serviceItem/setByItem',
        type: 'post',
        dataType: 'json',
        data: {
            id: value,
            type: type,
        },
        success: function success(data) {
            resetSelect(items, 'انتخاب نوع خدمت');
            if (!data || data.count != 'available') {
                return;
            }
            for (var i = 0; i < data.service_items.length; i++) {
                items.append('<option value="' + data.service_items[i]['id'] + '">' + data.service_items[i]['label'] + '</option>');
            }
        }
    });
}

function selectByCategory(id, value) {
    var row = rowNumber(id);
    var services = $('#service_id' + row);

    // Reset up front, not only in the success handler: /service/selectByCategory returns an
    // empty body for a category with no services, so the handler's populate branch never
    // runs and the PREVIOUS category's services would otherwise stay selectable.
    resetSelect(services, 'انتخاب خدمات');
    // the service choice is now void, so its dependants go with it
    resetSelect($('#service_item_id' + row), 'انتخاب نوع خدمت');
    $('#service_register_price' + row).val('');

    if (!value || value == 0) {
        return;
    }

    csrfAjaxSetup();
    $.ajax({
        url: '/dashboard/service/selectByCategory',
        type: 'post',
        dataType: 'json',
        data: {
            id: value
        },
        success: function success(data) {
            resetSelect(services, 'انتخاب خدمات');
            if (!data || data.count != 'available') {
                return;
            }
            for (var i = 0; i < data.services.length; i++) {
                services.append('<option value="' + data.services[i]['id'] + '">' + data.services[i]['title'] + '</option>');
            }
        }
    });
}

//fill service and category form
function getDataRequired(id) {
    $.ajax({
        url: '/dashboard/tasks-requires',
        type: 'get',
        dataType: 'json',
        success: function (data) {
            var categoryOptions = $(`#category_id${id}`).find('option');
            categoryOptions.remove();
            categoryOptions.append('<option value="all">انتخاب دسته بندی</option>');
            for (var category of data.data.categories) {
                var option = `<option value="${category.id}">${category.label}</option>`;
                categoryOptions.append(option);
            }

            var callcenterOptions = $('#callcenter_id').find('option');
            callcenterOptions.remove();
            callcenterOptions.append('<option value="all">انتخاب پشتیبان</option>');
            for (var callcenter of data.data.callcenters) {
                var option = `<option value="${callcenter.id}">${callcenter.name} ${callcenter.family}</option>`;
                callcenterOptions.append(option);
            }

            var centerOptions = $('#center_id').find('option');
            centerOptions.remove();
            centerOptions.append('<option value="all">انتخاب شعبه</option>');
            for (var center of data.data.centers) {
                var option = `<option value="${center.id}">${center.title}</option>`;
                centerOptions.append(option);
            }

            var representationOptions = $('#representation_id').find('option');
            representationOptions.remove();
            representationOptions.append('<option value="all">انتخاب نمایندگی</option>');
            for (var representation of data.data.representations) {
                var option = `<option value="${representation.id}">${representation.fa_name}</option>`;
                representationOptions.append(option);
            }

            var organizationOptions = $('#organization_id').find('option');
            organizationOptions.remove();
            organizationOptions.append('<option value="all">انتخاب سازمان</option>');
            for (var organization of data.data.organizations) {
                var option = `<option value="${organization.id}">${organization.fa_name}</option>`;
                organizationOptions.append(option);
            }
        },
    });
}

// Balances are read-only display fields, so they are formatted rather than kept raw.
function formatRial(amount) {
    var value = Number(amount);

    return (isNaN(value) ? 0 : value).toLocaleString('en-US') + ' ریال';
}

function searchUser(search) {
    if (search.length === 11) {

        $.ajax({
            url: "/dashboard/callcenter/filter?page=1",
            data: {
                user_phone: search
            },
            success: function (data) {
                createTable(data);
                document.getElementById('loading').style.display = 'none';
            }
        });


        $('.loading_send_link').css('display', 'block');
        $.ajax({
            type: "GET",
            url: "/dashboard/callcenter/fetchUser",
            data: {
                phone: search,
            }
        }).done(function (data) {
            $(".name").attr('value', data.data.user.name ? data.data.user.name : '');
            $(".family").attr('value', data.data.user.family ? data.data.user.family : '');
            $(".province_id").val(data.data.user.province_id);
            $(".city_id").val(data.data.user.city_id).change();
            $(".vipCheck").prop('checked', data.data.user.vip_user);
            $(".sosCheck").prop('checked', data.data.user.sos);
            $(".user_credit").val(formatRial(data.data.user.credit));
            $(".user_wallet").val(formatRial(data.data.user.wallet));
            $('.loading_send_link').css('display', 'none');
            if (!data.data.user.name) {
                $('.error_user').text('کاربری با این شماره تماس ثبت نام نشده است لطفا فیلدهای مربوط به کاربر را پر کنید.');
            } else {
                $('.error_user').text('');
                if (data.data.user.sex == 0){
                    $("#male-gender").prop("checked", true);
                }else{
                    $("#female-gender").prop("checked", true);
                }
            }
            if(data.data.user.conduct == "challenging")
                $("#challenging").prop("checked", true);
            else
                $("#challenging").prop("checked", false);

            generateDataTableUserHistories(data.data.histories);
            generateDataTableUserFactors(data.data.factors);
            fillInProgressTableBody(data.data.tasks)
            fillSendLinkTasksTableBody(data.data.tasks)
        });
    }
}

function fillSendLinkTasksTableBody(tasks) {
    $('#send-link-tasks-tbody').empty()
    $.each(tasks.data, function (index, value) {
        status_badge = getBadgeOfCallCenterStatus(value.status)
        description = '';
        if (value.service != null) {
            description = value.service;
        }
        $('#send-link-tasks-tbody').append(`
        <tr>
        <td>${description}</td>
        <td>${status_badge}</td>
        <td>${value.user}</td>
        <td>${value.date}</td>
        </tr>
        `)
    });
}

function fillInProgressTableBody(tasks) {
    $('#in-progress-tasks-tbody').empty()
    $.each(tasks.data, function (index, value) {
        status_badge = getBadgeOfCallCenterStatus(value.status)
        description = '';
        if (value.service != null) {
            description = value.service;
        }
        $('#in-progress-tasks-tbody').append(`
        <tr>
        <td>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="other_task_id" value="${value.id}">
        </div>
        </td>
        <td>${description}</td>
        <td>${status_badge}</td>
        <td>${value.user}</td>
        <td>${value.date}</td>
        </tr>
        `)
    });
    $('#in-progress-tasks-tbody').prepend(`
        <tr style="background: #dbfbfd">
        <td>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="other_task_id" value="new" checked="checked">
        </div>
        </td>
        <td> <label class="form-check-label">
                درخواست جدید
              </label></td>
        <td><span class="badge badge-danger">جدید</span></td>
        <td>خودم</td>
        <td>اکنون</td>

        </tr>
        `)

}

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
        columns: [
            {"data": "number"},
            {"data": "callcenter"},
            {"data": "user"},
            {"data": "date"},
            {"data": "description"},
        ],
    });
}

function generateDataTableUserFactors(data) {
    $('#data-table-factors tbody').empty();
    var i = 1;
    $.each(data, function (index, value) {
        let factor_status_badge = '';
        switch (value.status) {
            case 0:
                factor_status_badge = '<span class="badge badge-warning">در انتظار پرداخت</span> ';
                break;
            case 1:
                factor_status_badge = '<span class="badge badge-success">پرداخت شده</span> ';
                break;
            case 2:
                factor_status_badge = '<span class="badge badge-danger">عودت پرداخت</span>';
                break;
            case 3:
                factor_status_badge = '<span class="badge badge-danger">منقضی شده</span>';
                break;
            case 4:
                factor_status_badge = '<span class="badge badge-success">عودت واریزی</span>';
                break;
            case 5:
                factor_status_badge = '<span class="badge badge-danger">واریز شده</span>';
                break;
            default:
                factor_status_badge = ''
                break;
        }
        $('#data-table-factors tbody').append(`
        <tr>
        <td>${i++}</td>
        <td>${value.price}</td>
        <td>${value.type}</td>
        <td>${value.representation}</td>
        <td>${factor_status_badge}</td>
        <td>${value.date}</td>
        <td>${value.register_by}</td>
        <td>${(value.items || []).join('<br>')}</td>
        </tr>
        `)
    });
}

//setting for loading send link modal
$(document).on('show.bs.modal', '.modal', function () {
    const zIndex = 1040 + 10 * $('.modal:visible').length;
    $(this).css('z-index', zIndex);
    setTimeout(() => $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'));
});

$('.closeHistoryBtn').on('click', function () {
    $("#sendLinkModal").css("overflow", "auto");
});

$('#btnGroupDrop1').on('click', function () {
    $(".sendLink").empty();
    var div1 = document.createElement('div');
    div1.innerHTML = send_link_var;
    $(".sendLink")[1].append(div1);
});

//on change request type in create task
$("body").on('change', '#requestType', function () {
    if ($(this).val() == 'link') {
        $('#linkRequest').show();
    } else {
        $('#linkRequest').hide();
    }
});

$("body").on('click', '.task-status', function () {
    $(".sendLink").empty();
    var div1 = document.createElement('div');
    div1.innerHTML = send_link_var;
    $("#sendLink").append(div1);
    $("#close-task-description").val('');
    $("#status-tab").click();
    $("#historyBtn, #requestType").hide();
})

$("body").on('click', '.fast-service', function () {
    $(".register_price, .service_purchase_type, .factor-payment-type, .category_purchase_type").hide();
    $("#register_type").val('send_link_site');
})

// "ارسال لینک بررسی و پرداخت کالا" opens the same fast form on the کالا tab.
// The tab classes are toggled inside the modal rather than through Bootstrap's id-based
// .tab('show'), because the sendLink partial is included twice on this page and the ids
// are therefore not unique document-wide.
$("body").on('click', '.fast-product', function () {
    var scope = $('#sendLinkModal');

    scope.find('#services, #servicesContent').removeClass('active show');
    scope.find('#category, #categoryContent').addClass('active show');
})

$(document).on('change', '#send-factor', function () {
    if (this.checked) {
        $(".register_price, .service_purchase_type, .factor-payment-type, .category_purchase_type").show();
    } else {
        $(".register_price, .service_purchase_type, .factor-payment-type, .category_purchase_type").hide();
    }
});


//select province in sendLink form
$('body').on('change', '#province', function () {
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
            $('#cities')
                .empty()
                .append('<option value="all">انتخاب شهر</option>');
            $.each(data.cities, function (i, d) {
                $('#cities').append(`<option value="${d.id}">${d.name}</option>`);
            });
        }
    });
});

// A row the operator never touched still submits its placeholder select, so "was something
// actually chosen here?" is an id test, not a truthiness test. Mirrors the is_numeric()
// guards registerForm() applies to the same arrays server-side.
function isChosen(value) {
    return value !== undefined && value !== null && /^\d+$/.test(String(value).trim());
}

$('body').on('click', '#send_link_submit', function () {
    $('#validation_error').empty();
    var formData = $('#sendLinkForm').serializeArray();
    var valuesOf = function (name) {
        var values = {};
        var baseName = name.replace(/\[\]$/, '');
        var legacyIndex = 0;
        $.each(formData, function (i, field) {
            var match = field.name.match(new RegExp('^' + baseName + '\\[(\\d+)\\]$'));
            if (field.name == name) {
                values[legacyIndex++] = field.value;
            } else if (match) {
                values[match[1]] = field.value;
            }
        });
        return values;
    };

    if ($("#send-factor").prop("checked") == true) {
        var services = valuesOf('service[]');
        var service_purchase_type = valuesOf('service_purchase_type[]');
        var product_categories = valuesOf('product_category[]');
        var products = valuesOf('product[]');
        var product_attributes = valuesOf('product_attribute[]');
        var category_purchase_type = valuesOf('category_purchase_type[]');
        var incomplete_product_row = false;
        var missing_service_purchase_type = false;
        var missing_category_purchase_type = false;

        // Only a row that actually names a service/category needs a delivery method —
        // otherwise working purely on one tab trips the other tab's validation.
        $.each(services, function (i, service) {
            if (isChosen(service) && !isChosen(service_purchase_type[i])) {
                missing_service_purchase_type = true;
            }
        });
        $.each(product_categories, function (i, product_category) {
            if (isChosen(product_category) && (!isChosen(products[i]) || !isChosen(product_attributes[i]))) {
                incomplete_product_row = true;
            }
            if (isChosen(product_category) && !isChosen(category_purchase_type[i])) {
                missing_category_purchase_type = true;
            }
        });

        if (incomplete_product_row) {
            $('#validation_error').append("<p class='alert alert-danger'>انتخاب کالا و تنوع کالا برای هر ردیف الزامی است.</p>");
        }
        if (missing_service_purchase_type) {
            $('#validation_error').append("<p class='alert alert-danger'>انتخاب نحوه دریافت خدمت الزامی است.</p>");
        }
        if (missing_category_purchase_type) {
            $('#validation_error').append("<p class='alert alert-danger'>انتخاب نحوه دریافت کالا الزامی است.</p>");
        }

        if ($('#validation_error').is(':empty')) {
            $("#sendLinkForm").submit();
        }
    } else {
        $('#sendLinkModal').modal('hide');
        $('.page-loader').fadeIn(200)
        $("#sendLinkForm").submit();
    }
});
