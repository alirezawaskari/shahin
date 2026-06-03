/* ─────────────────────────────────────────────────────────────
 * admin-login.js
 *
 * منطق صفحه‌ی ورود پنل ادمین:
 *   1. سویچ بین تب‌ها (پسورد / OTP)
 *   2. تبدیل اعداد فارسی/عربی به انگلیسی در input ها
 *   3. AJAX برای درخواست و verify کد OTP
 *   4. تایمر شمارش معکوس
 *   5. مدیریت loading state دکمه‌ها
 *
 * وابستگی: jQuery
 * ───────────────────────────────────────────────────────────── */
(function ($) {
    'use strict';

    // ═══════════════════════════════════════════════════════════
    // پیکربندی AJAX سراسری
    // ═══════════════════════════════════════════════════════════

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept':       'application/json'
        }
    });

    // ═══════════════════════════════════════════════════════════
    // سویچ بین تب‌ها
    // ═══════════════════════════════════════════════════════════

    $('.login-tabs .nav-link').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var targetTab = $this.data('tab');

        $('.login-tabs .nav-link').removeClass('active');
        $this.addClass('active');

        $('.login-tab-content .tab-pane').removeClass('active');
        $('#tab-' + targetTab).addClass('active');
    });

    // ═══════════════════════════════════════════════════════════
    // تبدیل اعداد فارسی/عربی به انگلیسی
    // ═══════════════════════════════════════════════════════════

    function convertNumbers(input) {
        if (input === null || input === undefined) {
            return '';
        }

        var str = String(input);
        var persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        var arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        for (var i = 0; i < 10; i++) {
            str = str.replace(new RegExp(persian[i], 'g'), i)
                     .replace(new RegExp(arabic[i],  'g'), i);
        }

        return str;
    }

    // اعمال تبدیل خودکار روی همه‌ی input هایی که کلاس numeric-input دارند
    $(document).on('input', '.numeric-input, .otp-input', function () {
        var cursorPos = this.selectionStart;
        var original  = this.value;
        var converted = convertNumbers(original);

        if (original !== converted) {
            this.value = converted;
            try {
                this.setSelectionRange(cursorPos, cursorPos);
            } catch (e) {
                // برخی input types اجازه‌ی setSelectionRange نمی‌دهند
            }
        }
    });

    // در otp-input فقط رقم مجاز است
    $(document).on('input', '.otp-input', function () {
        this.value = convertNumbers(this.value).replace(/[^0-9]/g, '');
    });

    // ═══════════════════════════════════════════════════════════
    // منطق OTP
    // ═══════════════════════════════════════════════════════════

    var $otpRequestForm       = $('#otp-request-form');
    var $otpVerifyForm        = $('#otp-verify-form');
    var $otpRequestAlert      = $('#otp-request-alert');
    var $otpVerifyAlert       = $('#otp-verify-alert');
    var $otpPhone             = $('#otp-phone');
    var $otpVerifyPhone       = $('#otp-verify-phone');
    var $otpVerifyPhoneDisp   = $('#otp-verify-phone-display');
    var $otpCode              = $('#otp-code');
    var $otpTimerDisplay      = $('#otp-timer-display');
    var $otpResendBtn         = $('#otp-resend-btn');
    var $otpChangePhoneLink   = $('#otp-change-phone');
    var $otpRequestBtn        = $('#otp-request-btn');
    var $otpVerifyBtn         = $('#otp-verify-btn');

    var timerInterval = null;
    var currentPhone  = null;

    // ─── نمایش/مخفی کردن alert ها ───
    function showAlert($el, type, message) {
        $el.removeClass('alert-success alert-danger alert-warning')
           .addClass('alert-' + type)
           .text(message)
           .show();
    }

    function hideAllAlerts() {
        $otpRequestAlert.hide().empty();
        $otpVerifyAlert.hide().empty();
    }

    // ─── مدیریت loading state دکمه‌ها ───
    function setButtonLoading($btn, isLoading) {
        $btn.prop('disabled', isLoading);
        if (isLoading) {
            $btn.find('.btn-text').hide();
            $btn.find('.btn-loader').show();
        } else {
            $btn.find('.btn-text').show();
            $btn.find('.btn-loader').hide();
        }
    }

    // ─── تایمر شمارش معکوس ───
    function startTimer(seconds) {
        clearInterval(timerInterval);

        var remaining = parseInt(seconds, 10) || 120;
        $otpResendBtn.hide();

        function tick() {
            if (remaining <= 0) {
                clearInterval(timerInterval);
                $otpTimerDisplay.text('کد منقضی شد.');
                $otpResendBtn.show();
                return;
            }

            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            var formatted = (m > 0 ? m + ':' : '') + (s < 10 ? '0' + s : s);

            $otpTimerDisplay.text('زمان باقی‌مانده: ' + formatted);
            remaining--;
        }

        tick();
        timerInterval = setInterval(tick, 1000);
    }

    // ─── سویچ بین مراحل ───
    function showRequestStep() {
        clearInterval(timerInterval);
        hideAllAlerts();
        $otpVerifyForm.hide();
        $otpRequestForm.show();
    }

    function showVerifyStep(phone, ttlSeconds) {
        hideAllAlerts();
        currentPhone = phone;
        $otpVerifyPhone.val(phone);
        $otpVerifyPhoneDisp.text(phone);
        $otpCode.val('');
        $otpRequestForm.hide();
        $otpVerifyForm.show();
        $otpCode.trigger('focus');
        startTimer(ttlSeconds);
    }

    // ─── استخراج پیام خطا از پاسخ AJAX ───
    function extractErrorMessage(xhr) {
        var resp = xhr.responseJSON;

        if (resp && resp.message) {
            return resp.message;
        }

        if (resp && resp.errors) {
            // پیام‌های validation
            var errors = [];
            for (var field in resp.errors) {
                if (Object.prototype.hasOwnProperty.call(resp.errors, field)) {
                    errors = errors.concat(resp.errors[field]);
                }
            }
            if (errors.length) {
                return errors.join('، ');
            }
        }

        return 'خطایی رخ داد. لطفاً مجدداً تلاش کنید.';
    }

    // ─── ارسال درخواست OTP ───
    function sendOtpRequest(phone) {
        hideAllAlerts();
        setButtonLoading($otpRequestBtn, true);

        $.ajax({
            url:    $otpRequestForm.data('action'),
            method: 'POST',
            data:   { phone: phone }
        })
        .done(function (response) {
            // پاسخ موفقیت — می‌تواند واقعی یا fake (طبق گزینه الف) باشد
            var ttl = response && response.ttl_seconds
                ? response.ttl_seconds
                : 120;
            showVerifyStep(phone, ttl);
        })
        .fail(function (xhr) {
            showAlert($otpRequestAlert, 'danger', extractErrorMessage(xhr));
        })
        .always(function () {
            setButtonLoading($otpRequestBtn, false);
        });
    }

    // ─── submit فرم درخواست کد ───
    $otpRequestForm.on('submit', function (e) {
        e.preventDefault();
        var phone = convertNumbers($otpPhone.val()).trim();
        $otpPhone.val(phone); // نمایش شده‌ی تبدیل شده در input
        sendOtpRequest(phone);
    });

    // ─── submit فرم verify ───
    $otpVerifyForm.on('submit', function (e) {
        e.preventDefault();

        hideAllAlerts();
        setButtonLoading($otpVerifyBtn, true);

        var phone = $otpVerifyPhone.val();
        var code  = convertNumbers($otpCode.val()).trim();
        $otpCode.val(code);

        $.ajax({
            url:    $otpVerifyForm.data('action'),
            method: 'POST',
            data:   { phone: phone, code: code }
        })
        .done(function (response) {
            showAlert($otpVerifyAlert, 'success', (response && response.message) || 'ورود موفق.');

            if (response && response.redirect) {
                window.location.href = response.redirect;
            } else {
                window.location.reload();
            }
        })
        .fail(function (xhr) {
            showAlert($otpVerifyAlert, 'danger', extractErrorMessage(xhr));
            setButtonLoading($otpVerifyBtn, false);
        });
        // در حالت موفقیت، صفحه تغییر می‌کند پس نیاز به always نیست
    });

    // ─── ارسال مجدد ───
    $otpResendBtn.on('click', function () {
        if (currentPhone) {
            sendOtpRequest(currentPhone);
        } else {
            showRequestStep();
        }
    });

    // ─── تغییر شماره ───
    $otpChangePhoneLink.on('click', function (e) {
        e.preventDefault();
        showRequestStep();
    });

})(jQuery);
