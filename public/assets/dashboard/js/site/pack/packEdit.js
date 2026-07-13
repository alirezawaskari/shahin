// This is a static .js file, so Blade {{route()}} never runs here — building the URL
// from that literal produced a 404 ("{{route('ckeditor.upload'...")). Build the upload
// URL from the csrf-token meta at runtime instead, and guard CKEDITOR so a failed
// ckeditor.js load can't halt the rest of this script.
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('description', {
        filebrowserUploadUrl: '/dashboard/ckeditor/upload?_token=' + $('meta[name="csrf-token"]').attr('content'),
        filebrowserUploadMethod: 'form'
    });
}


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
