$(document).ready(function () {

    // $('#select_status').select2({
    //     placeholder: "انتخاب وضعیت"
    // });
    $('#task_type').select2({
        placeholder: "انتخاب نوع تسک "
    });
    $('#product_category').select2({
        placeholder: "دسته بندی کالا "
    });
    $('#province_id').select2();

    $('#service_items').select2({
        placeholder: "انتخاب نوع خدمت"
    });
    $('#factor_type').select2({
        placeholder: "انتخاب نوع دریافت وجه "
    });

    /*    click on select service */
    $("#service_category").change(function () {
        $('#service_items').empty();
        var token = $('meta[name="csrf-token"]').attr('content');
        var categoryId = $(this).val();
        $.ajax(
            {
                url: "/dashboard/site/category/fetch-services-by-category",
                type: 'post',
                data: {
                    "_token": token,
                    "id": categoryId
                },
                success: function (response) {

                    $('#sub_service_category')
                        .empty().append('<option value="all">انتخاب خدمت درخواستی</option>')
                    ;
                    $.each(response.data.data, function (i, d) {
                        $('#sub_service_category').append('<option value="' + d.id + '">' + d.title + '</option>');
                    });
                }
            });
    });
    /*    click on select service */
    $("#sub_service_category").change(function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var serviceId = $(this).val();
        $.ajax(
            {
                url: "/dashboard/service/fetch-serviceitem-by-service",
                type: 'post',
                data: {
                    "_token": token,
                    "id": serviceId
                },
                success: function (response) {
                    $('#service_items')
                        .empty();
                    $.each(response.data.data, function (i, d) {
                        $('#service_items').append('<option value="' + d.id + '">' + d.title + '</option>');
                    });
                }
            });
    });
});
