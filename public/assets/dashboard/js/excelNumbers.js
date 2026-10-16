/*!
 * excelNumbers.js — «ورود شماره‌ها از اکسل» برای ارسال پیامک گروهی.
 *
 * فایل را به سرور می‌فرستد (فقط خواندن، بدون ذخیره)، شماره‌های معتبر را می‌گیرد و به
 * کادر «شماره‌های همراه اضافی» (#custom_numbers) می‌افزاید؛ از آن به بعد دقیقاً مثل شماره‌های
 * دستی بررسی، بی‌تکرار و هزینه‌گذاری می‌شوند.
 */
(function () {
    'use strict';

    var button = document.getElementById('btnExcelNumbers');
    var input = document.getElementById('excel_numbers_file');
    var target = document.getElementById('custom_numbers');
    var result = document.getElementById('excelNumbersResult');
    if (!button || !input || !target || !result) {
        return;
    }

    var fa = function (n) { return Number(n).toLocaleString('fa-IR'); };

    function show(type, text) {
        result.className = 'small text-' + type;
        result.textContent = text;
    }

    button.addEventListener('click', function () { input.click(); });

    input.addEventListener('change', function () {
        if (!input.files.length) {
            return;
        }
        var data = new FormData();
        data.append('file', input.files[0]);
        button.disabled = true;
        show('muted', 'در حال خواندن فایل...');

        fetch(button.getAttribute('data-url'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: data
        }).then(function (response) {
            return response.json().then(function (body) { return {status: response.status, body: body}; });
        }).then(function (res) {
            if (res.status !== 200 || res.body.status !== 'success') {
                var errors = res.body && res.body.errors ? Object.keys(res.body.errors).map(function (k) { return res.body.errors[k][0]; }).join(' ') : '';
                show('danger', (res.body && res.body.message) || errors || 'خواندن فایل ناموفق بود.');
                return;
            }

            var existing = target.value.split(/[\s,;]+/).filter(Boolean);
            var seen = {};
            var merged = [];
            existing.concat(res.body.numbers).forEach(function (n) {
                if (!seen[n]) { seen[n] = true; merged.push(n); }
            });
            var added = merged.length - existing.length;
            target.value = merged.join('\n');
            target.rows = Math.min(8, Math.max(2, merged.length));
            target.dispatchEvent(new Event('input', {bubbles: true}));

            var text = fa(res.body.valid_count) + ' شماره‌ی معتبر از فایل خوانده شد (' + fa(added) + ' مورد جدید به لیست افزوده شد)';
            if (res.body.duplicate_count) { text += '، ' + fa(res.body.duplicate_count) + ' تکراری'; }
            if (res.body.invalid_count) { text += '، ' + fa(res.body.invalid_count) + ' نامعتبر (نمونه: ' + res.body.invalid_sample.join('، ') + ')'; }
            show(res.body.invalid_count ? 'warning' : 'success', text + '.');
        }).catch(function () {
            show('danger', 'ارتباط با سرور برقرار نشد.');
        }).finally(function () {
            button.disabled = false;
            input.value = '';
        });
    });
})();
