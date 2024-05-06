$(".checkbox_type").change(function() {
    if(this.checked) {
        $('input[name='+this.name+'_percent]').css('display', 'block');
    }
    else
    {
        $('input[name='+this.name+'_percent]').css('display', 'none');
    }
});
function selectAll(select_id) {
    $("#"+select_id+"> option").prop("selected", true);
    $("#"+select_id).trigger("change");
}
function deselectAll(select_id) {
    $("#"+select_id+"> option").prop("selected", false);
    $("#"+select_id).trigger("change");
}
function selectByCategory() {
    value = $('#service_category_id').val();
    if (value == []) {
        $('#service_id').find('option').remove();
    }

    if (value != []) {
        $.ajax({
            url: '/dashboard/service/selectByCategory',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#service_id').find('option').remove();
                    $('#service_id').append('<option disabled>انتخاب خدمات</option>');
                    for (var i = 0; i < data.services.length; i++) {
                        $('#service_id').append('<option value="' + data.services[i]['id'] + '">' + data.services[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}

function selectByService() {
    value = $('#service_id').val();
    if (value == []) {
        $('#center_id').find('option').remove();
    }

    if (value != []) {
        $.ajax({
            url: '/dashboard/center/selectByService',
            type: 'post',
            dataType: 'json',
            data: {
                id: value
            },
            success: function success(data) {
                if (data.count == 'available') {
                    $('#center_id').find('option').remove();
                    $('#center_id').append('<option disabled>انتخاب مرکز</option>');
                    for (var i = 0; i < data.centers.length; i++) {
                        $('#center_id').append('<option value="' + data.centers[i]['id'] + '">' + data.centers[i]['title'] + '</option>');
                    }
                }
            }
        });
    }
}
