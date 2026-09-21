$body = $("body");

// Global CSRF header for every dashboard ajax request (needed by the
// .file_upload_ajax / multipleImages uploads on all dashboard pages).
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * Universal upload indicator, with real byte progress.
 *
 * Hooks jQuery's GLOBAL ajax events rather than each call site, so every file upload —
 * the ones that already draw their own per-field spinner, the ones that never did, and
 * any added later — shows feedback with no extra work from the caller. Scoped to the
 * upload endpoints so ordinary ajax (saves, lookups, removeMedia) never triggers it, and
 * it counts overlapping uploads so the bar only clears when the last one finishes.
 *
 * The bar sits on #eff2fa and fills right-to-left (RTL reading direction) with the repo
 * green as bytes actually reach the server.
 */
(function () {
    if (window.__shahinUploadBarInstalled) return;   // both bundles define this; install once
    window.__shahinUploadBarInstalled = true;

    var PROGRESS_BG = '#eff2fa';
    var PROGRESS_FILL = '#28a745';   // repo green
    var PROGRESS_INK = '#0b3d21';    // readable on the bg AND on the fill sweeping under it

    var pending = 0;
    var active = [];   // one {loaded,total} per in-flight upload — drives the percentage

    function isUploadRequest(settings) {
        var url = (settings && settings.url) || '';
        return /uploadFilesTemporarily|ckeditor\/upload/i.test(url);
    }

    // Real byte progress needs the RAW XMLHttpRequest. The jqXHR wrapper handed to
    // ajaxSend/ajaxComplete has no .upload, so xhr.upload.onprogress is unreachable there.
    // jQuery builds its transport with `options.xhr()`, invoked with `this` = the settings —
    // the one hook that sees both the target URL and the real xhr.
    var baseXhrFactory = $.ajaxSettings.xhr;
    $.ajaxSetup({
        xhr: function () {
            var settings = this;
            var xhr = baseXhrFactory.call(settings);
            if (xhr && xhr.upload && isUploadRequest(settings)) {
                var rec = { loaded: 0, total: 0 };
                settings.__sulRec = rec;
                active.push(rec);
                xhr.upload.addEventListener('progress', function (ev) {
                    // lengthComputable is false for chunked/unknown-size bodies -> stay indeterminate
                    if (!ev.lengthComputable) return;
                    rec.loaded = ev.loaded;
                    rec.total = ev.total;
                    render();
                });
            }
            return xhr;
        }
    });

    /** Aggregate percent across overlapping uploads; -1 when the size isn't knowable. */
    function percent() {
        var loaded = 0, total = 0;
        for (var i = 0; i < active.length; i++) { loaded += active[i].loaded; total += active[i].total; }
        if (total <= 0) return -1;
        return Math.max(0, Math.min(100, Math.round(loaded / total * 100)));
    }

    /**
     * True once every in-flight upload has flushed all its bytes to the socket. xhr.upload
     * progress measures bytes handed to the OS send buffer, not bytes the server has received
     * or processed — on a fast/localhost link the body flushes almost instantly, so loaded
     * hits total long before the server finishes writing the file + Spatie row. Past this
     * point the wait is entirely server-side and no progress event will ever fire again.
     */
    function allBytesSent() {
        if (!active.length) return false;
        for (var i = 0; i < active.length; i++) {
            if (active[i].total <= 0 || active[i].loaded < active[i].total) return false;
        }
        return true;
    }

    function ensureUi() {
        if (document.getElementById('shahin_upload_loading')) return;

        var style = document.createElement('style');
        style.textContent =
            '#shahin_upload_loading{position:fixed;top:0;left:0;right:0;z-index:99999;display:none;' +
            'padding:10px 14px;background:' + PROGRESS_BG + ';color:' + PROGRESS_INK + ';font-size:14px;' +
            'line-height:1;box-shadow:0 2px 8px rgba(0,0,0,.2);overflow:hidden}' +
            // anchored right -> grows leftwards as it fills
            '#shahin_upload_loading .sul-fill{position:absolute;top:0;right:0;bottom:0;width:0;' +
            'background:' + PROGRESS_FILL + ';transition:width .12s linear}' +
            '#shahin_upload_loading.sul-indeterminate .sul-fill{width:100%;animation:sulpulse 1s ease-in-out infinite}' +
            '#shahin_upload_loading .sul-body{position:relative;display:flex;align-items:center;' +
            'justify-content:center;gap:8px}' +
            '#shahin_upload_loading .sul-spin{width:16px;height:16px;border:2px solid rgba(11,61,33,.25);' +
            'border-top-color:' + PROGRESS_INK + ';border-radius:50%;display:inline-block;' +
            'animation:sulspin .7s linear infinite}' +
            '@keyframes sulspin{to{transform:rotate(360deg)}}' +
            '@keyframes sulpulse{0%,100%{opacity:.25}50%{opacity:.5}}';
        document.head.appendChild(style);

        var bar = document.createElement('div');
        bar.id = 'shahin_upload_loading';
        bar.setAttribute('role', 'progressbar');
        bar.setAttribute('aria-live', 'polite');
        bar.innerHTML = '<div class="sul-fill"></div>' +
                        '<div class="sul-body"><span class="sul-spin"></span>' +
                        '<span class="sul-txt">در حال بارگذاری فایل...</span></div>';
        document.body.appendChild(bar);
    }

    function render() {
        ensureUi();
        var bar = document.getElementById('shahin_upload_loading');
        if (!bar) return;
        var fill = bar.querySelector('.sul-fill');

        if (pending <= 0) {
            bar.style.display = 'none';
            fill.style.width = '0%';
            bar.removeAttribute('aria-valuenow');
            bar.classList.remove('sul-indeterminate');
            return;
        }

        var multi = pending > 1;
        var pct = percent();

        if (pct >= 0 && !allBytesSent()) {
            // real byte progress: proportional fill. Visible only while bytes are still in
            // flight — on localhost this phase is near-instant.
            var txt = multi ? 'در حال بارگذاری ' + pending + ' فایل...' : 'در حال بارگذاری فایل...';
            bar.classList.remove('sul-indeterminate');
            fill.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
            bar.querySelector('.sul-txt').textContent = txt + ' ' + pct + '٪';
        } else if (allBytesSent()) {
            // bytes are up; now waiting on the server (file write, media row, conversions).
            // No progress event will fire, so show an indeterminate "processing" state rather
            // than freeze at a solid 100% that looks stuck.
            bar.classList.add('sul-indeterminate');
            bar.removeAttribute('aria-valuenow');
            bar.querySelector('.sul-txt').textContent = multi
                ? 'در حال پردازش ' + pending + ' فایل روی سرور...'
                : 'در حال پردازش فایل روی سرور...';
        } else {
            // size not knowable (chunked / unknown-length body) -> stay indeterminate
            bar.classList.add('sul-indeterminate');
            bar.removeAttribute('aria-valuenow');
            bar.querySelector('.sul-txt').textContent = multi
                ? 'در حال بارگذاری ' + pending + ' فایل...'
                : 'در حال بارگذاری فایل...';
        }
        bar.style.display = 'block';
    }

    $(document).ajaxSend(function (e, jqXhr, settings) {
        if (isUploadRequest(settings)) { pending++; render(); }
    });
    // fires on success AND error, so the bar can never get stuck on a failed upload
    $(document).ajaxComplete(function (e, jqXhr, settings) {
        if (!isUploadRequest(settings)) return;
        pending = Math.max(0, pending - 1);
        var rec = settings.__sulRec;
        if (rec) {
            var i = active.indexOf(rec);
            if (i > -1) active.splice(i, 1);
        }
        if (pending === 0) active.length = 0;   // belt-and-braces: never leak stale records
        render();
    });
})();

