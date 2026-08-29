(function ($) {
    'use strict';

    var modalElement = document.getElementById('business-transfer-modal');
    if (!modalElement) {
        return;
    }

    var $modal = $(modalElement);
    var $form = $('#business-transfer-form');
    var $destination = $('#business-transfer-destination');
    var $submit = $('#business-transfer-submit');

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

    function showModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        } else {
            $modal.modal('show');
        }
    }

    function showSelectionError(message) {
        if (window.toastr) {
            toastr.error(message);
            return;
        }
        if (window.swal) {
            swal(message, { icon: 'warning', button: 'باشه' });
            return;
        }
        window.alert(message);
    }

    function initialiseDestination(sourceBusinessIds) {
        var excluded = {};
        sourceBusinessIds.forEach(function (id) { excluded[Number(id)] = true; });

        $destination.find('option').each(function () {
            var value = Number(this.value);
            this.disabled = value > 0 && excluded[value] === true;
        });
        $destination.val('');

        if (window.AdminSelect2) {
            if ($destination.hasClass('select2-hidden-accessible')) {
                window.AdminSelect2.refresh($destination);
            } else {
                window.AdminSelect2.enhance($destination);
            }
        } else {
            $destination.select2({
                width: '100%',
                dir: 'rtl',
                dropdownParent: $modal,
                placeholder: 'انتخاب کسب و کار فعال'
            });
        }

        var hasDestination = $destination.find('option[value!=""]:not(:disabled)').length > 0;
        $('#business-transfer-no-destination').toggleClass('d-none', hasDestination);
        $submit.prop('disabled', !hasDestination);
    }

    function openTransfer(entity, ids, sourceBusinessIds) {
        if (!ids.length) {
            showSelectionError('حداقل یک رکورد را برای انتقال انتخاب کنید.');
            return;
        }

        $('#business-transfer-entity').val(entity);
        var $inputs = $('#business-transfer-record-inputs').empty();
        ids.forEach(function (id) {
            $('<input>', { type: 'hidden', name: 'ids[]', value: id }).appendTo($inputs);
        });

        $('#business-transfer-selection-summary').text(
            ids.length === 1
                ? 'یک رکورد برای انتقال انتخاب شده است.'
                : ids.length + ' رکورد برای انتقال انتخاب شده است.'
        );
        initialiseDestination(Array.from(new Set(sourceBusinessIds)));
        showModal();
    }

    $(document).on('click', '[data-business-transfer-single]', function () {
        var $trigger = $(this);
        var open = function () {
            var recordIds = parseBusinessIds($trigger.attr('data-record-ids'));
            if (!recordIds.length) {
                recordIds = [Number($trigger.attr('data-record-id'))];
            }
            openTransfer(
                $trigger.attr('data-transfer-entity'),
                recordIds,
                parseBusinessIds($trigger.attr('data-current-business-ids'))
            );
        };
        var $containingModal = $trigger.closest('.modal');

        if ($containingModal.length && $containingModal[0] !== modalElement) {
            $containingModal.one('hidden.bs.modal', open);
            if ($.fn.modal) {
                $containingModal.modal('hide');
            } else if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance($containingModal[0]).hide();
            }
            return;
        }

        open();
    });

    $(document).on('click', '[data-business-transfer-bulk]', function () {
        var $trigger = $(this);
        var selector = $trigger.attr('data-record-selector') || '.business-transfer-record';
        var $records = $(selector + ':checked');
        var ids = [];
        var sourceBusinessIds = [];

        $records.each(function () {
            ids.push(Number(this.value));
            sourceBusinessIds = sourceBusinessIds.concat(
                parseBusinessIds($(this).attr('data-current-business-ids'))
            );
        });

        openTransfer($trigger.data('transfer-entity'), ids, sourceBusinessIds);
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
    });

    $form.on('submit', function (event) {
        var destinationTitle = $destination.find('option:selected').text();
        var count = $('#business-transfer-record-inputs input').length;
        var message = 'آیا از انتقال ' + count + ' رکورد به کسب و کار «' + destinationTitle + '» مطمئن هستید؟';

        if (!$destination.val() || !window.confirm(message)) {
            event.preventDefault();
            return;
        }

        $submit.prop('disabled', true).text('در حال انتقال...');
    });
})(jQuery);
