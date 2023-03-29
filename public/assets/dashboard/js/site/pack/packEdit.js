
    CKEDITOR.replace('description', {
        filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token()])}}",
        filebrowserUploadMethod: 'form'
    });


if ($('.type_selected').attr("value") == 0) {
    $(".rawMaterialBox").css({'display': 'block'});
    $(".goodBox").css({'display': 'none'});
    $(".serviceBox").css({'display': 'none'});
}
if ($('.type_selected').attr("value") == 1) {
    $(".goodBox").css({'display': 'block'});
    $(".rawMaterialBox").css({'display': 'none'});
    $(".serviceBox").css({'display': 'none'});

}
if ($('.type_selected').attr("value") == 2) {
    $(".serviceBox").css({'display': 'block'});
    $(".goodBox").css({'display': 'none'});
    $(".rawMaterialBox").css({'display': 'none'});
}

$(document).ready(function () {
    $(".selectMultiTag").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    })
});


if (($(".expire_date").length > 0)) {
    $('.expire_date').persianDatepicker({
        observer: true,
        format: 'YYYY-MM-DD HH:mm:ss',
        timePicker: {
            enabled: true, meridiem: {enabled: true}
        }
    });
}
