/**
 * Shared ajax upload behaviour for guest-facing site forms.
 *
 * Site pages do not load the dashboard bundle (adminJs.js), so this file
 * mirrors the upload helpers the .file_upload_ajax and multipleImages
 * widgets rely on. Uploads go to the (now public, throttled + validated)
 * /dashboard/uploadFilesTemporarily endpoint and the returned MediaLibrary
 * id is written into the hidden *_path field / multiple_image[i][file] rows.
 */

// Attach the CSRF token to every ajax request on the page.
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
    }
});

/**
 * Universal upload indicator (mirrors the dashboard bundle — site pages don't load adminJs.js).
 *
 * Hooks jQuery's GLOBAL ajax events rather than each call site, so every file upload shows
 * feedback with no extra work from the caller. Scoped to the upload endpoints so ordinary
 * ajax never triggers it, and it counts overlapping uploads so the bar only clears when the
 * last one finishes.
 */
(function () {
    var pending = 0;

    function isUploadRequest(settings) {
        var url = (settings && settings.url) || '';
        return /uploadFilesTemporarily|ckeditor\/upload/i.test(url);
    }

    function ensureUi() {
        if (document.getElementById('shahin_upload_loading')) return;

        var style = document.createElement('style');
        style.textContent =
            '#shahin_upload_loading{position:fixed;top:0;left:0;right:0;z-index:99999;display:none;' +
            'align-items:center;justify-content:center;gap:8px;padding:10px 14px;background:#28a745;' +
            'color:#fff;font-size:14px;line-height:1;box-shadow:0 2px 8px rgba(0,0,0,.2)}' +
            '#shahin_upload_loading .sul-spin{width:16px;height:16px;border:2px solid rgba(255,255,255,.45);' +
            'border-top-color:#fff;border-radius:50%;display:inline-block;animation:sulspin .7s linear infinite}' +
            '@keyframes sulspin{to{transform:rotate(360deg)}}';
        document.head.appendChild(style);

        var bar = document.createElement('div');
        bar.id = 'shahin_upload_loading';
        bar.setAttribute('role', 'status');
        bar.setAttribute('aria-live', 'polite');
        bar.innerHTML = '<span class="sul-spin"></span><span class="sul-txt">در حال بارگذاری فایل...</span>';
        document.body.appendChild(bar);
    }

    function render() {
        ensureUi();
        var bar = document.getElementById('shahin_upload_loading');
        if (!bar) return;
        if (pending > 0) {
            bar.querySelector('.sul-txt').textContent = pending > 1
                ? 'در حال بارگذاری ' + pending + ' فایل...'
                : 'در حال بارگذاری فایل...';
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    $(document).ajaxSend(function (e, xhr, settings) {
        if (isUploadRequest(settings)) { pending++; render(); }
    });
    // fires on success AND error, so the bar can never get stuck on a failed upload
    $(document).ajaxComplete(function (e, xhr, settings) {
        if (isUploadRequest(settings)) { pending = Math.max(0, pending - 1); render(); }
    });
})();

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
// Namespaced + off-before-on so that pages which load BOTH this file and the dashboard
// adminJs.js (e.g. the site factorItem page) bind the handler only ONCE — otherwise every
// selected file uploads and previews twice.
$(document).off('change.fileUploadAjax', '.file_upload_ajax').on('change.fileUploadAjax', '.file_upload_ajax', function () {
    var file = $($(this)).prop('files')[0];
    if (!file) return;

    var file_attr_name = $(this).attr('name') || '';
    var file_attr_id = $(this).attr('id');

    // Re-selecting a different file for a single field: drop the previously uploaded
    // temp on the server so it isn't orphaned on disk, and clear its hidden id.
    var prevTemp = $('#' + file_attr_id + '_path').val();
    if (prevTemp) {
        $.post('/upload/removeTemp', { media_id: prevTemp });
        $('#' + file_attr_id + '_path').val('');
    }

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
            url: '/dashboard/uploadFilesTemporarily',
            data: fd,
            contentType: false,
            processData: false,
            success: function (result) {
                if (result['status'] === true) {
                    $('#loading' + file_attr_id).remove();
                    var previewFile = '';
                    if (!((fileType == 'application/x-rar-compressed') || (fileType == 'application/x-zip-compressed') || (fileType == 'application/pdf') || (fileType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') || (fileType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))) {
                        previewFile = '<img src="' + e.target.result + '" id="' + index_add_photo + '" class="rounded border" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">';
                    } else {
                        var viewHref = result['media_id'] ? ('/upload/view/' + result['media_id']) : '#';
                        previewFile = '<a href="' + viewHref + '" target="_blank" rel="noopener" id="' + index_add_photo + '" class="disInline image_create btn border border-success mr-3">مشاهده</a>';
                    }
                    if (file_attr_name.includes("[]") && !file_attr_name.includes("consent_image")) {
                        new_link(file_attr_id);
                        $('#' + file_attr_id + '_src' + ct + '.show').append(previewFile);
                        $('#' + file_attr_id + ct + '.path').val(result['media_id']);
                    } else {
                        // single-file field: replace the whole preview (image OR مشاهده link,
                        // never both) and add a remove (✕) button that drops the temp upload.
                        var removeBtn = '<div class="mt-1"><a href="javascript:void(0)" ' +
                            'class="btn btn-sm btn-danger remove_temp_media" data-id="' + result['media_id'] + '" ' +
                            'data-field="' + file_attr_id + '"><i class="fa fa-close"></i> حذف فایل</a></div>';
                        $('#' + file_attr_id + '_src.show').empty().append(previewFile).append(removeBtn);
                        $('#' + file_attr_id + '_path').val(result['media_id']);
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

// Remove a just-uploaded TEMP file on a guest site form (public, temp-scoped route).
// Deletes the temp media server-side, then clears the preview + hidden id so the
// form submits with no resume attached.
$(document).on('click', '.remove_temp_media', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var media_id = $btn.data('id');
    var field = $btn.data('field');
    var done = function () {
        if (field) {
            // single-file field: clear its preview + hidden temp id
            $('#' + field + '_src').empty();
            $('#' + field + '_path').val('');
        } else {
            // multipleImages row: drop the whole row
            $btn.closest('tr.row_image').remove();
        }
    };
    if (!media_id) { done(); return; }
    $.ajax({
        type: 'post',
        url: '/upload/removeTemp',
        data: { media_id: media_id },
        success: done,
        error: done
    });
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
                    url: '/dashboard/uploadFilesTemporarily',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function (result) {
                        if (result['status'] === true) {
                            var tr_images = $('<tr id="' + currentIndex + '" class="row_image">');
                            var image = '<td><img src="' + e.target.result + '" id="' + currentIndex + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + currentIndex + '" class="image_name_good" name="multiple_image[' + currentIndex + '][file]" value="' + result['media_id'] + '"></td>';
                            var remove = '<td><a data-id="' + result['media_id'] + '" title="حذف عکس" class="btn btn-sm btn-danger remove_temp_media"><i class="fa fa-close"></i></a></td>';
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

