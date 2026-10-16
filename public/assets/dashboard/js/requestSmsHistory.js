/*!
 * requestSmsHistory.js — تب «پیامک‌ها» در پنجره‌ی جزئیات درخواست.
 *
 * هر بار پنجره برای یک درخواست باز می‌شود (رویداد callcenter:opened) تب خالی می‌شود و با
 * اولین کلیک روی تب، فقط پیامک‌های همان درخواست از سرور خوانده می‌شود. متن پیام‌ها فقط با
 * textContent درج می‌شود (متن پیامک ورودی را مشتری می‌نویسد؛ هرگز innerHTML نمی‌شود).
 */
(function () {
    'use strict';

    var $ = window.jQuery;
    var pane = document.getElementById('smsHistory');
    var tab = document.getElementById('sms-history-tab');
    if (!$ || !pane || !tab) {
        return;
    }

    var state = pane.querySelector('.sms-history-state');
    var table = pane.querySelector('.sms-history-table');
    var tbody = pane.querySelector('.sms-history-tbody');
    var currentTask = null;
    var loadedFor = null;

    var STATUS = {
        delivered: ['تحویل شد', 'badge-success'],
        sent: ['ارسال شد', 'badge-info'],
        received: ['دریافت شد', 'badge-info'],
        failed: ['ناموفق', 'badge-danger'],
        queued: ['در صف', 'badge-warning'],
        pending: ['در انتظار', 'badge-warning']
    };

    function cell(text, className) {
        var td = document.createElement('td');
        td.textContent = text;
        if (className) { td.className = className; }
        return td;
    }

    function badge(text, cls) {
        var span = document.createElement('span');
        span.className = 'badge ' + cls + ' px-2 py-1';
        span.textContent = text;
        return span;
    }

    function reset() {
        loadedFor = null;
        tbody.textContent = '';
        table.classList.add('d-none');
        state.textContent = '';
    }

    function render(messages) {
        tbody.textContent = '';
        if (!messages.length) {
            table.classList.add('d-none');
            state.textContent = 'هیچ پیامکی برای این درخواست ثبت نشده است.';
            return;
        }
        state.textContent = '';
        messages.forEach(function (m, index) {
            var tr = document.createElement('tr');
            tr.appendChild(cell(String(index + 1)));

            var direction = document.createElement('td');
            direction.appendChild(m.direction === 'inbound' ? badge('دریافتی', 'badge-info') : badge('ارسالی', 'badge-success'));
            tr.appendChild(direction);

            tr.appendChild(cell(m.service || '-'));
            tr.appendChild(cell(m.party || '-', 'ltr'));

            var content = cell(m.redacted ? 'محرمانه - دسترسی محدود' : (m.content || '-'), 'text-right');
            if (m.redacted) { content.className += ' text-muted'; }
            tr.appendChild(content);

            var st = STATUS[m.status] || [m.status || '-', 'badge-secondary'];
            var statusCell = document.createElement('td');
            statusCell.appendChild(badge(st[0], st[1]));
            tr.appendChild(statusCell);

            tr.appendChild(cell(m.date || '-'));
            tbody.appendChild(tr);
        });
        table.classList.remove('d-none');
    }

    function load() {
        if (currentTask === null || loadedFor === currentTask) {
            return;
        }
        var taskId = currentTask;
        state.textContent = 'در حال بارگذاری...';
        $.ajax({
            url: pane.getAttribute('data-url-template').replace('__ID__', encodeURIComponent(taskId)),
            type: 'get',
            dataType: 'json',
            success: function (response) {
                if (taskId !== currentTask) { return; } // the modal moved on to another request
                loadedFor = taskId;
                render(response.data.messages);
            },
            error: function () {
                if (taskId !== currentTask) { return; }
                state.textContent = 'دریافت سابقه‌ی پیامک ناموفق بود.';
            }
        });
    }

    $(document).on('callcenter:opened', function (event, taskId) {
        currentTask = taskId;
        reset();
    });
    $(tab).on('click', load);
})();
