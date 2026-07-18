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

/**
 * Universal upload indicator with real byte progress
 * (mirrors the dashboard bundle — site pages don't load adminJs.js).
 *
 * Hooks jQuery's GLOBAL ajax events rather than each call site, so every file upload shows
 * feedback with no extra work from the caller. Scoped to the upload endpoints so ordinary
 * ajax never triggers it, and it counts overlapping uploads so the bar only clears when the
 * last one finishes.
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
