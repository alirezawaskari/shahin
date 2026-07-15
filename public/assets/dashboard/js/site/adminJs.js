$body = $("body");

// Global CSRF header for every dashboard ajax request (needed by the
// .file_upload_ajax / multipleImages uploads on all dashboard pages).
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * Universal upload indicator.
 *
 * Hooks jQuery's GLOBAL ajax events rather than each call site, so every file upload —
 * the ones that already draw their own per-field spinner, the ones that never did, and
 * any added later — shows feedback with no extra work from the caller. Scoped to the
 * upload endpoints so ordinary ajax (saves, lookups, removeMedia) never triggers it, and
 * it counts overlapping uploads so the bar only clears when the last one finishes.
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

// Shared builders so saved & just-uploaded media render identically everywhere.
function spatieMediaHtml(url, mediaId, boxH) {
    boxH = boxH || 120;
    return '<div class="div_remove_btn d-inline-block mb-2" id="media_' + mediaId + '">' +
        '<img src="' + url + '" class="rounded border" style="height:' + boxH + 'px;width:100%;object-fit:contain;background:#f8f9fa;" alt="">' +
        '<a data-id="' + mediaId + '" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>' +
        '</div>';
}
function spatiePlaceholderHtml(boxH) {
    boxH = boxH || 120;
    return '<div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2" style="height:' + boxH + 'px;background:#f8f9fa;">بدون تصویر</div>';
}

// Confirmation dialog that works whether SweetAlert2 (Swal.fire), sweetalert v2 (swal),
// or neither is loaded — always guarantees a confirmation via the native fallback.
function shahinConfirm(onConfirm) {
    // SweetAlert2 (realrashid/sweet-alert), when present
    if (window.Swal && typeof window.Swal.fire === 'function') {
        try {
            window.Swal.fire({
                title: 'آیا مطمئن هستید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله',
                cancelButtonText: 'خیر',
                reverseButtons: true
            }).then(function (result) {
                if (result && result.isConfirmed) onConfirm();
            });
            return;
        } catch (e) { /* fall through */ }
    }
    // sweetalert v2 (promise-based)
    try {
        if (typeof swal === 'function') {
            var p = swal({
                title: 'آیا مطمئن هستید؟',
                text: '',
                icon: 'warning',
                buttons: { cancel: 'خیر', confirm: 'بله' },
                dangerMode: true
            });
            if (p && typeof p.then === 'function') {
                p.then(function (res) { if (res) onConfirm(); });
                return;
            }
        }
    } catch (e) { /* fall through */ }
    // guaranteed native fallback
    if (window.confirm('آیا مطمئن هستید؟')) {
        onConfirm();
    }
}

/**
 * Render a field's current image in place after an ajax save (no reload).
 * Convention per field: #{fieldId}_current (display box, carries data-box-h),
 * #{fieldId}_src (temp preview) and #{fieldId}_path (hidden temp id).
 * info = {id, url} to show an image, or null/empty to show the placeholder.
 */
function setCurrentMedia(fieldId, info) {
    var $current = $('#' + fieldId + '_current');
    if ($current.length) {
        if (info && info.url) {
            $current.html(spatieMediaHtml(info.url, info.id, $current.data('box-h')));
        } else {
            $current.html(spatiePlaceholderHtml($current.data('box-h')));
        }
    }
    // the temp selection is now persisted -> clear its preview + hidden id
    $('#' + fieldId + '_src').empty();
    $('#' + fieldId + '_path').val('');
}