// Shared builders so saved & just-uploaded media render identically everywhere.
function spatieMediaHtml(url, mediaId, boxH) {
    boxH = boxH || 120;
    return '<div class="div_remove_btn d-inline-block mb-2 mt-2" id="media_' + mediaId + '">' +
        '<img src="' + url + '" class="rounded border" style="height:' + boxH + 'px;width:100%;object-fit:contain;background:#f8f9fa;" alt="">' +
        '<a data-id="' + mediaId + '" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>' +
        '</div>';
}
function spatiePlaceholderHtml(boxH) {
    boxH = boxH || 120;
    return '<div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2 mt-2" style="height:' + boxH + 'px;background:#f8f9fa;">بدون تصویر</div>';
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
                                if ((file_attr_name || '').includes("[]") && !(file_attr_name || '').includes("consent_image")) {
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
    var logo = $('.logo_path').val();
    var valid = category_id && title && title_en && description && (type !== null) && logo;
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
                    $('#logo').html('');
                    $('.title').val('');
                    $('.title_en').val('');
                    $('.description').val('');
                    $('.link').val('');
                    $('.logo_path').val('');
                    // go to the brand list after a successful create
                    setTimeout(function () { window.location.href = '/dashboard/site/brand'; }, 800);
                }
                if (result['status'] === "warning") toastr.warning(result['message']);
            }
        });
    } else if (!logo) {
        toastr.warning('لطفا لوگوی برند را بارگذاری کنید');
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

//-----------------------------------------------------------------------------------------------------
// اتصال قرارداد — the contract association widget (Admin/contract/partials/attach.blade.php).
//
// Delegated + namespaced with off-before-on, so several widgets can sit on one page
// (a service has one per usage location) without stacking duplicate handlers.
//-----------------------------------------------------------------------------------------------------
// CKEditor is loaded per-page in this admin (service edit has it, role edit does not),
// so the widget brings its own copy when the host page has none. Everything queues
// behind a single load.
var contractCkQueue = [];
var contractCkLoading = false;

function contractEnsureCkeditor(done) {
    if (window.CKEDITOR) {
        done();
        return;
    }

    contractCkQueue.push(done);

    if (contractCkLoading) {
        return;
    }

    contractCkLoading = true;

    $.getScript('/assets/dashboard/js/ckeditor.js')
        .done(function () {
            $.each(contractCkQueue, function (i, cb) { cb(); });
            contractCkQueue = [];
        })
        .fail(function () {
            // No editor available: the plain textareas still submit their html.
            contractCkQueue = [];
        });
}

// Instantiated only once a pane is actually visible — CKEditor 4 mis-measures itself
// when replaced inside a display:none container.
function contractInitEditors($pane) {
    var $areas = $pane.find('textarea.contract-rich-text').filter(function () {
        return !$(this).data('ck-done');
    });

    if (!$areas.length) {
        return;
    }

    contractEnsureCkeditor(function () {
        $areas.each(function () {
            var id = this.id;

            if (!id || CKEDITOR.instances[id]) {
                return;
            }

            $(this).data('ck-done', true);
            CKEDITOR.replace(id, window.ck_uploader || {});
        });
    });
}

// Writes editor content back into the textarea. CKEditor hooks form submit itself, but
// only for editors it replaced — doing it explicitly keeps the ajax-filled ones honest.
function contractSyncEditors($widget) {
    if (!window.CKEDITOR) {
        return;
    }

    $widget.find('textarea.contract-rich-text').each(function () {
        var instance = CKEDITOR.instances[this.id];

        if (instance) {
            instance.updateElement();
        }
    });
}

function contractSetEditorValue($textarea, value) {
    var id = $textarea.attr('id');

    $textarea.val(value || '');

    if (window.CKEDITOR && id && CKEDITOR.instances[id]) {
        CKEDITOR.instances[id].setData(value || '');
    }
}

function contractAttachSyncPanes($widget) {
    var mode = $widget.find('.contract-mode:checked').val();

    var $existing = $widget.find('.contract-pane[data-pane="existing"]').toggle(mode === 'existing');
    var $new = $widget.find('.contract-pane[data-pane="new"]').toggle(mode === 'new');
    // Number and date belong to the association, so they show for both attach modes.
    $widget.find('.contract-pane[data-pane="meta"]').toggle(mode === 'existing' || mode === 'new');

    // The edit box only makes sense once a contract is actually selected.
    $widget.find('.contract-edit-box')
        .toggle(mode === 'existing' && !!$widget.find('.contract-select').val());

    if (mode === 'existing') {
        contractInitEditors($existing);
    } else if (mode === 'new') {
        contractInitEditors($new);
    }
}

$(document)
    .off('change.contractMode', '.contract-attach-widget .contract-mode')
    .on('change.contractMode', '.contract-attach-widget .contract-mode', function () {
        contractAttachSyncPanes($(this).closest('.contract-attach-widget'));
    });

// Fills the contract dropdown with the contracts of the currently chosen folder.
//
// keepSelection: re-selects the contract that was already chosen (the one the server
// rendered as attached), set with .val() so no change event fires — the edit box and
// its server-prefilled interlock stay exactly as rendered.
function contractLoadFolderContracts($widget, keepSelection) {
    var $select = $widget.find('.contract-select');
    var selectedId = keepSelection ? $select.val() : '';
    var selectedLabel = keepSelection ? $select.find('option:selected').text() : '';

    $select.empty().append('<option value="">در حال بارگذاری...</option>');

    $.get('/dashboard/contractUsage/byFolder', { folder_id: $widget.find('.contract-folder-filter').val() }, function (data) {
        $select.empty().append('<option value="">لطفا انتخاب کنید..</option>');

        $.each(data.contracts, function (i, contract) {
            var label = contract.type ? contract.title + ' (' + contract.type + ')' : contract.title;
            $select.append($('<option>').val(contract.id).text(label));
        });

        if (selectedId) {
            // Keep the attached contract selectable even if it has since left this folder.
            if (!$select.find('option[value="' + selectedId + '"]').length) {
                $select.append($('<option>').val(selectedId).text(selectedLabel));
            }
            $select.val(selectedId);
        } else if (!data.contracts.length) {
            $select.empty().append('<option value="">قراردادی در این پوشه وجود ندارد</option>');
        }
    }).fail(function () {
        $select.empty().append('<option value="">خطا در دریافت قراردادها</option>');
    });
}

// Folder -> contract cascade.
$(document)
    .off('change.contractFolder', '.contract-attach-widget .contract-folder-filter')
    .on('change.contractFolder', '.contract-attach-widget .contract-folder-filter', function () {
        var $widget = $(this).closest('.contract-attach-widget');

        // The previous selection is gone, so nothing is safe to edit yet.
        $widget.find('.contract-edit-box').hide().find('.contract-edit-id').val('');

        contractLoadFolderContracts($widget, false);
    });

// Selecting a contract loads it into the edit box, so it can be changed right here
// instead of from the contract bank.
$(document)
    .off('change.contractSelect', '.contract-attach-widget .contract-select')
    .on('change.contractSelect', '.contract-attach-widget .contract-select', function () {
        var $widget = $(this).closest('.contract-attach-widget');
        var $box = $widget.find('.contract-edit-box');
        var contractId = $(this).val();

        // Clear the interlock first: until the fetch lands, the fields do not hold this
        // contract's text and the server must not accept them as an edit.
        $widget.find('.contract-edit-id').val('');

        if (!contractId) {
            $box.hide();
            return;
        }

        $box.show();
        contractInitEditors($widget.find('.contract-pane[data-pane="existing"]'));

        $.get('/dashboard/contractUsage/contract/' + contractId, function (data) {
            var contract = data.contract;

            $widget.find('.contract-edit-title').val(contract.title || '');
            $widget.find('.contract-edit-type').val(contract.type || '');
            contractSetEditorValue($widget.find('.contract-edit-content'), contract.content);

            var $currentFile = $widget.find('.contract-current-file');
            if (contract.has_file) {
                $currentFile.show().find('.contract-current-file-link').attr('href', contract.file_url);
            } else {
                $currentFile.hide();
            }

            // Editing a shared contract changes it everywhere it is used — say so.
            var $warning = $widget.find('.contract-shared-warning');
            if (contract.usages_count > 1) {
                $warning.text(
                    'توجه: این قرارداد در ' + contract.usages_count +
                    ' محل استفاده می شود. تغییرات در همه آن ها اعمال خواهد شد.'
                ).show();
            } else {
                $warning.hide();
            }

            // Only now do the fields actually hold this contract's text.
            $widget.find('.contract-edit-id').val(contract.id);
        }).fail(function () {
            $widget.find('.contract-shared-warning')
                .text('خطا در دریافت اطلاعات قرارداد. ویرایش متن در این حالت ذخیره نمی شود.')
                .show();
        });
    });

// CKEditor writes back to its textarea on submit only for editors it replaced itself.
$(document)
    .off('submit.contractAttach', '.contract-attach-widget form')
    .on('submit.contractAttach', '.contract-attach-widget form', function () {
        contractSyncEditors($(this).closest('.contract-attach-widget'));
    });

$(function () {
    $('.contract-attach-widget').each(function () {
        contractAttachSyncPanes($(this));

        // The dropdown is rendered with at most the attached contract, and the folder
        // select already sits on its default («دسته بندی نشده» when nothing is attached),
        // so choosing that folder fires no change and the list would never load. Load it
        // up front instead.
        contractLoadFolderContracts($(this), true);
    });
});
