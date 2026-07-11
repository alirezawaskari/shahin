// remove slider Img in edit slider
$(document).on('click', '.remove_slider', function () {
    var slider_id = $(this).attr('id');
    $.ajax({
        type: "post",
        url: '/dashboard/removeImgSlider',
        headers: {
            "Authorization": localStorage.getItem('token')
        },
        data: {
            slider_id: slider_id
        },
        success: function (result) {
            if (result['status'] == 'true') {
                $('#slider_' + slider_id).remove();
            }
        }
    });
});

$(document).on('click', '.removeCatMenu', function () {
    var id = $(this).attr('id');
    $.ajax({
        url: "/dashboard/removeCatMenu",
        type: 'post',
        data: {
            id: id
        },
        headers: {
            "Authorization": localStorage.getItem('token')
        },
        success: function (result) {
            if (result['status'] == 'true') {
                $('#' + id).remove();
            }
        }
    })
});

//edit gallery
const themeBoxes = document.querySelectorAll('.theme-box');
const primaryPicker = document.getElementById('primaryPicker');
const secondaryPicker = document.getElementById('secondaryPicker');
const primaryText = document.getElementById('primaryText');
const secondaryText = document.getElementById('secondaryText');


document.getElementById('serviceSelect')?.addEventListener('change', function () {
    let serviceId = this.value;
    let itemSelect = document.getElementById('serviceItemSelect');

    itemSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

    if (!serviceId) {
        itemSelect.innerHTML = '<option value="">ابتدا سرویس را انتخاب کنید</option>';
        return;
    }

    fetch(`/service-items/${serviceId}`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                itemSelect.innerHTML = '<option value="">خالی است</option>';
                return;
            }

            let options = '<option value="">انتخاب زیرسرویس</option>';
            data.forEach(item => {
                options += `<option value="${item.id}">${item.title}</option>`;
            });
            itemSelect.innerHTML = options;
        })
        .catch(() => {
            itemSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
        });
});
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ajax save for the category edit form (no full-page form redirect)
$('body').on('click', '#category-update-submit', function (e) {
    e.preventDefault();
    var $btn = $(this);
    if ($btn.hasClass('disabled')) return;
    $btn.addClass('disabled').css('opacity', 0.6);

    var form = $('#category-edit-form');
    $.ajax({
        url: form.attr('action'),
        type: 'POST', // form carries _method=PATCH
        data: form.serialize(),
        success: function (res) {
            var msg = (res && res.messages && res.messages[0]) ? res.messages[0] : 'با موفقیت ویرایش شد';
            if (typeof toastr !== 'undefined') {
                toastr.success(msg);
            } else if (typeof swal !== 'undefined') {
                swal(msg, { icon: 'success', button: 'باشه' });
            }
            // update the UI in place (no reload): fill/replace image, icon and gallery slots
            if (typeof applySavedMedia === 'function') {
                applySavedMedia(res && res.media);
            }
            $btn.removeClass('disabled').css('opacity', 1);
        },
        error: function (xhr) {
            $btn.removeClass('disabled').css('opacity', 1);
            var msg = 'ویرایش انجام نشد، دوباره تلاش کنید.';
            if (typeof toastr !== 'undefined') {
                toastr.error(msg);
            } else if (typeof swal !== 'undefined') {
                swal('خطا', msg, 'error');
            }
            console.error('category update failed:', xhr.status, xhr.responseText);
        }
    });
});

$('#categorySelect, #serviceSelect').change(function () {

    $.ajax({
        url: "{{ route('ghorfeOnlineList.getGallery') }}",
        type: "POST",
        data: {
            category_id: $('#categorySelect').val(),
            service_id: $('#serviceSelect').val(),
            ghorfe_id: $('#ghorfe_id').val()
        },
        success: function (res) {
            $('#gallerySection').html(res);
        },
        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });

});

function previewImage(event, previewId) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (e) {
        document.getElementById(previewId).src = e.target.result;
    };

    reader.readAsDataURL(file);
}


// حذف عکس (فقط UI - backend باید هندل کند)
function removeImage(previewId) {
    const el = document.getElementById(previewId);
    el.src = "https://via.placeholder.com/150x100?text=Removed";
}
document.addEventListener('DOMContentLoaded', () => {

    initTabsNavigation();
    initMenuBuilder();
    initColorPicker();

    function initTabsNavigation() {
        const tabs = Array.from(document.querySelectorAll('.nav-link[data-toggle="pill"]'))
            .filter(t => t.offsetParent !== null);

        const getActiveIndex = () =>
            tabs.findIndex(t => t.classList.contains('active'));

        const showTab = (i) => {
            if (i < 0 || i >= tabs.length) return;
            $(tabs[i]).tab('show');
        };

        document.querySelectorAll('.btn-next').forEach(btn =>
            btn.addEventListener('click', () => showTab(getActiveIndex() + 1))
        );

        document.querySelectorAll('.btn-prev').forEach(btn =>
            btn.addEventListener('click', () => showTab(getActiveIndex() - 1))
        );
    }


    function initMenuBuilder() {
        const btn = document.getElementById('add-menu');
        const wrapper = document.getElementById('menu-wrapper');
        if (!btn || !wrapper) return;

        btn.addEventListener('click', () => {
            const items = wrapper.querySelectorAll('.menu-item');

            if (items.length >= 5) {
                alert('حداکثر 5 منو');
                return;
            }

            const last = items[items.length - 1];
            if (last && !isMenuValid(last)) {
                alert('لطفا منوی قبلی را کامل کنید');
                return;
            }

            wrapper.insertAdjacentHTML('beforeend', menuTemplate());

            if (wrapper.querySelectorAll('.menu-item').length >= 5) {
                btn.style.display = 'none';
            }
        });

        function isMenuValid(el) {
            return [...el.querySelectorAll('input[required]')]
                .every(i => i.value.trim() !== '');
        }

        function menuTemplate() {
            return `
                <div class="menu-item border p-2 mb-3">
                    <input type="hidden" name="ids[]" value="">
                    <div class="col-12">
                        <label>عنوان</label>
                        <input type="text" name="titles[]" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label>لینک</label>
                        <input type="text" name="links[]" class="form-control" required>
                    </div>
                </div>
            `;
        }
    }


    function initColorPicker() {
        const primary = document.getElementById('primaryPicker');
        const secondary = document.getElementById('secondaryPicker');
        const primaryText = document.getElementById('primaryText');
        const secondaryText = document.getElementById('secondaryText');

        if (!primary || !secondary) return;

        const sync = () => {
            primaryText.value = primary.value;
            secondaryText.value = secondary.value;
        };

        primary.addEventListener('input', sync);
        secondary.addEventListener('input', sync);

        sync();
    }
    const activeTab = document.querySelector('.mobile-tabs-wrapper .nav-link.active');

    if (activeTab) {
        activeTab.scrollIntoView({
            behavior: 'smooth',
            inline: 'center'
        });
    }
});
