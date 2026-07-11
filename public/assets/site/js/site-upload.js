/**
 * Shared ajax upload behaviour for guest-facing site forms.
 *
 * Site pages do not load the dashboard bundle (adminJs.js), so this file
 * mirrors the upload helpers the .file_upload_ajax and multipleImages
 * widgets rely on. Uploads go to the (now public, throttled + validated)
 * /dashboard/uploadPhotosTemporarily endpoint and the returned MediaLibrary
 * id is written into the hidden *_path field / multiple_image[i][file] rows.
 */

// Attach the CSRF token to every ajax request on the page.
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
    }
});

var ct = 1;
var index_add_photo = 0;
var index_add_img = 0;

// Clone template used by the [] (multi-link) variant of .file_upload_ajax.
function new_link(file_attr_id) {
    ct++;
    var div1 = document.createElement('div');
    div1.id = ct;
    div1.className = "col-md-3";
    var delLink = '<div class="btn badge-red mt-2"><a class="text-white" href="javascript:delIt(' + ct + ' , \'' + file_attr_id + '\')"><i class="fa fa-trash" aria-hidden="true"></i></a></div>';
    div1.innerHTML = document.getElementById(file_attr_id + '_newlinktpl').innerHTML + delLink;
    document.getElementById(file_attr_id + '_newlink').appendChild(div1);
    div1.children[0].id = file_attr_id + '_src' + ct;
    div1.children[1].id = file_attr_id + ct;
}

function delIt(eleId, file_attr_id) {
    var ele = document.getElementById(eleId);
    if (ele && ele.parentNode) {
        ele.parentNode.removeChild(ele);
    }
}

// Single-file ajax upload.
$(document).on('change', '.file_upload_ajax', function () {
    var file = $($(this)).prop('files')[0];
    if (!file) return;

    var file_attr_name = $(this).attr('name') || '';
    var file_attr_id = $(this).attr('id');
    var fileType = file["type"];
    var validImageTypes = [
        "image/jpg", "image/jpeg", "image/png", "image/webp", "image/svg+xml",
        "image/psd", "image/tiff", "image/gif", "application/pdf",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-excel", "application/x-rar-compressed",
        "application/x-zip-compressed"
    ];
    var size = file.size;

    if ($.inArray(fileType, validImageTypes) === -1) {
        toastr.error('آپلود ناموفق : فرمت فایل مجاز نیست.');
        return;
    }
    if (size > 6000000) {
        toastr.error('آپلود ناموفق : حجم فایل از ۶ مگابایت بیشتر می باشد.');
        return;
    }

    var fd = new FormData();
    fd.append('file', file);
    var reader = new FileReader();
    var loading = '<div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading' + file_attr_id + '">' +
        '<div role="status" class="spinner-border text-primary"></div><br><span>در حال بارگذاری...</span></div>';
    $('#' + file_attr_id + '_src.show').append(loading);
    $('#loading' + file_attr_id).css('display', 'block');

    reader.onload = function (e) {
        $.ajax({
            type: "post",
            url: '/dashboard/uploadPhotosTemporarily',
            data: fd,
            contentType: false,
            processData: false,
            success: function (result) {
                if (result['status'] === true) {
                    $('#' + file_attr_id + '_src.show div').remove();
                    $('#' + file_attr_id + '_src.show img').remove();
                    var previewFile = '';
                    if (!((fileType == 'application/x-rar-compressed') || (fileType == 'application/x-zip-compressed') || (fileType == 'application/pdf') || (fileType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') || (fileType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))) {
                        previewFile = '<img src="' + e.target.result + '" id="' + index_add_photo + '" class="card-img-top" height="150px">';
                    } else {
                        previewFile = '<a href="' + result['image_name']['id'] + '" id="' + index_add_photo + '" class="disInline image_create btn border border-success mr-3">مشاهده</a>';
                    }
                    if (file_attr_name.includes("[]") && !file_attr_name.includes("consent_image")) {
                        new_link(file_attr_id);
                        $('#' + file_attr_id + '_src' + ct + '.show').append(previewFile);
                        $('#' + file_attr_id + ct + '.path').val(result['image_name']['id']);
                    } else {
                        $('#' + file_attr_id + '_src.show').append(previewFile);
                        $('#' + file_attr_id + '_path').val(result['image_name']['id']);
                    }
                } else if (result['status'] === false) {
                    toastr.error(result['message'] || 'آپلود ناموفق');
                }
                $('#loading' + file_attr_id).css('display', 'none');
            },
            error: function () {
                $('#loading' + file_attr_id).css('display', 'none');
                toastr.error('آپلود ناموفق، دوباره تلاش کنید.');
            }
        });
    };
    reader.readAsDataURL(file);
});

// Multiple-image ajax upload widget (mirrors the dashboard multipleImages()).
function multipleImages(input) {
    var multipleImagesHasLink = "0";
    if ($('.multipleImagesHasLink').val()) multipleImagesHasLink = $('.multipleImagesHasLink').val();
    if (!input.files || !input.files.length) return;

    var validImageTypes = ["image/jpg", "image/jpeg", "image/png", "image/webp"];

    Array.prototype.forEach.call(input.files, function (file) {
        var fileType = file["type"];
        if ($.inArray(fileType, validImageTypes) === -1) {
            toastr.error('آپلود ناموفق : فرمت تصویر بایستی JPG/PNG/WEBP باشد.');
            return;
        }
        if (file.size > 6000000) {
            toastr.error('آپلود ناموفق : حجم عکس از ۶ مگابایت بیشتر می باشد.');
            return;
        }

        (function (currentIndex, currentFile) {
            var fd = new FormData();
            fd.append('file', currentFile);
            var reader = new FileReader();
            reader.onload = function (e) {
                $.ajax({
                    type: "post",
                    url: '/dashboard/uploadPhotosTemporarily',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function (result) {
                        if (result['status'] === true) {
                            var tr_images = $('<tr id="' + currentIndex + '" class="row_image">');
                            var image = '<td><img src="' + e.target.result + '" id="' + currentIndex + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + currentIndex + '" class="image_name_good" name="multiple_image[' + currentIndex + '][file]" value="' + result['image_name']['id'] + '"></td>';
                            var remove = '<td><a id="' + currentIndex + '" class="btn btn-sm btn-danger removeImageCreated"><i class="fa fa-trash"></i></a></td>';
                            var title = '<td><input type="text" class="form-control" placeholder="عنوان" name="multiple_image[' + currentIndex + '][title]"></td>';
                            if (multipleImagesHasLink === "1") {
                                var link = '<td><input type="text" class="form-control" placeholder="لینک" name="multiple_image[' + currentIndex + '][link]" required></td>';
                                tr_images.append(image).append(title).append(link).append(remove);
                            } else {
                                tr_images.append(image).append(title).append(remove);
                            }
                            $('.imagesList').append(tr_images);
                        } else if (result['status'] === false) {
                            toastr.error(result['message'] || 'آپلود ناموفق');
                        }
                    }
                });
            };
            reader.readAsDataURL(currentFile);
        })(index_add_img, file);

        ++index_add_img;
    });

    input.value = '';
}

// Remove a row added by multipleImages (temp media is throwaway; remove client-side).
$(document).on('click', '.removeImageCreated', function () {
    $(this).closest('tr.row_image').remove();
});