// one saved row of an unlimited "list" collection (e.g. portfolio / work-samples)
function mediaListRowHtml(m, titleName, boxH) {
    boxH = boxH || 80;
    var title = (m.title || '').replace(/"/g, '&quot;');
    return '<tr class="row_image" id="media_' + m.id + '">' +
        '<td><img src="' + m.url + '" class="rounded border" style="height:' + boxH + 'px;width:auto;object-fit:contain;background:#f8f9fa;"></td>' +
        '<td><input type="text" class="form-control" placeholder="عنوان" name="' + titleName + '[' + m.id + '][title]" value="' + title + '"></td>' +
        '<td><a data-id="' + m.id + '" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a></td>' +
        '</tr>';
}

/**
 * Rebuild an unlimited list collection after an ajax save (no reload).
 * Convention: #{listKey}_list (tbody of saved rows, carries data-title-name + data-box-h),
 * #{listKey}_temp (tbody the upload widget appends to), #{listKey}_fileinput (file input),
 * #{listKey}_list_wrap (optional wrapper toggled by emptiness).
 */
function setListMedia(listKey, items) {
    var $list = $('#' + listKey + '_list');
    if ($list.length) {
        var titleName = $list.data('title-name') || listKey;
        var boxH = $list.data('box-h');
        var html = '';
        (items || []).forEach(function (m) { html += mediaListRowHtml(m, titleName, boxH); });
        $list.html(html);
        var $wrap = $('#' + listKey + '_list_wrap');
        if ($wrap.length) $wrap.toggle((items || []).length > 0);
    }
    // the just-added temp rows are now persisted -> clear them + reset the file input
    $('#' + listKey + '_temp').empty();
    $('#' + listKey + '_fileinput').val('');
}

/**
 * Reusable applier for a controller's saved-media map (Category, Service, ...).
 * Shape: { singles: { <fieldId>: {id,url}|null },
 *          slots:   { <inputPrefix>: { <index>: {id,url} } },
 *          lists:   { <listKey>: [ {id,url,title}, ... ] } }
 * Single fields -> #{fieldId}_*, slot fields -> #{inputPrefix}_{index}_*, lists -> #{listKey}_*.
 */
function applySavedMedia(media) {
    if (!media) return;
    if (media.singles) {
        $.each(media.singles, function (fieldId, info) {
            setCurrentMedia(fieldId, info);
        });
    }
    if (media.slots) {
        $.each(media.slots, function (inputPrefix, slots) {
            $.each(slots || {}, function (index, info) {
                setCurrentMedia(inputPrefix + '_' + index, info);
            });
        });
    }
    if (media.lists) {
        $.each(media.lists, function (listKey, items) {
            setListMedia(listKey, items);
        });
    }
}

// Remove an image via the ✕ button. One handler covers both cases (works with the
// existing DOM, no view changes needed) — resolved by which data-* the button carries:
//   • data-id    -> the Spatie media id to delete on the server (removeMedia)
//   • data-field -> present only on a not-yet-saved TEMP upload preview: after deleting the
//                   temp media, clear its preview + hidden temp id instead of a saved wrapper
// Delegated on the child so clicking the inner <i> works too.
$(document).on('click', '.remove_media_btn .fa-close', function (e) {
    e.preventDefault();
    var $btn = $(this).closest('.remove_media_btn');
    if (!$btn.length) return;
    var media_id = $btn.attr('data-id');
    var field = $btn.attr('data-field'); // set -> temp single-field upload preview
    var isTemp = field || $btn.attr('data-temp'); // any not-yet-saved upload -> drop without confirm
    var $row = $btn.closest('.div_remove_btn, .row_image');
    if (!$row.length) $row = $btn;
    var $current = $row.closest('[id$="_current"]');

    // Multi-file ([]) preview rows can't carry data-id: the row is built by new_link()
    // before the upload resolves, so the temp id is written into the row's hidden .path
    // input afterwards. Read it from there so these rows delete server-side too, instead
    // of only disappearing from the UI.
    if (!media_id) {
        media_id = $row.find('input.path').val();
    }

    var applyUi = function () {
        if (field) {
            // temp upload preview: clear its preview + hidden temp id
            $('#' + field + '_src').empty();
            $('#' + field + '_path').val('');
        } else {
            $row.remove();
            // restore the "بدون تصویر" placeholder in slot-based fields
            if ($current.length) {
                $current.html(spatiePlaceholderHtml($current.data('box-h')));
            }
        }
    };

    var doDelete = function () {
        if (!media_id) { applyUi(); return; } // nothing on the server -> just update the UI
        $.ajax({
            type: "post",
            url: '/dashboard/removeMedia',
            data: { media_id: media_id },
            success: function (result) {
                if (result && result.status === true) applyUi();
            }
        });
    };

    // a not-yet-saved temp upload drops instantly (still deleted server-side by id);
    // an already-saved media asks for confirmation first
    if (isTemp) {
        doDelete();
    } else {
        shahinConfirm(doDelete);
    }
});



$(document).on({
    ajaxStart: function () {
        $body.addClass("loading");
    },
    ajaxStop: function () {
        $body.removeClass("loading");
    }
});


//select2
$(document).ready(function () {
    $('.selectMulti').select2({
        dir: "rtl"
    });

    $('.js-example-basic-single').select2();
});

//upload image good - use in createGood.blade
var index_add_img = 0;

function readURL(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpg", "image/jpeg"];
        if ($.inArray(fileType, validImageTypes) > 0) {
            if (6000000 >= input.files[0].size) {
                var fd = new FormData();
                fd.append('file', input.files[0]);
                fd.append('index_add_img', index_add_img);
                var reader = new FileReader();
                reader.onload = function (e) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/uploadFilesTemporarily',
                        data: fd,
                        contentType: false,
                        processData: false,
                        headers: {
                            "Authorization": localStorage.getItem('token')
                        },
                        success: function (result) {
                            if (result['status'] === true) {
                                if (index_add_img === 0) {
                                    input_first_photo = '<input id="' + index_add_img + '" class="radio_good"  name="image_name_good[' + index_add_img + '][index]" type="radio" checked value="1"><i class="fa fa-lg fa-flag-o"></i>';
                                } else {
                                    input_first_photo = '<input id="' + index_add_img + '" class="radio_good"  name="image_name_good[' + index_add_img + '][index]" type="radio" value="0"><i class="fa fa-lg fa-flag-o"></i>';
                                }

                                var tr_images = $('<tr id="' + index_add_img + '" class="row_image">');
                                var image = '<td><img src="' + e.target.result + '" id="' + index_add_img + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + index_add_img + '" class="image_name_good" name="image_name_good[' + index_add_img + '][name]" value="' + result['media_id'] + '"></td>';
                                var remove = '<td><a id="' + index_add_img + '" class="flagProduct">' + input_first_photo + '</a><a data-id="' + result['media_id'] + '" data-temp="1" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a></td>';

                                tr_images.append(image).append(remove);
                                $('.imagesList').append(tr_images);
                                ++index_add_img;
                                $('.submitGood').css({
                                    'display': 'block'
                                });
                            }
                        }
                    });

                };

                reader.readAsDataURL(input.files[0]);
            } else {
                toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            }
        } else {
            toastr.error('آپلود ناموفق : فرمت تصویر بایستی JPG باشد.');
        }

    }
}

