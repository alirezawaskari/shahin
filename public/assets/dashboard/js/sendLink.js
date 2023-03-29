var ct = 1;
newServiceLink();
newCategoryLink();
var send_link_var = $('#sendLink').html();
$("#sendLink").empty();

function newServiceLink() {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    var delLink = '<a class="text-white" href="javascript:delServiceLink(' + ct + ')"><div class="btn badge-red"><i class="fa fa-trash" aria-hidden="true"></i></div></a>';

    div1.innerHTML = document.getElementById('newservicetpl').innerHTML;
    div1.classList.add("row");
    document.getElementById('newservice').appendChild(div1);
    div1.children[4].id = "delete" + ct;
    document.getElementById('delete' + ct).innerHTML = delLink;

    $("div#" + ct).children("div.service_category").children("select#service_category_id1")[0].id = "service_category_id" + ct;
    document.getElementById('service_category_id' + ct).onchange = undefined;
    $('#service_category_id' + ct).change(function () {
        selectByCategory(this.id, this.value);
    });

    $("div#" + ct).children("div.service").children("select#service_id1")[0].id = "service_id" + ct;
    document.getElementById('service_id' + ct).onchange = undefined;
    $('#service_id' + ct).change(function () {
        getFinancialPlansTypeByItem(this.id, this.value , 'service');
    });

    $("div#" + ct).children("div.financial_plans_type").children("select#financial_plans_type_id1")[0].id = "financial_plans_type_id" + ct;

    $("div#" + ct).children("div.service_purchase_type").children("select#service_purchase_type1")[0].id = "service_purchase_type" + ct;

    getDataRequired(ct);
}

function newCategoryLink() {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    var delLink = '<a class="text-white" href="javascript:delCategoryLink(' + ct + ')"><div class="btn badge-red"><i class="fa fa-trash" aria-hidden="true"></i></div></a>';

    div1.innerHTML = document.getElementById('newcategorytpl').innerHTML;
    div1.classList.add("row");
    document.getElementById('newcategory').appendChild(div1);
    div1.children[3].id = "delete" + ct;
    document.getElementById('delete' + ct).innerHTML = delLink;

    $("div#" + ct).children("div.product_category").children("select#product_category_id1")[0].id = "product_category_id" + ct;

    $("div#" + ct).children("div.financial_plans_type").children("select#financial_plans_type_id1")[0].id = "financial_plans_type_id" + ct;
    document.getElementById('financial_plans_type_id' + ct).onchange = undefined;
    $('#financial_plans_type_id' + ct).change(function () {
        getFinancialPlansTypeByItem(this.id, this.value , 'category');
    });

    $("div#" + ct).children("div.category_purchase_type").children("select#category_purchase_type1")[0].id = "category_purchase_type" + ct;

    getDataRequired(ct);
}

function delServiceLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newservice');
    parentEle.removeChild(ele);
}

function delCategoryLink(eleId) {
    d = document;
    var ele = d.getElementById(eleId);
    var parentEle = d.getElementById('newcategory');
    parentEle.removeChild(ele);
}

function getFinancialPlansTypeByItem(id, value , type) {
    if(type == 'category')
    {
        id = id.slice(19);
    }
    else if(type == 'service')
    {
        id = id.slice(10);
    }

    if (value == 0) {
        $('#financial_plans_type_id' + id).find('option').remove();
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    if (value != 0) {
        $.ajax({
            url: '/dashboard/financialPlansType/setByItem',
            type: 'post',
            dataType: 'json',
            data: {
                id: value,
                type: type,
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#financial_plans_type_id' + id).find('option').remove();
                    for (var i = 0; i < data.financial_plans_types.length; i++) {
                        $('#financial_plans_type_id' + id).append('<option value="' + data.financial_plans_types[i]['id'] + '">' + data.financial_plans_types[i]['label'] + '</option>');
                    }
                }
            }
        });
    }
}

