/*!
 * requestFilterPicker.js — کامپوننت «فیلتر بر اساس درخواست‌ها» در صفحه‌ی ارسال پیامک گروهی.
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
    var badgeContainer = document.getElementById('requestFilterBadgeContainer');
    var summaryEl = document.getElementById('requestFilterSummary');
    var quickClearBtn = document.getElementById('request_filter_quick_clear');

    // Friendly labels for summary chips
    var FIELD_LABELS = {
        'name': 'نام',
        'family': 'نام خانوادگی',
        'user_phone': 'موبایل',
        'description': 'موضوع درخواست',
        'startDate': 'از تاریخ',
        'endDate': 'تا تاریخ',
        'sex': 'جنسیت',
        'has_factor': 'فاکتور',
        'type': 'روش ورود',
        'request_type': 'نوع درخواست',
        'status': 'وضعیت',
        'province': 'استان',
        'city': 'شهر',
        'product_category': 'دسته کالا',
        'service_category': 'دسته خدمت',
        'sub_service_category': 'خدمت',
        'service_items': 'نوع خدمت',
        'product': 'کالا',
        'product_attribute': 'تنوع کالا',
        'users': 'کارشناس',
        'agents': 'بازاریاب'
    };

    function initFilterSelect2() {
        $filterForm.find('select').each(function () {
            var $el = $(this);
            var ph = $el.attr('data-placeholder') || 'انتخاب کنید';
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                dir: 'rtl',
                placeholder: ph,
                allowClear: true,
                width: '100%',
                dropdownParent: $(modalEl)
            });
        });
    }

    // Initialize Select2 when modal is shown so dimensions and positions calculate properly
    modalEl.addEventListener('shown.bs.modal', function () {
        initFilterSelect2();
    });

    // Cascading: Province -> City
    $filterForm.on('change', '#province_id', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var provId = $(this).val();
        var $city = $filterForm.find('#city_id').empty().append('<option value="all">همه شهرها</option>');
        if (!provId || provId === 'all') {
            $city.trigger('change');
            return;
        }
        $.ajax({
            url: '/city/select', type: 'post', dataType: 'json',
            data: { _token: token, id: provId },
            success: function (data) {
                if (data && data.cities) {
                    $.each(data.cities, function (i, d) {
                        $city.append('<option value="' + d.id + '">' + d.name + '</option>');
                    });
                }
                $city.trigger('change');
            }
        });
    });

    // Cascading: Service Category -> Sub Service Category
    $filterForm.on('change', '#service_category', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var catId = $(this).val();
        var $sub = $filterForm.find('#sub_service_category').empty();
        $filterForm.find('#service_items').empty().trigger('change');
        if (!catId || (Array.isArray(catId) && catId.length === 0)) {
            $sub.trigger('change');
            return;
        }
        $.ajax({
            url: '/dashboard/service/selectByCategory', type: 'post', dataType: 'json',
            data: { _token: token, id: catId },
            success: function (response) {
                if (response && response.services) {
                    $.each(response.services, function (i, d) {
                        $sub.append('<option value="' + d.id + '">' + (d.title || d.name) + '</option>');
                    });
                }
                $sub.trigger('change');
            }
        });
    });

    // Cascading: Sub Service Category -> Service Items
    $filterForm.on('change', '#sub_service_category', function () {
        var token = $('meta[name="csrf-token"]').attr('content');
        var sId = $(this).val();
        var $items = $filterForm.find('#service_items').empty();
        if (!sId || (Array.isArray(sId) && sId.length === 0)) {
            $items.trigger('change');
            return;
        }
        $.ajax({
            url: '/dashboard/service/fetch-serviceitem-by-service', type: 'post', dataType: 'json',
            data: { _token: token, id: sId },
            success: function (response) {
                if (response && response.service_items) {
                    $.each(response.service_items, function (i, d) {
                        $items.append('<option value="' + d.id + '">' + (d.title || d.name) + '</option>');
                    });
                }
                $items.trigger('change');
            }
        });
    });

    // Fills a select with the options of one ajax call per selected parent id (the product
    // endpoints take a single id), skipping anything already added.
    function loadChildren($target, url, ids, responseKey) {
        $target.empty();
        if (!ids || ids.length === 0) {
            $target.trigger('change');
            return;
        }
        var token = $('meta[name="csrf-token"]').attr('content');
        var seen = {};
        var pending = ids.length;
        ids.forEach(function (id) {
            $.ajax({
                url: url, type: 'post', dataType: 'json',
                data: { _token: token, id: id },
                success: function (response) {
                    $.each((response && response[responseKey]) || [], function (i, d) {
                        if (!seen[d.id]) {
                            seen[d.id] = true;
                            $target.append('<option value="' + d.id + '">' + d.label + '</option>');
                        }
                    });
                },
                complete: function () {
                    if (--pending === 0) {
                        $target.trigger('change');
                    }
                }
            });
        });
    }

    // Cascading: Product Category -> Product -> Product Attribute (variant)
    $filterForm.on('change', '#product_category', function () {
        loadChildren($filterForm.find('#product_item'), '/dashboard/callcenter/productsByCategory', $(this).val(), 'products');
    });
    $filterForm.on('change', '#product_item', function () {
        loadChildren($filterForm.find('#product_attribute'), '/dashboard/callcenter/productAttributes', $(this).val(), 'product_attributes');
    });

    function activeFields() {
        return $filterForm.serializeArray().filter(function (f) {
            return f.value !== '' && f.value !== 'all' && f.name !== 'exel' && f.name !== '_token';
        });
    }

    function renderSummary(fields) {
        if (!summaryEl) return;
        summaryEl.innerHTML = '';
        if (!fields || !fields.length) return;

        // Group fields by base name
        var grouped = {};
        fields.forEach(function (f) {
            var baseName = f.name.replace('[]', '');
            if (!grouped[baseName]) {
                grouped[baseName] = [];
            }
            grouped[baseName].push(f.value);
        });

        Object.keys(grouped).forEach(function (key) {
            var label = FIELD_LABELS[key] || key;
            var vals = grouped[key];
            var displayText = label + ': ' + (vals.length > 1 ? vals.length + ' مورد' : vals[0]);

            var chip = document.createElement('span');
            chip.className = 'badge bg-light text-dark border py-1 px-2 small';
            chip.textContent = displayText;
            summaryEl.appendChild(chip);
        });
    }

    function render(count, fields) {
        if (count === null || count === 0) {
            host.innerHTML = '';
            if (badge) badge.classList.add('d-none');
            if (badgeContainer) badgeContainer.classList.add('d-none');
            if (quickClearBtn) quickClearBtn.classList.add('d-none');
            if (summaryEl) summaryEl.innerHTML = '';
            document.body.classList.remove('has-request-filter');
            return;
        }

        if (badge) {
            badge.classList.remove('d-none');
            var countEl = badge.querySelector('[data-count]');
            if (countEl) countEl.textContent = count.toLocaleString('fa-IR');
        }
        if (badgeContainer) badgeContainer.classList.remove('d-none');
        if (quickClearBtn) quickClearBtn.classList.remove('d-none');
        renderSummary(fields);
        document.body.classList.add('has-request-filter');
    }

    function apply() {
        var fields = activeFields();
        host.innerHTML = '';

        if (fields.length === 0) {
            render(0, []);
            modal.hide();
            triggerPreviewSafe();
            return;
        }

        // The sentinel makes criteria mean "audience = requests matching filter"
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

        render(fields.length, fields);
        modal.hide();
        triggerPreviewSafe();
    }

    function clear() {
        $filterForm[0].reset();
        $filterForm.find('select').val(null).trigger('change');
        liveSeq++;
        liveMessage('<span class="text-muted small">با انتخاب فیلترها، تعداد مخاطبین اینجا نمایش داده می‌شود.</span>');
        render(null, []);
        modal.hide();
        triggerPreviewSafe();
    }

    function triggerPreviewSafe() {
        if (typeof window.triggerPreview === 'function') {
            window.triggerPreview();
        } else if (typeof triggerPreview === 'function') {
            triggerPreview();
        }
    }

    // ---- Live count inside the modal (only on pages that give the modal a preview URL) ----
    var previewUrl = modalEl.getAttribute('data-preview-url');
    var liveBox = document.getElementById('request_filter_live');
    var liveTimer = null;
    var liveSeq = 0;

    function liveMessage(html) {
        if (liveBox) liveBox.innerHTML = html;
    }

    function liveFormData(fields) {
        var fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        var panel = form.querySelector('input[name="service_type"]:checked');
        if (panel) fd.append('service_type', panel.value);
        var custom = form.querySelector('[name="custom_numbers"]');
        if (custom && custom.value) fd.append('custom_numbers', custom.value);
        fd.append('filters[request_filter][__source]', 'requests');
        fields.forEach(function (field) {
            var bracket = field.name.indexOf('[');
            fd.append(bracket === -1
                ? 'filters[request_filter][' + field.name + ']'
                : 'filters[request_filter][' + field.name.slice(0, bracket) + ']' + field.name.slice(bracket), field.value);
        });
        return fd;
    }

    function refreshLiveCount() {
        if (!previewUrl || !liveBox) return;
        var fields = activeFields();
        if (!fields.length) {
            liveMessage('<span class="text-muted small">با انتخاب فیلترها، تعداد مخاطبین اینجا نمایش داده می‌شود.</span>');
            return;
        }
        var seq = ++liveSeq;
        liveMessage('<span class="text-muted small"><i class="fa fa-spinner fa-spin ms-1"></i> در حال شمارش...</span>');
        fetch(previewUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: liveFormData(fields)
        })
        .then(function (r) { return r.json().then(function (body) { return { status: r.status, body: body }; }); })
        .then(function (res) {
            if (seq !== liveSeq) return; // a newer combination is already being counted
            if (res.status === 403 || res.body.status === 'error') {
                liveMessage('<span class="text-danger small"><i class="fa fa-exclamation-circle ms-1"></i> ' + (res.body.message || 'امکان شمارش وجود ندارد.') + '</span>');
                return;
            }
            var rec = res.body.recipient_summary || {};
            var valid = Number(rec.valid_count || 0);
            liveMessage(
                '<span class="fw-bold ' + (valid > 0 ? 'text-success' : 'text-danger') + '">' +
                '<i class="fa fa-users ms-1"></i> ' + valid.toLocaleString('fa-IR') + ' مخاطب معتبر</span>' +
                '<span class="text-muted small me-2"> (کل: ' + Number(rec.total_candidates || 0).toLocaleString('fa-IR') +
                '، نامعتبر: ' + Number(rec.invalid_count || 0).toLocaleString('fa-IR') +
                '، تکراری: ' + Number(rec.duplicate_count || 0).toLocaleString('fa-IR') + ')</span>'
            );
        })
        .catch(function () {
            if (seq === liveSeq) liveMessage('<span class="text-danger small">خطای ارتباط با سرور</span>');
        });
    }

    function scheduleLiveCount() {
        clearTimeout(liveTimer);
        liveTimer = setTimeout(refreshLiveCount, 600);
    }

    if (previewUrl) {
        // Select2 and plain inputs both end in a change/input event on the filter form.
        $filterForm.on('change input', scheduleLiveCount);
        modalEl.addEventListener('shown.bs.modal', refreshLiveCount);
    }

    var applyBtn = document.getElementById('request_filter_apply');
    if (applyBtn) applyBtn.addEventListener('click', apply);

    var clearBtn = document.getElementById('request_filter_clear');
    if (clearBtn) clearBtn.addEventListener('click', clear);

    var openBtn = document.getElementById('open_request_filter');
    if (openBtn) openBtn.addEventListener('click', function () { modal.show(); });

    var removeBtn = document.getElementById('request_filter_remove');
    if (removeBtn) removeBtn.addEventListener('click', clear);

    if (quickClearBtn) quickClearBtn.addEventListener('click', clear);
})();