//sliderCategory
function multipleImages(input) {
    var multipleImagesHasLink = "0";
    if ($('.multipleImagesHasLink').val()) multipleImagesHasLink = $('.multipleImagesHasLink').val();
    if (!input.files || !input.files.length) return;

    var validImageTypes = ["image/jpg", "image/jpeg", "image/png", "image/webp"];

    // Upload every selected file (supports multi-select and repeated selections).
    Array.prototype.forEach.call(input.files, function (file) {
        var fileType = file["type"];
        if ($.inArray(fileType, validImageTypes) === -1) {
            toastr.error('آپلود ناموفق : فرمت تصویر بایستی JPG/PNG/WEBP باشد.');
            return;
        }
        if (file.size > 6000000) {
            toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            return;
        }

        // Snapshot the index/file so async callbacks don't clobber each other.
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
                    headers: {
                        "Authorization": localStorage.getItem('token')
                    },
                    success: function (result) {
                        if (result['status'] === true) {
                            var tr_images = $('<tr id="' + currentIndex + '" class="row_image">');
                            var image = '<td><img src="' + e.target.result + '" id="' + currentIndex + '" class="disInline image_create border-radius5" width="80px"><input type="hidden" id="' + currentIndex + '" class="image_name_good" name="multiple_image[' + currentIndex + '][file]" value="' + result['media_id'] + '"></td>';
                            // unified remove: delete the temp media by id (removeMedia) + drop the .row_image row.
                            // data-temp marks it as not-yet-saved so it drops without a confirmation.
                            var remove = '<td><a data-id="' + (result['media_id'] || '') + '" data-temp="1" class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a></td>';
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

    // Allow re-selecting the same file(s) to trigger change again.
    input.value = '';
}

ct = 1;
//upload single image
var index_add_photo = 0;
// Namespaced + off-before-on so pages that load BOTH this file and the site-upload.js
// (e.g. the site factorItem page) bind the handler only ONCE (no duplicate uploads).
$(document).off('change.fileUploadAjax', '.file_upload_ajax').on('change.fileUploadAjax', '.file_upload_ajax', function () {
    var file = $($(this)).prop('files')[0];
    if (file) {
        var file_attr_name = $(this).attr('name');
        var file_attr_id = $(this).attr('id');

        // Re-selecting a different file for a single field: drop the previously
        // uploaded temp so it isn't orphaned on disk (the hidden *_path only ever
        // holds a temp id, never a saved media id), then clear its hidden id.
        var prevTemp = $('#' + file_attr_id + '_path').val();
        if (prevTemp) {
            $.post('/dashboard/removeMedia', { media_id: prevTemp });
            $('#' + file_attr_id + '_path').val('');
        }

        var fileType = file["type"];
        var validImageTypes = [
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/svg+xml",
            "image/psd",
            "image/tiff",
            "image/gif",
            "application/pdf",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
            "application/x-rar-compressed",
            "application/x-zip-compressed"
        ];
        var size = file.size;
        if ($.inArray(fileType, validImageTypes) !== -1) {
            if (6000000 >= size) {
                var fd = new FormData();
                fd.append('file', file);
                var reader = new FileReader();
                var loading = `<div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading` + file_attr_id + `">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
                </div>`;
                $('#' + file_attr_id + '_src.show').append(loading);
                $('#loading' + file_attr_id).css('display', 'block');
                reader.onload = function (e) {
                    $.ajax({
                        type: "post",
                        url: '/dashboard/uploadFilesTemporarily',
                        data: fd,
                        contentType: false,
                        processData: false,
                        headers: {
                            "Authorization": localStorage.getItem('token')
                        },
                        success: function (result) {

                            if (result['status'] === true) {
                                $('#' + file_attr_id + '_src.show div').remove();
                                $('#' + file_attr_id + '_src.show img').remove();
                                var previewFile = '';
                                if (!((fileType == 'application/x-rar-compressed') || (fileType == 'application/x-zip-compressed') || (fileType == 'application/pdf') || (fileType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') || (fileType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))) {
                                    previewFile = '<img src="' + e.target.result + '" id="' + index_add_photo + '" ' +
                                        'class="rounded border" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">';
                                } else {
                                    // non-image (pdf/doc/zip...): view via the viewMedia route which streams
                                    // the temp file inline (temp uploads live on the private disk with no
                                    // public url). target=_blank -> pdf renders in the browser; other types
                                    // download and open in the OS program associated with the file extension.
                                    var viewHref = result['media_id'] ? ('/dashboard/viewMedia/' + result['media_id']) : '#';
                                    previewFile = '<a href="' + viewHref + '" target="_blank" rel="noopener" id="' + index_add_photo + '" ' +
                                        'class="disInline image_create btn border border-success mr-3" >مشاهده</a>';
                                }
                                if (file_attr_name.includes("[]") && !file_attr_name.includes("consent_image")) {
                                    new_link(file_attr_id);
                                    $('#' + file_attr_id + '_src' + ct + '.show').append(previewFile);
                                    $('#' + file_attr_id + ct + '.path').val(result['media_id']);
                                } else {
                                    var $src = $('#' + file_attr_id + '_src.show');
                                    // a remove (✕) button so the user can drop the selection before saving.
                                    // data-id = the temp media id -> the remove handler deletes it server-side.
                                    var tempRemove = '<div class="mt-1"><a href="javascript:void(0)" ' +
                                        'class="btn btn-sm remove_media_btn" data-id="' + (result['media_id'] || '') + '" data-field="' + file_attr_id + '" ' +
                                        'title="حذف عکس"><i class="fa fa-close"></i></a></div>';
                                    $src.empty().append(previewFile).append(tempRemove);
                                    $('#' + file_attr_id + '_path').val(result['media_id']);
                                }
                            } else if (result['status'] === false) {
                                let errorMessage = result['message'];
                                $('.alert-danger').remove();
                                let errorAlert = `
                                    <div class="alert alert-danger mb-4" role="alert">
                                        ${errorMessage}
                                    </div>
                                `;
                                $('#error-container').append(errorAlert);
                                $('html, body').animate({
                                    scrollTop: $('#error-container').offset().top - 100 // کمی بالاتر از خطا
                                }, 500);
                            }
                            $('#loading' + file_attr_id).css('display', 'none');
                        }
                    });
                };
                reader.readAsDataURL(file);
            } else {
                toastr.error('آپلود ناموفق : حجم عکس از 6 مگا بایت بیشتر می باشد.');
            }
        } else {
            toastr.error('آپلود ناموفق : فرمت فایل بایستی pdf|svg+xml|JPG|png|webp|xlsx باشد.');
        }
    }
    // reset the input so re-selecting the SAME file still fires 'change' (e.g. after removing it)
    this.value = '';
});

//flagProduct
$(document).on('click', '.flagProduct', function () {
    var index = $(this).attr('id');
    $("a.flagProduct > .radio_good").prop('checked', false);
    $("#" + index + ".radio_good").prop('checked', true);
});

//reviews
var counter_strength = $('.counter_review_strength').val();

var counter_weakness = 1;

$('.addInputStrength').click(function () {
    var new_div = document.createElement('div');
    new_div.id = 'dynamicInputStrength' + counter_strength;
    new_div.className = 'input-group mrg10';
    new_div.innerHTML = "<input type='text' class='form-control' name='reviewstrength[" + counter_strength + "][text]' placeholder='نقاط قوت'> " +
        "<span class='input-group-btn'>  <button id=" + counter_strength + " type='button' class='btn btn-danger removeInputStrength'>-</button></span> </div> ";
    document.getElementById('reviewstrength').appendChild(new_div);
    counter_strength++;

});

$(document).on('click', '.removeInputStrength', function () {
    var id = $(this).attr('id');
    var elem = document.getElementById('dynamicInputStrength' + id);
    return elem.parentNode.removeChild(elem);
});

$('.addInputWeakness').click(function () {
    var new_div = document.createElement('div');
    new_div.id = 'dynamicAddInputWeakness' + counter_weakness;
    new_div.className = 'input-group mrg10';
    new_div.innerHTML = "<input type='text' class='form-control' name='reviewWeakness[" + counter_weakness + "][text]' placeholder='نقاط ضعف'> " +
        "<span class='input-group-btn'>  <button id=" + counter_weakness + " type='button' class='btn btn-danger removeInputWeakness'>-</button></span> </div> ";
    document.getElementById('reviewWeakness').appendChild(new_div);
    counter_weakness++;

});

$(document).on('click', '.removeInputWeakness', function () {
    var id = $(this).attr('id');
    var elem = document.getElementById('dynamicAddInputWeakness' + id);
    return elem.parentNode.removeChild(elem);
});

//remove_review
$(document).on('click', '.remove_review', function () {
    var id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeReview',
        headers: {
            "Authorization": localStorage.getItem('token')
        },
        data: {
            id: id
        },
        success: function (result) {
            if (result['status'] == true) {
                $('#dynamicReview' + id).remove();
            }
        }
    });
});

// Jquery Dependency

$("input[data-type='currency']").on({
    keyup: function () {
        formatCurrency($(this));
    },
    blur: function () {
        formatCurrency($(this), "blur");
    }
});

function formatNumber(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}

function formatCurrency(input, blur) {
    // puts cursor back in right position.

    // get input value
    var input_val = input.val();

    // don't validate empty input
    if (input_val === "") {
        return;
    }

    // original length
    var original_len = input_val.length;

    // initial caret position
    var caret_pos = input.prop("selectionStart");

    // check for decimal
    if (input_val.indexOf(".") >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(".");

        // split number by decimal point
        // var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);

        // add commas to left side of number
        // left_side = formatNumber(left_side);

        // validate right side
        right_side = formatNumber(right_side);

        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
            // right_side += "00";
        }

        // Limit decimal to only 2 digits
        // right_side = right_side.substring(0, 2);

        // join number by .
        // input_val = left_side + "." + right_side;

    } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber(input_val);
        // input_val = input_val;

        // final formatting
        if (blur === "blur") {
            // input_val += ".00";
        }
    }

    // send updated string to input
    input.val(input_val);

    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}

$('.showConfirmDelete').click(function (event) {
    var form = $(this).closest("form");
    event.preventDefault();

    Swal.fire({
        title: "آیا مطمئن هستید؟",
        showDenyButton: true,
        confirmButtonText: 'بله',
        denyButtonText: 'خیر'
    })
        .then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });


});