function selectByCategory(id, value) {
    id = id.slice(19);
    if (value == 0) {
        $('#service_id' + id).find('option').remove();
    }

    if (value != 0) {
        $.ajax({
            url: '/dashboard/service/selectByCategory',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#service_id' + id).find('option').remove();
                    $('#service_id' + id).append('<option selected disabled>انتخاب خدمات</option>');
                    for (var i = 0; i < data.services.length; i++) {
                        $('#service_id' + id).append('<option value="' + data.services[i]['id'] + '">' + data.services[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}

function getDataRequired(id) {
    $.ajax({
        url: '/dashboard/tasks-requires',
        type: 'get',
        dataType: 'json',
        success: function success(data) {
            $('#service_id' + id).find('option').remove();
            $('#service_id' + id).append('<option selected disabled>انتخاب خدمات</option>');
            for (var i = 0; i < data.data.services.length; i++) {
                $('#service_id' + id).append('<option value="' + data.data.services[i]['id'] + '">' + data.data.services[i]['label'] + '</option>');
            }
            $('#category_id' + id).find('option').remove();
            $('#category_id' + id).append('<option value="all">انتخاب دسته بندی</option>');
            for (var i = 0; i < data.data.categories.length; i++) {
                $('#category_id' + id).append('<option value="' + data.data.categories[i]['id'] + '">' + data.data.categories[i]['label'] + '</option>');
            }
            $('#callcenter_id').find('option').remove();
            $('#callcenter_id').append('<option value="all">انتخاب پشتیبان</option>');
            for (var i = 0; i < data.data.callcenters.length; i++) {
                $('#callcenter_id').append('<option value="' + data.data.callcenters[i]['id'] + '">' + data.data.callcenters[i]['name'] + ' ' + data.data.callcenters[i]['family'] + '</option>');
            }
            $('#center_id').find('option').remove();
            $('#center_id').append('<option value="all">انتخاب مرکز</option>');
            for (var i = 0; i < data.data.centers.length; i++) {
                $('#center_id').append('<option value="' + data.data.centers[i]['id'] + '">' + data.data.centers[i]['title'] + '</option>');
            }
            $('#representation_id').find('option').remove();
            $('#representation_id').append('<option value="all">انتخاب نمایندگی</option>');
            for (var i = 0; i < data.data.representations.length; i++) {
                $('#representation_id').append('<option value="' + data.data.representations[i]['id'] + '">' + data.data.representations[i]['fa_name'] + '</option>');
            }
            $('#organization_id').find('option').remove();
            $('#organization_id').append('<option value="all">انتخاب سازمان</option>');
            for (var i = 0; i < data.data.organizations.length; i++) {
                $('#organization_id').append('<option value="' + data.data.organizations[i]['id'] + '">' + data.data.organizations[i]['fa_name'] + '</option>');
            }
        }
    });
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
            $('.loading_send_link').css('display', 'none');
            if (!data.data.user.name) {
                $('.error_user').text('کاربری با این شماره تماس ثبت نام نشده است لطفا فیلدهای مربوط به کاربر را پر کنید.');
            } else {
                $('.error_user').text('');
            }
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
        description='';
        if (value.service!=null){
            description=value.service;
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
        description='';
        if (value.service!=null){
            description=value.service;
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
    var i=1;
    $.each(data, function (index, value) {
      let factor_status_badge='';
         switch (value.status) {
            case 0:
                factor_status_badge= '<span class="badge badge-warning">در انتظار پرداخت</span> ';
                break;
            case 1:
                factor_status_badge= '<span class="badge badge-success">پرداخت شده</span> ';
                break;
            case 2:
                factor_status_badge= '<span class="badge badge-danger">عودت پرداخت</span>';
                break;
            case 3:
                factor_status_badge= '<span class="badge badge-danger">منقضی شده</span>';
                break;
            case 4:
                factor_status_badge= '<span class="badge badge-success">عودت واریزی</span>';
                break;
            case 5:
                factor_status_badge= '<span class="badge badge-danger">واریز شده</span>';
                break;
            default:
                factor_status_badge= ''
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
        <td>${value.items.toString()}</td>
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
        $('#linkRequest').css('display', 'block');
        $('#otherRequest').css('display', 'none');
    } else {
        $('#otherRequest').css('display', 'block');
        $('#linkRequest').css('display', 'none');
    }
});

$("body").on('click', '.task-status', function () {
    $(".sendLink").empty();
    var div1 = document.createElement('div');
    div1.innerHTML = send_link_var;
    $("#sendLink").append(div1);
    $("#historyBtn").css('display', 'none');
    $("#requestType").css('display', 'none');
})

$("body").on('click', '.fast-service', function () {
    $(".financial_plans_type").hide()
    $(".service_purchase_type").hide()
    $(".factor-payment-type").hide()
    $(".category_purchase_type").hide()
    $("#register_type").val('send_link_site');
})

$(document).on('change', '#send-factor', function () {
    if (this.checked) {
        $(".financial_plans_type").show()
        $(".service_purchase_type").show()
        $(".factor-payment-type").show()
        $(".category_purchase_type").show()
    } else {
        $(".financial_plans_type").hide()
        $(".service_purchase_type").hide()
        $(".factor-payment-type").hide()
        $(".category_purchase_type").hide()
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
                $('#cities').append('<option value="' + d.id + '">' + d.name + '</option>');
            });
        }
    });
});

$('body').on('click', '#send_link_submit', function () {
    $('#validation_error').empty();
    var id = $(this).attr("id");
    var formData = $('#sendLinkForm').serializeArray();
    var services = [];
    var service_purchase_type = [];
    var product_categories = [];
    var category_purchase_type = [];
    var services_count = 0;
    var service_purchase_type_count = 0;
    var product_categories_count = 0;
    var category_purchase_type_count = 0;


    if ($("#send-factor").prop("checked") == true) {
        $.each(formData, function (i, field) {
            if (field.name == 'service[]') {
                services[services_count] = field.value;
                services_count++;
            }
            if (field.name == 'service_purchase_type[]') {
                service_purchase_type[service_purchase_type_count] = field.value;
                service_purchase_type_count++;
            }
            if (field.name == 'product_category[]') {
                product_categories[product_categories_count] = field.value;
                product_categories_count++;
            }
            if (field.name == 'category_purchase_type[]') {
                category_purchase_type[category_purchase_type_count] = field.value;
                category_purchase_type_count++;
            }
        });
        $.each(services, function (i, service) {
            if (!service_purchase_type[i]) {
                $('#validation_error').append("<p class='alert alert-danger'>انتخاب نحوه دریافت خدمت الزامی است.</p>");
            }
        });
        $.each(product_categories, function (i, product_category) {
            if (!category_purchase_type[i]) {
                $('#validation_error').append("<p class='alert alert-danger'>انتخاب نحوه دریافت کالا الزامی است.</p>");
            }
        });
        if ($('#validation_error').is(':empty')) {
            $("#sendLinkForm").submit();
        }
    } else {
        $('#sendLinkModal').modal('hide');
        $('.page-loader').fadeIn(200)
        $("#sendLinkForm").submit();
    }
});
