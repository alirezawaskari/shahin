/*!
 * select2-init.js — راه‌اندازی سراسری Select2 در پنل مدیریت
 * -----------------------------------------------------------------------------
 * هر <select> ای که فهرست بلندی داشته باشد به صورت خودکار به Select2 تبدیل
 * می‌شود تا قابلیت جستجو داشته باشد. نیازی به افزودن کلاس به تک‌تک فرم‌ها نیست.
 *
 * خاموش کردن روی یک المان:        <select data-no-select2>
 * روشن کردن اجباری (فهرست کوتاه): <select data-select2-enable>
 * آستانه‌ی اختصاصی:               <select data-select2-min="5">
 * غیرفعال کردن کادر جستجو:        <select data-select2-search="false">
 * نمایش دکمه‌ی حذف انتخاب:         <select data-allow-clear="true">
 * متن راهنما:                     <select data-placeholder="...">
 * متن کمکی برای جستجو روی گزینه:  <option data-search="09121234567">
 *
 * هشدار: صفت «data-select2» را روی هیچ المانی نگذارید. Select2 برای پیدا کردن
 * نمونه‌ی خودش از GetData(el, 'select2') استفاده می‌کند که در نبودِ نمونه به
 * jQuery.data('select2') برمی‌گردد؛ آن صفت مقدار '' برمی‌گرداند و سازنده‌ی
 * Select2 روی آن destroy() صدا می‌زند و استثنا می‌دهد.
 *
 * این فایل باید بعد از dashboard.js (که jQuery و Select2 را همراه دارد) و پیش
 * از اسکریپت‌های اختصاصی هر صفحه بارگذاری شود.
 */