//type brand in create new brand
$(document).on('click', '.type_zero', function () {
    $('.type_zero_box').css({
        'display': 'flex'
    });
    $('.type_one_box').css({
        'display': 'none'
    });
});

//type brand in create new brand
$(document).on('click', '.type_one', function () {
    $('.type_one_box').css({
        'display': 'flex'
    });
    $('.type_zero_box').css({
        'display': 'none'
    });
});

$(document).on('click', '.brandStore', function () {
    var type = null;
    if ($(".type:checked").val()) type = $(".type:checked").val();
    var request_by = $('.request_by').val();
    var category_id = $('.category_id').val();
    var title = $('.title').val();
    var title_en = $('.title_en').val();
    var description = $('.description').val();
    var link_zero = $('#link_zero').val();
    var link_one = $('#link_one').val();
    var registration_form = $('.registration_form_path').val();
    var logo = $('.logo_path').val();

    var valid = title && title_en && description && (type !== null) && logo && (type !== '0' || registration_form);
    if (valid) {
        $.ajax({
            type: "post",
            url: '/dashboard/site/brand',
            headers: {
                "Authorization": localStorage.getItem('token')
            },
            data: {
                request_by: request_by,
                category_id: category_id,
                title: title,
                title_en: title_en,
                description: description,
                type: type,
                registration_form_path: registration_form,
                link_zero: link_zero,
                link_one: link_one,
                logo_path: logo
            },
            success: function (result) {
                if (result['status'] === "true") {
                    toastr.success(result['message']);
                    $('#brandRequestModal').modal('toggle');
                    $('.type').prop('checked', false);
                    $('.request_by').val('');
                    $('#registration_form').html('');
                    $('#logo').html('');
                    $('.title').val('');
                    $('.title_en').val('');
                    $('.description').val('');
                    $('.link').val('');
                    $('.registration_form_path').val('');
                    $('.logo_path').val('');
                    // go to the brand list after a successful create
                    setTimeout(function () { window.location.href = '/dashboard/site/brand'; }, 800);
                }
                if (result['status'] === "warning") toastr.warning(result['message']);
            }
        });
    } else if (!logo) {
        toastr.warning('لطفا لوگوی برند را بارگذاری کنید');
    } else if (type === '0' && !registration_form) {
        toastr.warning('لطفا برگه ثبت برند را بارگذاری کنید');
    } else {
        toastr.warning('لطفا تمامی موارد ستاره دار را پر کنید');
    }

});

