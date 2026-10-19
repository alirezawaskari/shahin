/*!
 * requestFilterSms.js — «ارسال پیامک به نتیجه فیلتر» در صفحه‌ی درخواست‌ها.
 *
 * فیلتر فعلی صفحه (#filter_form) را همان‌طور که هست به‌عنوان filters[request_filter] به
 * endpointهای ارسال گروهی می‌فرستد؛ پس سطح دسترسی، بررسی موجودی، حذف انصراف‌دهندگان و
 * ثبت کمپین دقیقاً همان ارسال گروهی است. سمت سرور فقط به __source=requests نگاه می‌کند تا
 * «فیلتر خالی» هم به‌معنی «همه‌ی درخواست‌ها» باشد و نه «همه‌ی کاربران کسب‌وکار».
 */
(function () {
    'use strict';

    var modalEl = document.getElementById('filterSmsModal');
    var openBtn = document.getElementById('open_filter_sms');
    if (!modalEl || !openBtn || typeof bootstrap === 'undefined') {
        return;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var $ = window.jQuery;
    var previewed = false;

    var el = function (id) { return document.getElementById(id); };
    var fa = function (n) { return Number(n).toLocaleString('fa-IR'); };

    function showAlert(type, text) {
        var box = el('fsms_alert');
        box.className = 'alert alert-' + type + ' py-2 small';
        box.textContent = text;
    }

    function hideAlert() {
        el('fsms_alert').className = 'alert d-none py-2 small';
    }

    /** Current filter form -> FormData entries under filters[request_filter]. */
    function buildPayload() {
        var data = new FormData();
        data.append('recipient_type', 'normal');
        data.append('sender_number_id', el('fsms_sender').value);
        data.append('message', el('fsms_message').value);
        if (el('fsms_template').value) {
            data.append('sms_message_template_id', el('fsms_template').value);
        }
        data.append('filters[request_filter][__source]', 'requests');
        // Direct SMS: the server requires the view-phone-numbers permission for this flag.
        data.append('filters[request_filter][__direct]', '1');

        var businessId = modalEl.getAttribute('data-business-id');

        $('#filter_form').serializeArray().forEach(function (field) {
            if (field.name === 'exel' || field.name === '_token' || field.value === '') {
                return;
            }
            // The staff-only business picker decides which business the campaign is sent from.
            if (field.name === 'ghorfe_online_list') {
                if (field.value !== 'all') {
                    businessId = field.value;
                }
                return;
            }
            var bracket = field.name.indexOf('[');
            var name = bracket === -1
                ? 'filters[request_filter][' + field.name + ']'
                : 'filters[request_filter][' + field.name.slice(0, bracket) + ']' + field.name.slice(bracket);
            data.append(name, field.value);
        });

        data.append('business_id', businessId);

        return data;
    }

    function hasActiveFilter() {
        return $('#filter_form').serializeArray().some(function (f) {
            return f.value !== '' && f.value !== 'all' && f.name !== 'exel' && f.name !== '_token' && f.name !== 'ghorfe_online_list';
        });
    }

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
            body: data
        }).then(function (response) {
            return response.json().then(function (body) { return {status: response.status, body: body}; });
        });
    }

    function failureText(res) {
        if (res.body && res.body.message) {
            return res.body.message;
        }
        if (res.body && res.body.errors) {
            return Object.keys(res.body.errors).map(function (k) { return res.body.errors[k][0]; }).join(' ');
        }
        return 'خطای نامشخص. لطفا دوباره تلاش کنید.';
    }

    function setSendEnabled(on) {
        el('fsms_send').disabled = !on;
    }

    function preview() {
        hideAlert();
        setSendEnabled(false);
        previewed = false;
        var button = el('fsms_preview');
        button.disabled = true;

        return post(modalEl.getAttribute('data-preview-url'), buildPayload()).then(function (res) {
            button.disabled = false;
            if (res.status !== 200 || res.body.status !== 'success') {
                showAlert('danger', failureText(res));
                return;
            }
            var rec = res.body.recipient_summary;
            var msg = res.body.message_summary;
            var fin = res.body.financial_summary;

            el('fsms_summary').classList.remove('d-none');
            el('fsms_valid').textContent = fa(rec.valid_count);
            el('fsms_invalid').textContent = fa(rec.invalid_count);
            el('fsms_dup').textContent = fa(rec.duplicate_count);
            el('fsms_pages').textContent = fa(msg.page_count);
            el('fsms_cost').textContent = fa(fin.estimated_total_cost) + ' ریال';
            el('fsms_balance').textContent = fa(fin.current_balance) + ' ریال';
            el('fsms_enough').textContent = fin.has_sufficient_balance ? 'کافی' : 'ناکافی (کسری ' + fa(fin.balance_deficit) + ' ریال)';
            el('fsms_enough').className = 'fw-bold ' + (fin.has_sufficient_balance ? 'text-success' : 'text-danger');

            var hasMessage = el('fsms_message').value.trim() !== '' || el('fsms_template').value !== '';
            if (rec.valid_count === 0) {
                showAlert('warning', 'هیچ مخاطب معتبری با این فیلتر پیدا نشد.');
            } else if (!hasMessage) {
                showAlert('warning', 'متن پیامک یا یک قالب را انتخاب کنید.');
            } else if (!fin.has_sufficient_balance) {
                showAlert('danger', 'موجودی پنل تبلیغاتی برای این ارسال کافی نیست.');
            } else {
                previewed = true;
                setSendEnabled(true);
            }
        }).catch(function () {
            button.disabled = false;
            showAlert('danger', 'ارتباط با سرور برقرار نشد.');
        });
    }

    function send() {
        var count = el('fsms_valid').textContent;
        var warning = hasActiveFilter() ? '' : '\n\nهشدار: هیچ فیلتری اعمال نشده است و پیامک برای «همه‌ی درخواست‌ها» ارسال می‌شود.';
        if (!previewed || !window.confirm('پیامک برای ' + count + ' مخاطب ارسال شود؟ این کار قابل بازگشت نیست.' + warning)) {
            return;
        }

        setSendEnabled(false);
        post(modalEl.getAttribute('data-send-url'), buildPayload()).then(function (res) {
            if (res.status === 200 && res.body.status === 'success') {
                showAlert('success', res.body.message);
                if (res.body.status_url) {
                    var link = document.createElement('a');
                    link.href = res.body.status_url;
                    link.className = 'ms-2';
                    link.textContent = 'مشاهده وضعیت کمپین';
                    el('fsms_alert').appendChild(link);
                }
                return;
            }
            showAlert('danger', failureText(res));
            setSendEnabled(true);
        }).catch(function () {
            showAlert('danger', 'ارتباط با سرور برقرار نشد.');
            setSendEnabled(true);
        });
    }

    openBtn.addEventListener('click', function () {
        hideAlert();
        previewed = false;
        setSendEnabled(false);
        modal.show();
    });

    var previewBtn = el('fsms_preview');
    var sendBtn = el('fsms_send');
    if (previewBtn) { previewBtn.addEventListener('click', preview); }
    if (sendBtn) { sendBtn.addEventListener('click', send); }

    // Any edit invalidates the last preview: the numbers shown no longer describe what would be sent.
    ['fsms_sender', 'fsms_message', 'fsms_template'].forEach(function (id) {
        var field = el(id);
        if (!field) { return; }
        field.addEventListener('input', function () { previewed = false; setSendEnabled(false); });
        field.addEventListener('change', function () { previewed = false; setSendEnabled(false); });
    });

    var template = el('fsms_template');
    if (template) {
        template.addEventListener('change', function () {
            var option = template.options[template.selectedIndex];
            if (option && option.getAttribute('data-content')) {
                el('fsms_message').value = option.getAttribute('data-content');
            }
        });
    }
})();
