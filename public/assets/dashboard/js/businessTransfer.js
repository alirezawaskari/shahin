/*!
 * businessTransfer.js — «انتقال به کسب و کار»
 * -----------------------------------------------------------------------------
 * همه‌جا درون‌خطی است و هیچ مودالی ندارد:
 *
 * ۱) فهرست‌ها: «کسب و کار» یکی از مقدارهای «ارجاع به» است. با انتخابش فهرست
 *    Select2 کسب و کار مقصد ظاهر می‌شود و همان دکمه‌ی «انتقال» نوار، رکوردهای
 *    تیک‌خورده را منتقل می‌کند.
 *
 * ۲) صفحه‌ی ویرایش/جزئیات: یک Select2 درون‌خطی «انتقال به کسب و کار» کنار
 *    دکمه‌ی «انتقال».
 *
 * هر ظرفی با data-business-transfer-scope یک «دامنه»ی انتقال است. شناسه‌ی
 * رکوردها در فهرست‌ها از چک‌باکس‌های انتخاب‌شده (data-record-selector) و در
 * صفحه‌ی ویرایش از data-record-ids خوانده می‌شود. انتقال با یک فرم POST موقت
 * به businessTransfer.store انجام می‌شود؛ مجوز و اعتبارسنجی سمت سرور است.
 */
