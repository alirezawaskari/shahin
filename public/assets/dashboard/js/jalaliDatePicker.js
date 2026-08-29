/**
 * یک مدل تقویم واحد برای پنل مدیریت.
 *
 * The panel used to ship two different pickers:
 *
 *   1. the Vue `<date-picker>` component (vue-persian-datetime-picker), registered inside
 *      dashboard.js and used by the callcenter request-list filters — the reference calendar;
 *   2. pwt-datepicker, bound by jQuery to marker classes such as `.date`, `.expire_date`,
 *      `.check-date` … on most of the admin forms.
 *
 * This file removes the second one. Everything that pwt-datepicker used to initialise is
 * now backed by the very same `<date-picker>` component, so the panel has one calendar.
 *
 * How it stays compatible
 * -----------------------
 * The original `<input>` is kept in the DOM as a hidden value holder, and the picker is
 * mounted next to it. That keeps the element's `name`, `id` and `value` exactly where they
 * were, so page scripts that read (`$('#check-date').val()`) or write
 * (`$('#edit-check-date').val(...)`) the field keep working untouched — a write is
 * intercepted and pushed into the picker so the two never drift apart.
 *
 * The submitted string is token-for-token what pwt-datepicker used to write (a Jalali date
 * in the same layout), so no server-side date parsing had to change. pwt-datepicker wrote
 * Persian digits and this writes Latin ones; every consumer goes through
 * Helper::vertaToMiladi(), which normalises digits first.
 *
 * pwt-datepicker's `onSelect` callback has no equivalent here — pages that used it should
 * listen for `change` on the field instead. The unix timestamp `onSelect` used to receive
 * is published on the element as `data-jdp-unix` (milliseconds, same as before).
 */