$(document).ready(function () {
    $('.selectSearch').select2();
});

$(document).on('click', '.remove-tr', function () {
    $(this).parents('tr').remove();
});


// work_sample
$(document).on('click', '.remove_work_sample', function () {
    var work_sample_id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgWorkSample',
        data: {
            work_sample_id: work_sample_id
        },
        success: function (result) {
            if (result['status'] == 'true') {
                $('#work_sample_' + work_sample_id).remove();
            }
        }
    });
});

$(document).ready(function () {
    //submit on create new user
    $("#create-user-form").submit(function (event) {
        var form = $(this);
        var formData = form.serialize();
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            }
        });
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: formData,
        }).done(function (data) {
            if (data.status == 'success') {
                swal("عملیات موفق! ", data.message, "success");
                clearNewUserFormInputsData();
            } else if (data.status == 'error') {
                swal("توجه! ", data.message, "error");
                clearNewUserFormInputsData();
            } else if (data.status == 'validation_error') {
                swal("خطا! ", data.messages.join('\n'), "error");
            }
        });
        event.preventDefault();
    });
//------------------------------------------------------------------------------
//select province for create new user
    $('#new_user_province_id').on('change', function () {
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
                $('#new_user_city_id')
                    .empty()
                    .append('<option value="all">انتخاب شهر</option>')
                ;
                $.each(data.cities, function (i, d) {
                    $('#new_user_city_id').append('<option value="' + d.id + '">' + d.name + '</option>');
                });
            }
        });
    });
//-----------------------------------------------------------------------------------------------------
});

function clearNewUserFormInputsData() {
    $('#new_user_name').val('');
    $('#new_user_family').val('');
    $('#new_user_gender').val('');
    $('#new_user_province_id').val('');
    $('#new_user_city_id').val('');
}