(function ($) {
    'use strict';

    var TRANSFER_URL = window.BUSINESS_TRANSFER_URL || '/dashboard/business-transfer';

    function parseBusinessIds(value) {
        if (Array.isArray(value)) {
            return value.map(Number).filter(Boolean);
        }
        if (typeof value === 'number') {
            return value ? [value] : [];
        }
        return String(value || '')
            .replace(/[\[\]"']/g, '')
            .split(',')
            .map(function (id) { return Number(String(id).trim()); })
            .filter(Boolean);
    }

    function unique(ids) {
        var seen = {};
        var out = [];

        ids.forEach(function (id) {
            if (id && !seen[id]) {
                seen[id] = true;
                out.push(id);
            }
        });

        return out;
    }

    function notify(message) {
        if (window.swal) {
            swal(message, { icon: 'warning', button: 'باشه' });
            return;
        }
        if (window.toastr) {
            toastr.error(message);
            return;
        }
        window.alert(message);
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    /** فرم موقت انتقال را می‌سازد و ارسال می‌کند (سرور با پیام، به همین صفحه برمی‌گردد) */
    function postTransfer(entity, ids, destinationId) {
        var $form = $('<form>', { method: 'post', action: TRANSFER_URL })
            .append($('<input>', { type: 'hidden', name: '_token', value: csrfToken() }))
            .append($('<input>', { type: 'hidden', name: '_business_transfer', value: '1' }))
            .append($('<input>', { type: 'hidden', name: 'entity', value: entity }))
            .append($('<input>', { type: 'hidden', name: 'destination_business_id', value: destinationId }));

        ids.forEach(function (id) {
            $form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
        });

        $form.appendTo(document.body).trigger('submit');
    }

    function scopeOf(el) {
        return $(el).closest('[data-business-transfer-scope]');
    }

    function destinationOf($scope) {
        return $scope.find('[data-business-transfer-destination]').first();
    }

    /** شناسه‌ی رکوردها: در صفحه‌ی ویرایش ثابت، در فهرست از چک‌باکس‌های انتخاب‌شده */
    function recordIdsOf($scope) {
        var fixed = parseBusinessIds($scope.attr('data-record-ids'));

        if (fixed.length) {
            return fixed;
        }

        var selector = $scope.attr('data-record-selector') || '.business-transfer-record';

        return unique($(selector + ':checked').map(function () {
            return Number(this.value);
        }).get());
    }

    /** کسب و کار فعلیِ رکوردهای انتخاب‌شده نباید مقصد باشد */
    function sourceBusinessIdsOf($scope) {
        var selector = $scope.attr('data-record-selector');

        if (!selector) {
            return parseBusinessIds($scope.attr('data-current-business-ids'));
        }

        var ids = [];

        $(selector + ':checked').each(function () {
            ids = ids.concat(parseBusinessIds($(this).attr('data-current-business-ids')));
        });

        return unique(ids);
    }

    /**
     * گزینه‌های کسب و کارِ فعلیِ رکوردهای انتخاب‌شده را غیرفعال می‌کند.
     * در صفحه‌ی ویرایش، کسب و کار فعلی از همان ابتدا در Blade غیرفعال شده
     * است و فقط دامنه‌هایی که رکوردشان پویا تعیین می‌شود اینجا کار دارند.
     */
    function syncDestination($scope) {
        var $destination = destinationOf($scope);

        if (!$destination.length ||
            (!$scope.attr('data-record-selector') && $scope.attr('data-current-business-ids') === undefined)) {
            return;
        }

        var excluded = {};
        sourceBusinessIdsOf($scope).forEach(function (id) { excluded[id] = true; });

        var changed = false;

        $destination.find('option').each(function () {
            var value = Number(this.value);
            var shouldDisable = value > 0 && excluded[value] === true;

            if (this.disabled !== shouldDisable) {
                this.disabled = shouldDisable;
                changed = true;
            }
        });

        if (!changed) {
            return;
        }

        if ($destination.find('option:selected').prop('disabled')) {
            $destination.val('');
        }

        if (window.AdminSelect2) {
            window.AdminSelect2.refresh($destination);
        }
    }

    function typeSelectOf($scope) {
        return $scope.find('[data-business-transfer-type-select]').first();
    }

    /** آیا در این دامنه «کسب و کار» به عنوان مقصدِ ارجاع انتخاب شده است؟ */
    function businessSelected($scope) {
        var $type = typeSelectOf($scope);

        return $type.length ? $type.val() === 'business' : true;
    }

    function submitScope($scope) {
        var $destination = destinationOf($scope);

        if (!$destination.length) {
            return false;
        }

        var ids = recordIdsOf($scope);

        if (!ids.length) {
            notify('حداقل یک رکورد را برای انتقال انتخاب کنید.');
            return true;
        }

        syncDestination($scope);

        var destinationId = Number($destination.val());

        if (!destinationId) {
            notify('کسب و کار مقصد را انتخاب کنید.');
            return true;
        }

        if ($destination.find('option:selected').prop('disabled')) {
            notify('کسب و کار فعلی رکوردهای انتخاب‌شده را نمی‌توان به عنوان مقصد انتخاب کرد.');
            return true;
        }

        var title = $.trim($destination.find('option:selected').text());

        if (window.confirm('آیا از انتقال ' + ids.length + ' رکورد به کسب و کار «' + title + '» مطمئن هستید؟')) {
            postTransfer($scope.attr('data-transfer-entity'), ids, destinationId);
        }

        return true;
    }

    // «ارجاع به» → کسب و کار: فهرست مقصد ظاهر می‌شود
    $(document).on('change', '[data-business-transfer-type-select]', function () {
        var $scope = scopeOf(this);
        var isBusiness = this.value === 'business';

        $scope.find('[data-business-transfer-box]')
            .toggleClass('d-none', !isBusiness)
            .css('display', isBusiness ? 'block' : 'none');

        if (isBusiness) {
            syncDestination($scope);
        }
    });

    $(document).on('click', '[data-business-transfer-submit]', function (event) {
        event.preventDefault();
        submitScope(scopeOf(this));
    });

    $(document).on('change', '.business-transfer-select-all', function () {
        var table = $(this).closest('table');
        table.find('.business-transfer-record').prop('checked', this.checked).trigger('change');
    });

    $(document).on('change', '.business-transfer-record', function () {
        var table = $(this).closest('table');
        var records = table.find('.business-transfer-record');
        var checked = records.filter(':checked');

        table.find('.business-transfer-select-all').prop(
            'checked',
            records.length > 0 && records.length === checked.length
        );

        $('[data-business-transfer-scope][data-record-selector]').each(function () {
            syncDestination($(this));
        });
    });

    // -------------------------------------------------------------------------
    // فیلد «کسب و کار» در فرم ویرایش
    //
    // مثل هر فیلد دیگری با دکمه‌ی ثبتِ خود فرم ذخیره می‌شود؛ فقط چون تغییرش
    // رکورد را جابه‌جا می‌کند، پیش از ارسال یک بار تایید گرفته می‌شود.
    // -------------------------------------------------------------------------

    function confirmFieldChange($container) {
        var confirmed = true;

        $container.find('[data-business-transfer-field]').each(function () {
            if (!confirmed) {
                return;
            }

            var $field = $(this);
            var current = String($field.attr('data-current-business-id') || '');
            var chosen = String($field.val() || '');

            if (chosen === current || chosen === '') {
                return;
            }

            var title = $.trim($field.find('option:selected').text());
            confirmed = window.confirm('آیا از انتقال این رکورد به کسب و کار «' + title + '» مطمئن هستید؟');
        });

        return confirmed;
    }

    $(document).on('submit', 'form', function (event) {
        if (!confirmFieldChange($(this))) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });

    // -------------------------------------------------------------------------
    // API برای اسکریپت‌های صفحه
    //
    // نوار انتقالِ فهرست پرونده‌ها و کالسنتر یک دکمه‌ی «انتقال» مشترک دارد؛
    // هندلر خودشان اول این را صدا می‌زند و اگر مقصدِ ارجاع «کسب و کار» باشد،
    // انتقال اینجا انجام می‌شود و هندلر صفحه ادامه نمی‌دهد.
    // -------------------------------------------------------------------------
    window.BusinessTransfer = {
        /** @return {boolean} true اگر انتقال به کسب و کار مدیریت شد */
        handle: function (trigger) {
            var $scope = scopeOf(trigger);

            if (!$scope.length || !businessSelected($scope)) {
                return false;
            }

            return submitScope($scope);
        },
        syncDestination: function (scope) {
            syncDestination($(scope));
        },
        /** @return {boolean} false یعنی کاربر انتقال را تایید نکرد و نباید ذخیره شود */
        confirmFieldChange: function (container) {
            return confirmFieldChange($(container));
        }
    };
})(jQuery);