(function (window, document) {
    'use strict';

    /**
     * Legacy marker class -> picker configuration.
     *
     * `format` is what gets submitted. Each one is the Jalali spelling of the `format`
     * the matching pwt-datepicker call used to pass, so the value shape is unchanged.
     */
    var LEGACY = {
        'date':            { type: 'datetime', format: 'jYYYY/jMM/jDD H:m:s' },
        'check-date':      { type: 'date',     format: 'jYYYY/jMM/jDD' },
        'promissory-date': { type: 'date',     format: 'jYYYY/jMM/jDD' },
        'expire_date':     { type: 'datetime', format: 'jYYYY-jMM-jDD HH:mm:ss' },
        'start_at':        { type: 'datetime', format: 'jYYYY-jMM-jDD HH:mm:ss' },
        'end_at':          { type: 'datetime', format: 'jYYYY-jMM-jDD HH:mm:ss' },
        'start_date':      { type: 'date',     format: 'jYYYY-jMM-jDD' },
        'end_date':        { type: 'date',     format: 'jYYYY-jMM-jDD' },
        'start_time':      { type: 'time',     format: 'HH:mm:ss' },
        'end_time':        { type: 'time',     format: 'HH:mm:ss' }
    };

    var LEGACY_CLASSES = Object.keys(LEGACY);

    var SELECTOR = ['input[data-jdp]'].concat(
        LEGACY_CLASSES.map(function (cls) { return 'input.' + cls; })
    ).join(',');

    var PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    var ARABIC_DIGITS  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    /** The native value accessor, used to bypass our own interceptor. */
    var NATIVE_VALUE = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');

    function latin(value) {
        var out = String(value);
        for (var i = 0; i < 10; i++) {
            out = out.split(PERSIAN_DIGITS[i]).join(i).split(ARABIC_DIGITS[i]).join(i);
        }
        return out;
    }

    /**
     * pwt-datepicker read the value already sitting in the input as a Gregorian date
     * (`initialValueType: 'gregorian'`) and showed the Jalali equivalent. Values come out
     * of the database that way, so do the same conversion before handing it to the picker.
     *
     * A value that is already Jalali (an edit modal populated from a rendered date, or a
     * form redisplayed after a validation error) is passed straight through.
     */
    function toPickerValue(raw, format) {
        var moment = window.jMoment;

        if (raw === null || raw === undefined) return '';

        var value = latin(raw).trim();

        // Several date columns are NOT NULL, so "never filled in" reaches the form as a
        // MySQL zero date. Show it as the empty field it really is.
        if (/^0{4}[-/]0{1,2}[-/]0{1,2}/.test(value)) return '';

        // Time-only fields carry no calendar, and without moment-jalaali there is nothing
        // sensible to convert with.
        if (!value || !moment || format.indexOf('j') === -1) return value;

        // A few fields (group SMS `send_at`) are stored as a unix timestamp.
        if (/^\d{10}$|^\d{13}$/.test(value)) {
            return moment(value.length === 10 ? Number(value) * 1000 : Number(value)).format(format);
        }

        var gregorian = moment(value, [
            'YYYY-MM-DD HH:mm:ss', 'YYYY-MM-DD HH:mm', 'YYYY-MM-DD',
            'YYYY/MM/DD HH:mm:ss', 'YYYY/MM/DD HH:mm', 'YYYY/MM/DD'
        ], true);

        if (!gregorian.isValid()) {
            var parsed = new Date(value);
            gregorian = isNaN(parsed.getTime()) ? null : moment(parsed);
        }

        // A Jalali year parsed as Gregorian lands centuries in the past — that is the tell
        // that the value was Jalali to begin with.
        if (!gregorian || !gregorian.isValid() || gregorian.year() < 1800) return value;

        return gregorian.format(format);
    }

    /** `data-jdp-min="today|tomorrow|<date>"` -> a bound in the picker's own format. */
    function resolveMin(token, format) {
        if (!token) return '';

        var moment = window.jMoment;
        if (!moment) return '';

        if (token === 'today')    return moment().format(format);
        if (token === 'tomorrow') return moment().add(1, 'day').format(format);

        return toPickerValue(token, format);
    }

    function resolveConfig(input) {
        var preset = null;

        for (var i = 0; i < LEGACY_CLASSES.length; i++) {
            if (input.classList.contains(LEGACY_CLASSES[i])) {
                preset = LEGACY[LEGACY_CLASSES[i]];
                break;
            }
        }

        preset = preset || LEGACY.date;

        var data = input.dataset || {};

        var format = data.jdpFormat || preset.format;

        // Whatever the input was styled with, minus the marker classes that brought us here.
        var inputClass = (input.className || '')
            .split(/\s+/)
            .filter(function (cls) { return cls && LEGACY_CLASSES.indexOf(cls) === -1; })
            .join(' ');

        return {
            type: data.jdpType || preset.type,
            format: format,
            min: resolveMin(data.jdpMin, format),
            placeholder: input.getAttribute('placeholder') || '',
            inputClass: inputClass || 'form-control',
            disabled: input.disabled || input.readOnly
        };
    }

    /** Write into the value holder without bouncing back through the interceptor. */
    function publish(input, value) {
        var config = input.jdpConfig;

        NATIVE_VALUE.set.call(input, value);
        input.setAttribute('value', value);

        var moment = window.jMoment;
        var unix = '';

        if (moment && value && config.format.indexOf('j') !== -1) {
            var parsed = moment(latin(value), config.format);
            if (parsed.isValid()) unix = String(parsed.valueOf());
        }

        input.dataset.jdpUnix = unix;

        if (window.jQuery) {
            window.jQuery(input).trigger('change');
        } else {
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /**
     * Page scripts still set these fields with `$('#x').val(...)`. Route those writes into
     * the picker so what is displayed and what will be submitted stay the same value.
     */
    function interceptWrites(input, vm) {
        Object.defineProperty(input, 'value', {
            configurable: true,
            get: function () {
                return NATIVE_VALUE.get.call(this);
            },
            set: function (value) {
                var normalised = toPickerValue(value, this.jdpConfig.format);
                NATIVE_VALUE.set.call(this, normalised);
                vm.current = normalised;
            }
        });
    }

    function mount(input) {
        if (input.jdpMounted || !window.Vue) return;

        input.jdpMounted = true;

        var config = resolveConfig(input);
        input.jdpConfig = config;

        // A hidden input is exempt from HTML5 validation, so carry `required` over to the
        // submit guard below rather than dropping the constraint silently.
        if (input.hasAttribute('required')) {
            input.removeAttribute('required');
            input.setAttribute('data-jdp-required', '1');
        }

        var initial = toPickerValue(input.value, config.format);

        NATIVE_VALUE.set.call(input, initial);
        input.type = 'hidden';

        var holder = document.createElement('div');
        holder.className = 'jdp-wrapper';
        input.parentNode.insertBefore(holder, input);

        var vm = new window.Vue({
            el: holder,
            data: { current: initial },
            render: function (h) {
                var vm = this;

                return h('date-picker', {
                    props: {
                        value: vm.current,
                        format: config.format,
                        type: config.type,
                        min: config.min,
                        locale: 'fa',
                        placeholder: config.placeholder,
                        inputClass: config.inputClass,
                        disabled: config.disabled,
                        clearable: true
                    },
                    on: {
                        input: function (value) {
                            vm.current = value || '';
                            publish(input, vm.current);
                        }
                    }
                });
            }
        });

        interceptWrites(input, vm);
    }

    /**
     * Mount every legacy date field inside `root`. Safe to call repeatedly — fields that
     * are already mounted are skipped — so scripts that inject rows can just call it again.
     */
    function initJalaliDatePickers(root) {
        var scope = root || document;

        if (scope.nodeType === 1 && scope.matches && scope.matches(SELECTOR)) mount(scope);

        var fields = scope.querySelectorAll ? scope.querySelectorAll(SELECTOR) : [];

        for (var i = 0; i < fields.length; i++) mount(fields[i]);
    }

    window.initJalaliDatePickers = initJalaliDatePickers;

    // Rows added after load (payment lines, installment lines, uploaded-file blocks …) are
    // built by jQuery, so nothing would compile them. Pick them up as they arrive.
    function watch() {
        if (!window.MutationObserver) return;

        var pending = false;

        new window.MutationObserver(function () {
            if (pending) return;
            pending = true;
            window.setTimeout(function () {
                pending = false;
                initJalaliDatePickers(document);
            }, 0);
        }).observe(document.body, { childList: true, subtree: true });
    }

    function ready() {
        initJalaliDatePickers(document);
        watch();

        if (!window.jQuery) return;

        window.jQuery(document).on('submit', 'form', function (event) {
            var missing = window.jQuery(this)
                .find('[data-jdp-required]')
                .filter(function () { return !this.value; });

            if (!missing.length) return;

            event.preventDefault();
            window.alert('لطفا تاریخ را انتخاب کنید.');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})(window, document);