(function ($) {
    'use strict';

    if (!$ || !$.fn || !$.fn.select2) {
        return;
    }

    // -------------------------------------------------------------------------
    // تنظیمات
    // -------------------------------------------------------------------------
    var config = {
        // حداقل تعداد گزینه تا یک select تک‌انتخابی «فهرست بلند» شمرده شود
        minOptions: 10,
        // حداقل تعداد گزینه برای selectهای چندانتخابی
        minOptionsMultiple: 2,
        // فهرست‌های بزرگ‌تر از این مقدار با آداپتور محدودکننده رندر می‌شوند
        virtualizeAbove: 300,
        // حداکثر تعداد نتیجه‌ای که یکجا نمایش داده می‌شود
        resultLimit: 100,
        defaultPlaceholder: 'انتخاب کنید'
    };

    // یک صفحه می‌تواند پیش از بارگذاری این فایل (مثلاً در @section('css'))
    // تنظیمات را بازنویسی کند:  <script>window.SELECT2_CONFIG = {minOptions: 5}</script>
    if (window.SELECT2_CONFIG) {
        $.extend(config, window.SELECT2_CONFIG);
    }

    // کلاس‌هایی که در پروژه از قبل به معنای «این را Select2 کن» به کار رفته‌اند
    var OPT_IN_SELECTOR = '.select2, .selectMulti, .selectSearch, .js-example-basic-single, [data-select2-enable]';

    // جاهایی که نباید دست بزنیم
    var OPT_OUT_SELECTOR = '[data-no-select2], .no-select2';
    var OPT_OUT_ANCESTOR = '[data-no-select2], .no-select2, .dataTables_length, .dt-length, .note-editor';

    // -------------------------------------------------------------------------
    // ماژول‌های داخلی Select2 (تشخیص نمونه‌ی واقعی + آداپتور فهرست‌های بزرگ)
    // -------------------------------------------------------------------------
    var amd = $.fn.select2.amd;
    var Utils = null;
    var LimitedSelectAdapter = null;

    if (amd && typeof amd.require === 'function') {
        try {
            Utils = amd.require('select2/utils');
        } catch (e) {
            Utils = null;
        }

        try {
            var SelectData = amd.require('select2/data/select');

            var LimitResults = function (decorated, $element, options) {
                decorated.call(this, $element, options);
            };

            LimitResults.prototype.query = function (decorated, params, callback) {
                var limit = this.options.get('resultLimit') || 0;

                decorated.call(this, params, function (data) {
                    if (limit > 0 && data && data.results && data.results.length > limit) {
                        var hidden = data.results.length - limit;

                        data.results = data.results.slice(0, limit);
                        data.results.push({
                            id: '__select2_more__',
                            text: hidden + ' مورد دیگر… برای یافتن گزینه‌ی موردنظر جستجو کنید',
                            disabled: true
                        });
                    }

                    callback(data);
                });
            };

            if (Utils && Utils.Decorate) {
                LimitedSelectAdapter = Utils.Decorate(SelectData, LimitResults);
            }
        } catch (e) {
            LimitedSelectAdapter = null;
        }
    }

    // -------------------------------------------------------------------------
    // نرمال‌سازی متن فارسی برای جستجو
    // ی/ي، ک/ك، ه/ة، ارقام فارسی و عربی، نیم‌فاصله و اعراب یکسان‌سازی می‌شوند
    // -------------------------------------------------------------------------
    var CHAR_MAP = {
        'ي': 'ی', // ي عربی      -> ی
        'ى': 'ی', // ى مقصوره    -> ی
        'ك': 'ک', // ك عربی      -> ک
        'ة': 'ه', // ة           -> ه
        'أ': 'ا', // أ           -> ا
        'إ': 'ا', // إ           -> ا
        'آ': 'ا', // آ           -> ا
        'ؤ': 'و', // ؤ           -> و
        'ئ': 'ی'  // ئ           -> ی
    };

    // نیم‌فاصله و علائم جهت، کشیده، و اعراب حذف می‌شوند
    var STRIP_RE = /[\u200B-\u200F\u061C\u0640\u064B-\u0655\u0670\uFEFF]/g;

    function normalize(text) {
        if (text === null || text === undefined) {
            return '';
        }

        var stripped = String(text).replace(STRIP_RE, '');
        var result = '';

        for (var i = 0; i < stripped.length; i++) {
            var ch = stripped.charAt(i);
            var code = stripped.charCodeAt(i);

            if (code >= 0x0660 && code <= 0x0669) {          // ارقام عربی ٠-٩
                result += String.fromCharCode(code - 0x0660 + 48);
            } else if (code >= 0x06F0 && code <= 0x06F9) {   // ارقام فارسی ۰-۹
                result += String.fromCharCode(code - 0x06F0 + 48);
            } else if (CHAR_MAP[ch]) {
                result += CHAR_MAP[ch];
            } else {
                result += ch;
            }
        }

        return result.toLowerCase().replace(/\s+/g, ' ').replace(/^\s|\s$/g, '');
    }

    function haystackFor(data) {
        var extra = '';

        if (data.element && data.element.getAttribute) {
            extra = data.element.getAttribute('data-search') || '';
        }

        return normalize(data.text) + (extra ? ' ' + normalize(extra) : '');
    }

    /**
     * جستجوی مداراجو: اگر عیناً پیدا نشد، بار دوم فاصله‌ها را هم نادیده
     * می‌گیرد تا «می شود»، «می‌شود» و «میشود» همه یکسان رفتار کنند.
     */
    function matchesTerm(haystack, term) {
        if (haystack.indexOf(term) > -1) {
            return true;
        }

        var tight = term.replace(/ /g, '');

        return tight !== '' && haystack.replace(/ /g, '').indexOf(tight) > -1;
    }

    function matcher(params, data) {
        var term = normalize(params && params.term);

        if (term === '') {
            return data;
        }

        if (typeof data.text === 'undefined') {
            return null;
        }

        if (data.children && data.children.length) {
            var group = $.extend(true, {}, data);

            for (var i = group.children.length - 1; i >= 0; i--) {
                if (!matchesTerm(haystackFor(group.children[i]), term)) {
                    group.children.splice(i, 1);
                }
            }

            return group.children.length ? group : null;
        }

        return matchesTerm(haystackFor(data), term) ? data : null;
    }

    // -------------------------------------------------------------------------
    // ترجمه‌ی فارسی پیام‌های Select2
    // -------------------------------------------------------------------------
    var language = {
        errorLoading: function () {
            return 'دریافت نتایج ممکن نشد.';
        },
        inputTooLong: function (args) {
            return 'لطفاً ' + (args.input.length - args.maximum) + ' نویسه را حذف کنید';
        },
        inputTooShort: function (args) {
            return 'لطفاً ' + (args.minimum - args.input.length) + ' نویسه یا بیشتر وارد کنید';
        },
        loadingMore: function () {
            return 'در حال دریافت نتایج بیشتر…';
        },
        maximumSelected: function (args) {
            return 'تنها می‌توانید ' + args.maximum + ' مورد را انتخاب کنید';
        },
        noResults: function () {
            return 'موردی یافت نشد.';
        },
        searching: function () {
            return 'در حال جستجو…';
        },
        removeAllItems: function () {
            return 'حذف همه‌ی موارد';
        }
    };

    // -------------------------------------------------------------------------
    // کمکی‌ها
    // -------------------------------------------------------------------------
    function isSmallScreen() {
        return window.matchMedia
            ? window.matchMedia('(max-width: 767.98px)').matches
            : $(window).width() < 768;
    }

    function optionCount(el) {
        return el && el.options ? el.options.length : 0;
    }

    function looksEnhanced(el) {
        return el.className.indexOf('select2-hidden-accessible') > -1;
    }

    /**
     * نمونه‌ی واقعیِ Select2 مربوط به همین المان را برمی‌گرداند.
     *
     * کلون‌های DOM صفت data-select2-id را با خود می‌برند و GetData نمونه‌ی
     * المانِ اصلی را برمی‌گرداند؛ مقایسه‌ی $element این حالت را رد می‌کند.
     * بررسی کلاس در ابتدا لازم است چون GetData روی هر المانی که صدا زده شود
     * یک data-select2-id به آن می‌چسباند.
     */
    function instanceOf(el) {
        if (!looksEnhanced(el)) {
            return null;
        }

        var inst = null;

        if (Utils && Utils.GetData) {
            try {
                inst = Utils.GetData(el, 'select2');
            } catch (e) {
                inst = null;
            }
        }

        // GetData در نبودِ نمونه به jQuery.data برمی‌گردد و می‌تواند مقداری
        // که نمونه‌ی Select2 نیست برگرداند، پس نوع را هم بررسی می‌کنیم.
        if (!inst || typeof inst !== 'object' || !inst.$element) {
            return null;
        }

        return inst.$element[0] === el ? inst : null;
    }

    /** پاک‌سازی بقایای Select2 روی یک <select> کلون‌شده */
    function cleanupClone(el) {
        var $el = $(el);

        $el.nextAll('.select2-container').first().remove();
        $el.removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .removeAttr('aria-hidden')
            .removeAttr('tabindex');
        $el.find('option, optgroup').removeAttr('data-select2-id');
    }

    // -------------------------------------------------------------------------
    // انتقال ظاهرِ <select> به کنترلِ Select2
    //
    // Select2 عنصر اصلی را با clip پنهان می‌کند و یک <span> کنار آن می‌سازد.
    // در نتیجه کلاس‌های فاصله (mb-3، my-2، …) و همچنین hide()/show() که روی
    // خودِ <select> اعمال شده‌اند دیگر اثری بر ظاهر صفحه ندارند. این تابع
    // آن‌ها را روی span بازتاب می‌دهد تا صفحات قدیمی همان‌طور که نوشته شده‌اند
    // کار کنند.
    //
    // کلاس‌های شبکه‌بندی (col-*) عمداً منتقل نمی‌شوند: Select2 هنگام باز شدن،
    // کلاس‌های همین span را روی پوششِ dropdown کپی می‌کند و max-width آن‌ها
    // فهرست بازشو را له می‌کند.
    // -------------------------------------------------------------------------
    var MIRROR_CLASS_RE = new RegExp(
        '^(' +
        'm[trblxy]?-\\d|m[trblxy]?-auto|' +   // فاصله‌ی بیرونی
        'd-none|invisible|' +                 // پنهان‌سازی
        'is-invalid|is-valid|' +              // اعتبارسنجی
        'form-control-(sm|lg)|form-select-(sm|lg)' +
        ')$'
    );

    function syncPresentation(el) {
        var inst = instanceOf(el);

        if (!inst || !inst.$container || !inst.$container.length) {
            return;
        }

        var $container = inst.$container;
        var classes = (el.getAttribute('class') || '').split(/\s+/);
        var wanted = [];

        for (var i = 0; i < classes.length; i++) {
            var name = classes[i];

            if (name && name.indexOf('select2') !== 0 && MIRROR_CLASS_RE.test(name)) {
                wanted.push(name);
            }
        }

        var previous = $container.data('s2Mirrored') || [];

        for (var j = 0; j < previous.length; j++) {
            if ($.inArray(previous[j], wanted) === -1) {
                $container.removeClass(previous[j]);
            }
        }

        if (wanted.length) {
            $container.addClass(wanted.join(' '));
        }

        $container.data('s2Mirrored', wanted);

        // hide() / show() / css('display', …) روی <select>
        $container[0].style.display = (el.style.display === 'none') ? 'none' : '';
    }

    function boolAttr($el, name, fallback) {
        var raw = $el.attr(name);

        if (raw === undefined) {
            return fallback;
        }

        return !(raw === 'false' || raw === '0');
    }

    /**
     * متن راهنما: اگر اولین گزینه مقدار خالی دارد، متن خودش گویاتر از یک
     * «انتخاب کنید» عمومی است (مثلاً «انتخاب استان»).
     */
    function placeholderFor($el) {
        var explicit = $el.attr('data-placeholder');

        if (explicit) {
            return explicit;
        }

        var first = $el[0].options ? $el[0].options[0] : null;

        if (first && first.getAttribute('value') === '' && $.trim(first.text) !== '') {
            return $.trim(first.text);
        }

        return config.defaultPlaceholder;
    }

    // -------------------------------------------------------------------------
    // تنظیمات پایه‌ی هر المان
    // -------------------------------------------------------------------------
    function baseOptions($el) {
        var el = $el[0];
        var multiple = !!el.multiple;

        var options = {
            width: '100%',
            dir: 'rtl',
            language: language,
            matcher: matcher,
            placeholder: placeholderFor($el),
            allowClear: boolAttr($el, 'data-allow-clear', false),
            closeOnSelect: !multiple
        };

        if (!boolAttr($el, 'data-select2-search', true)) {
            options.minimumResultsForSearch = Infinity;
        }

        // فهرست‌های خیلی بزرگ: فقط بخشی از نتایج رندر می‌شود تا باز شدن کند نشود
        if (LimitedSelectAdapter && optionCount(el) > config.virtualizeAbove) {
            options.dataAdapter = LimitedSelectAdapter;
            options.resultLimit = config.resultLimit;
        }

        // داخل مودال، dropdown باید درون خود مودال ساخته شود؛ در غیر این صورت
        // مهار فوکوسِ بوت‌استرپ اجازه‌ی تایپ در کادر جستجو را نمی‌دهد.
        var $host = $el.closest('.modal, .offcanvas');

        if ($host.length) {
            options.dropdownParent = $host.first();
        }

        return options;
    }

    // -------------------------------------------------------------------------
    // «.select2()» را بی‌خطر می‌کنیم: فراخوانی دوباره به‌جای ساختن کنترلِ تکراری،
    // نمونه‌ی قبلی را از بین می‌برد و با تنظیماتِ ادغام‌شده از نو می‌سازد.
    // -------------------------------------------------------------------------
    var nativeSelect2 = $.fn.select2;

    function callNative($el, arg) {
        try {
            return nativeSelect2.call($el, arg);
        } catch (e) {
            return null;
        }
    }

    /**
     * Select2 نمونه‌ها را در یک cache سراسری با کلیدِ data-select2-id نگه
     * می‌دارد و اگر آن صفت نباشد، از id خودِ المان استفاده می‌کند. در صفحاتی
     * که یک partial دو بار include شده، دو <select> با id یکسان وجود دارد و
     * نمونه‌ها روی هم می‌افتند. با دادن یک کلید یکتا از پیش، این اتفاق
     * نمی‌افتد.
     */
    var uniqueKey = 0;

    function ensureUniqueKey(el) {
        if (!el.getAttribute('data-select2-id')) {
            el.setAttribute('data-select2-id', 'as2-' + (++uniqueKey));
        }
    }

    function initOne(el, userOptions) {
        var $el = $(el);

        if (instanceOf(el)) {
            callNative($el, 'destroy');
        }

        if (looksEnhanced(el)) {
            cleanupClone(el);
        }

        ensureUniqueKey(el);

        var options = $.extend({}, baseOptions($el), userOptions || {});

        // زبان ممکن است از سوی صفحه فقط تا حدی بازنویسی شود
        if (userOptions && $.isPlainObject(userOptions.language)) {
            options.language = $.extend({}, language, userOptions.language);
        }

        try {
            nativeSelect2.call($el, options);
            syncPresentation(el);
        } catch (e) {
            if (window.console && window.console.warn) {
                window.console.warn('select2-init: راه‌اندازی ناموفق بود', el, e);
            }
        }
    }

    $.fn.select2 = function (options) {
        if (options === undefined || $.isPlainObject(options)) {
            this.each(function () {
                if (this.tagName === 'SELECT') {
                    initOne(this, options);
                } else if (this.tagName === 'INPUT') {
                    // Select2 روی <input> هم کار می‌کند؛ دست‌نخورده رد می‌شود
                    nativeSelect2.call($(this), options || {});
                }

                // هر چیز دیگری نادیده گرفته می‌شود.
                //
                // مهم: span ای که خودِ Select2 می‌سازد کلاس «select2» دارد،
                // پس گزینشگر `$('.select2')` در custom.js هم <select>های
                // اصلی و هم آن spanها را برمی‌دارد. چون ما کنترل‌ها را پیش از
                // هندلرهای ready می‌سازیم، آن فراخوانی به spanها هم می‌رسید و
                // روی هرکدام یک Select2ِ دومِ بی‌گزینه می‌ساخت — همان کنترل
                // تکراری با «انتخاب کنید» و «No results found».
            });

            return this;
        }

        return nativeSelect2.apply(this, arguments);
    };

    $.extend($.fn.select2, nativeSelect2);

    // -------------------------------------------------------------------------
    // انتخاب خودکار المان‌های واجد شرایط
    // -------------------------------------------------------------------------
    function shouldEnhance(el) {
        if (instanceOf(el)) {
            return false;
        }

        var $el = $(el);

        // بررسی‌های ارزان اول، تا در صفحاتی با دهها select هزینه‌ای نداشته باشد
        if (!$el.is(OPT_IN_SELECTOR)) {
            var min = parseInt($el.attr('data-select2-min'), 10);

            if (isNaN(min)) {
                min = el.multiple ? config.minOptionsMultiple : config.minOptions;
            }

            if (optionCount(el) < min) {
                return false;
            }
        }

        // list-box چندخطی (size>1) رفتار متفاوتی دارد و باید بومی بماند
        var size = parseInt($el.attr('size'), 10);

        if (!isNaN(size) && size > 1) {
            return false;
        }

        return !($el.is(OPT_OUT_SELECTOR) || $el.closest(OPT_OUT_ANCESTOR).length);
    }

    function scan(root) {
        var $root = root ? $(root) : $(document.body);

        if (!$root.length) {
            return;
        }

        var $selects = $root.find('select');

        if ($root.is('select')) {
            $selects = $selects.add($root);
        }

        $selects.each(function () {
            if (shouldEnhance(this)) {
                initOne(this, null);
            }
        });
    }

    // -------------------------------------------------------------------------
    // همگام‌سازی با کدهای قدیمی که مستقیم DOM را دستکاری می‌کنند
    // -------------------------------------------------------------------------
    var scanQueued = false;

    function queueScan() {
        if (scanQueued) {
            return;
        }

        scanQueued = true;
        window.setTimeout(function () {
            scanQueued = false;
            scan();
        }, 60);
    }

    /** فقط نمایشِ کنترل را با مقدار/گزینه‌های فعلیِ <select> هماهنگ می‌کند */
    function refresh(el) {
        if (instanceOf(el)) {
            $(el).trigger('change.select2');
        }
    }

    var pendingRefresh = [];
    var refreshQueued = false;

    function queueRefresh(el) {
        if ($.inArray(el, pendingRefresh) === -1) {
            pendingRefresh.push(el);
        }

        if (refreshQueued) {
            return;
        }

        refreshQueued = true;
        window.setTimeout(function () {
            refreshQueued = false;

            var list = pendingRefresh;
            pendingRefresh = [];

            for (var i = 0; i < list.length; i++) {
                refresh(list[i]);
            }
        }, 0);
    }

    var pendingPresentation = [];
    var presentationQueued = false;

    function queuePresentation(el) {
        if ($.inArray(el, pendingPresentation) === -1) {
            pendingPresentation.push(el);
        }

        if (presentationQueued) {
            return;
        }

        presentationQueued = true;
        window.setTimeout(function () {
            presentationQueued = false;

            var list = pendingPresentation;
            pendingPresentation = [];

            for (var i = 0; i < list.length; i++) {
                syncPresentation(list[i]);
            }
        }, 0);
    }

    // ۱) افزوده/حذف شدن <option>، اضافه شدن قالب‌های جدید به صفحه، و
    //    تغییر کلاس یا style روی <select>هایی که Select2 شده‌اند
    function watchDom() {
        if (!window.MutationObserver || !document.body) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            var needScan = false;

            for (var i = 0; i < mutations.length; i++) {
                var m = mutations[i];

                if (m.type === 'attributes') {
                    if (m.target.tagName === 'SELECT' && looksEnhanced(m.target)) {
                        queuePresentation(m.target);
                    }

                    continue;
                }

                if (m.type !== 'childList') {
                    continue;
                }

                var target = m.target;
                var owner = null;

                if (target && target.tagName === 'SELECT') {
                    owner = target;
                } else if (target && target.tagName === 'OPTGROUP' &&
                    target.parentNode && target.parentNode.tagName === 'SELECT') {
                    owner = target.parentNode;
                }

                if (owner) {
                    if (instanceOf(owner)) {
                        queueRefresh(owner);
                    } else {
                        needScan = true; // شاید تازه به آستانه رسیده باشد
                    }

                    continue;
                }

                for (var j = 0; j < m.addedNodes.length; j++) {
                    var node = m.addedNodes[j];

                    if (node.nodeType !== 1 || node.className === undefined) {
                        continue;
                    }

                    // خودِ DOMِ ساخته‌شده توسط Select2 را نادیده بگیر
                    if (typeof node.className === 'string' &&
                        node.className.indexOf('select2-') === 0) {
                        continue;
                    }

                    if (node.tagName === 'SELECT' ||
                        (node.querySelector && node.querySelector('select'))) {
                        needScan = true;
                        break;
                    }
                }
            }

            if (needScan) {
                queueScan();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    // ۲) کدهایی مثل `$('#city').val(id)` که بدون trigger مقدار را عوض می‌کنند
    var nativeVal = $.fn.val;
    var syncingVal = false;

    $.fn.val = function () {
        var result = nativeVal.apply(this, arguments);

        if (arguments.length && !syncingVal) {
            var dirty = [];

            for (var i = 0; i < this.length; i++) {
                if (this[i].tagName === 'SELECT' && looksEnhanced(this[i])) {
                    dirty.push(this[i]);
                }
            }

            if (dirty.length) {
                syncingVal = true;

                try {
                    for (var k = 0; k < dirty.length; k++) {
                        refresh(dirty[k]);
                    }
                } finally {
                    syncingVal = false;
                }
            }
        }

        return result;
    };

    // ۳) محتوایی که با AJAX جایگزین می‌شود
    $(document).on('ajaxComplete', queueScan);

    // ۴) form.reset() مقدارها را برمی‌گرداند ولی رویداد change نمی‌فرستد
    $(document).on('reset', 'form', function (e) {
        var form = e.target;

        window.setTimeout(function () {
            $(form).find('select.select2-hidden-accessible').each(function () {
                refresh(this);
            });
        }, 0);
    });

    // ۵) مودال‌ها، آفکانواس‌ها، تب‌ها و آکاردئون‌ها
    $(document).on('shown.bs.modal shown.bs.offcanvas', function (e) {
        scan(e.target);
    });

    $(document).on('shown.bs.tab shown.bs.collapse', function () {
        queueScan();
    });

    function closeAllOpen() {
        $('select.select2-hidden-accessible').each(function () {
            if (instanceOf(this)) {
                callNative($(this), 'close');
            }
        });
    }

    $(document).on('hide.bs.modal hide.bs.offcanvas', function (e) {
        $(e.target).find('select.select2-hidden-accessible').each(function () {
            if (instanceOf(this)) {
                callNative($(this), 'close');
            }
        });
    });

    // با چرخش صفحه، dropdownِ باز دیگر سرِ جای درستی نیست
    $(window).on('orientationchange', closeAllOpen);

    // ۶) کادر جستجو
    //    روی دسکتاپ باید بلافاصله فوکوس بگیرد تا بشود بی‌درنگ تایپ کرد.
    //    روی موبایل نباید، چون صفحه‌کلید بلافاصله باز می‌شود و نیمی از فهرست
    //    را می‌پوشاند؛ کاربر هر وقت خواست جستجو کند روی کادر ضربه می‌زند.
    //    فقط کادرِ داخل dropdown (تک‌انتخابی) هدف است؛ کادرِ درون‌خطیِ
    //    چندانتخابی بخشی از خودِ کنترل است و نباید دستکاری شود.
    var SEARCH_FIELD_SELECTOR =
        '.select2-container--open .select2-search--dropdown .select2-search__field';

    $(document).on('select2:open', function () {
        // Select2 خودش هنگام باز شدن فوکوس می‌دهد، پس بعد از آن اجرا می‌شویم
        window.setTimeout(function () {
            var field = document.querySelector(SEARCH_FIELD_SELECTOR);

            if (!field) {
                return;
            }

            if (isSmallScreen()) {
                field.blur();
            } else if (document.activeElement !== field) {
                field.focus();
            }
        }, 0);
    });

    // -------------------------------------------------------------------------
    // اجرا
    //
    // کنترل‌ها باید پیش از هندلرهای ready صفحات ساخته شوند، چون بعضی از آن‌ها
    // سراغ خودِ کنترلِ Select2 می‌روند (مثلاً center.js که با
    // `$('.services-select').next('.select2').hide()` آن را پنهان می‌کند).
    //
    // جی‌کوئری به DOMContentLoaded روی document گوش می‌دهد و چون زودتر از ما
    // ثبت شده، در فاز هدف زودتر اجرا می‌شود. با گوش دادن در فاز capture روی
    // window، پیش از همه‌ی آن‌ها اجرا می‌شویم.
    // -------------------------------------------------------------------------
    var booted = false;

    function boot() {
        if (booted) {
            return;
        }

        booted = true;
        watchDom();
        scan();
    }

    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', boot, true);
    } else {
        boot();
    }

    // یک اسکنِ پایانی بعد از اجرای همه‌ی هندلرهای ready هر صفحه
    $(function () {
        window.setTimeout(scan, 0);
    });

    $(window).on('load', function () {
        window.setTimeout(scan, 0);
    });

    // -------------------------------------------------------------------------
    // API عمومی
    // -------------------------------------------------------------------------
    window.AdminSelect2 = {
        config: config,
        scan: scan,
        normalize: normalize,
        refresh: function (target) {
            $(target).each(function () {
                refresh(this);
            });
        },
        enhance: function (target, options) {
            $(target).each(function () {
                if (this.tagName === 'SELECT') {
                    initOne(this, options || null);
                }
            });
        },
        destroy: function (target) {
            $(target).each(function () {
                if (instanceOf(this)) {
                    callNative($(this), 'destroy');
                }
            });
        }
    };

})(window.jQuery);
