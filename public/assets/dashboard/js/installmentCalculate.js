$(document).ready(function () {
    $('#service1').on('change', function() {
       if ($('#service1').val() != 'none'){
           $("#product_category1").val('none');
       }
    });
     $('#service2').on('change', function() {
           if ($('#service2').val() != 'none'){
               $("#product_category2").val('none');
           }
        });
     $('#service3').on('change', function() {
           if ($('#service3').val() != 'none'){
               $("#product_category3").val('none');
           }
        });
     //----------------------------------------------------
    $('#product_category1').on('change', function() {
       if ($('#product_category1').val() != 'none'){
           $("#service1").val('none');
       }
    });
     $('#product_category2').on('change', function() {
           if ($('#product_category2').val() != 'none'){
               $("#service2").val('none');
           }
        });
     $('#product_category3').on('change', function() {
           if ($('#product_category3').val() != 'none'){
               $("#service3").val('none');
           }
        });
     //-------------------------------------------------

});
function calculate(ct) {
    event.preventDefault();
    var form = $('#calculate' + ct)[0];
    var data = new FormData(form);
    $.ajax({
        type: "POST",
        enctype: 'multipart/form-data',
        url: '/dashboard/installment/calculate',
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 800000,
        success: function (result) {
            if (result["success"]) {
                $("#totalPrice" + ct).html(result["success"].total_price);
                $("#prepayment" + ct).html(result["success"].prepayment);
                $("#installmentsPrice" + ct).html(result["success"].installments_price);
                $("#installmentsNum" + ct).html(result["success"].installments_num);
                $("#loan" + ct).html(result["success"].loan);
                $("#guarantee" + ct).html(result["success"].guarantee);
                $("#installmentsNum").val(result["success"].installments_num);
            }
            else {
                toastr.error(result["message"]);
            }
        },
        error: function (e) {
            toastr.error('لطفا خدمت یا دسته بندی مورد نظر را انتخاب نمائید.');
        }
    });
}
function getFinancialPlansTypeByItem(id, value, type) {
    if (type == 'category') {
        id = id.slice(19);
    } else if (type == 'service') {
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
                        $(`#financial_plans_type_id${id}`).append(`<option value="${data.financial_plans_types[i].id}">${data.financial_plans_types[i].label}</option>`);

                    }
                }
            }
        });
    }
}
