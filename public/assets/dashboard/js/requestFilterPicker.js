/*!
 * requestFilterPicker.js — پنجره‌ی «فیلتر بر اساس درخواست‌ها» در صفحه‌ی ارسال پیامک گروهی.
 *
 * فیلدهای پنجره همان فیلدهای صفحه‌ی درخواست‌هاست. با «اعمال فیلتر»، مقادیر پرشده به‌صورت
 * inputهای مخفی filters[request_filter][...] داخل فرم ارسال گروهی قرار می‌گیرند؛ چون صفحه
 * پیش‌نمایش و ارسال را با new FormData(form) می‌سازد، بدون هیچ تغییر دیگری همراه آن‌ها می‌رود.
 */
(function () {
    'use strict';

    var $ = window.jQuery;
    var modalEl = document.getElementById('requestFilterModal');
    var form = document.getElementById('bulkSmsForm');
    var host = document.getElementById('requestFilterInputs');
    if (!$ || !modalEl || !form || !host || typeof bootstrap === 'undefined') {
        return;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var $filterForm = $('#request_filter_form');
    var badge = document.getElementById('requestFilterBadge');

    // Province -> city, same endpoint the requests page uses.
    $('#province_id').on('change', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '/city/select', type: 'post', dataType: 'json',
            data: {_token: token, id: $(this).val()},
            success: function (data) {
                var $city = $('#city_id').empty().append('<option value="all">انتخاب شهر</option>');
                $.each(data.cities, function (i, d) {
                    $city.append('<option value="' + d.id + '">' + d.name + '</option>');
                });
            }
        });
    });

    function activeFields() {
        return $filterForm.serializeArray().filter(function (f) {
            return f.value !== '' && f.value !== 'all' && f.name !== 'exel' && f.name !== '_token';
        });
    }

    function render(count) {
        if (count === null) {
            host.innerHTML = '';
            badge.classList.add('d-none');
            document.body.classList.remove('has-request-filter');
            return;
        }
        badge.classList.remove('d-none');
        badge.querySelector('[data-count]').textContent = count.toLocaleString('fa-IR');
        document.body.classList.add('has-request-filter');
    }

    function apply() {
        var fields = activeFields();
        host.innerHTML = '';

        // The sentinel makes "no criteria" still mean "audience = all requests" on the server,
        // rather than silently falling back to the user-field filters.
        var inputs = [['filters[request_filter][__source]', 'requests']];
        fields.forEach(function (field) {
            var bracket = field.name.indexOf('[');
            var name = bracket === -1
                ? 'filters[request_filter][' + field.name + ']'
                : 'filters[request_filter][' + field.name.slice(0, bracket) + ']' + field.name.slice(bracket);
            inputs.push([name, field.value]);
        });

        inputs.forEach(function (pair) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            host.appendChild(input);
        });

        render(fields.length);
        modal.hide();
    }

    function clear() {
        $filterForm[0].reset();
        $filterForm.find('select').val(null).trigger('change');
        render(null);
        modal.hide();
    }

    document.getElementById('request_filter_apply').addEventListener('click', apply);
    document.getElementById('request_filter_clear').addEventListener('click', clear);
    document.getElementById('open_request_filter').addEventListener('click', function () { modal.show(); });
    document.getElementById('request_filter_remove').addEventListener('click', clear);
})();
