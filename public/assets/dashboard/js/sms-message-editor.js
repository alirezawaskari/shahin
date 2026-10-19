/**
 * SmsMessageEditor
 * ----------------
 * Shared SMS message editor used by every send screen (single, bulk, legacy group).
 * Markup comes from resources/views/Admin/sms/partials/message-editor.blade.php.
 *
 * It owns:
 *   - the dynamic-parameter chip editor (delegates to SmsParamChips, if loaded),
 *   - ONE live counter rendered INSIDE the field (used / remaining, pages, encoding,
 *     tariff and estimated cost).
 *
 * The counting rules mirror SingleSmsSendService::calculateSegmentation() exactly:
 *   - advertising messages get "\nلغو 11" appended (after rtrim) unless the text
 *     already contains "لغو 11"; it counts toward characters and pages;
 *   - any character outside ASCII 0x20-0x7E / \r \n \t makes the message Unicode;
 *   - Unicode: 70 / 134 / then +67 per page;  GSM-7: 160 / 306 / then 153 per page;
 *   - length is in code points (mb_strlen).
 *
 * Public API
 *   SmsMessageEditor.get(idOrEl)  -> instance (lazy-initialises the editor)
 *   SmsMessageEditor.segment(text, advertising) -> stats (pure function)
 *   instance.getStats()           -> last computed stats
 *   instance.getText()            -> raw text as typed (chips serialised as {token})
 *   instance.setValue(text)       -> replace content (re-renders chips) and recount
 *   instance.setAdvertising(bool) -> toggle the mandatory opt-out accounting (OTP = false)
 *   instance.setResolver(fn)      -> optional fn(text) => text used for counting/preview
 *   instance.setCostPerPage(n)    -> override tariff
 *   instance.onChange(fn)         -> fn(stats, instance) on every change
 *   instance.textarea / instance.root
 *
 * Event: a bubbling CustomEvent "smseditor:change" (detail = stats) is dispatched on the
 * root element; the underlying <textarea> also keeps receiving native input/change events.
 *
 * stats = { typedCount, charCount, pageCount, remaining, perPage, limit, isUnicode,
 *           encoding, encodingLabel, appended, text, finalText, cost, costPerPage, empty }
 */
(function (window, document) {
    'use strict';

    var OPT_OUT = 'لغو 11';
    var registry = {};

    function codePoints(str) {
        return Array.from(str || '').length;
    }

    function isUnicode(str) {
        if (!str) return false;
        return /[^ -~\r\n\t]/.test(str);
    }

    function rtrimPhp(str) {
        return str.replace(/[ \t\n\r\0\x0B]+$/, '');
    }

    function segment(text, advertising) {
        var typed = text || '';
        var typedCount = codePoints(typed);
        var final = typed;
        var appended = false;

        // Same rule as SingleSmsSendService::calculateSegmentation(): the suffix is appended even when
        // nothing is typed, so an empty advertising message already counts «لغو 11».
        if (advertising && final.indexOf(OPT_OUT) === -1) {
            final = rtrimPhp(final) + '\n' + OPT_OUT;
            appended = true;
        }

        var total = codePoints(final);
        var unicode = isUnicode(final);
        var pages = 1;
        var remaining;
        var perPage;

        if (total === 0) {
            remaining = unicode ? 70 : 160;
            perPage = remaining;
        } else if (unicode) {
            if (total <= 70) {
                pages = 1; remaining = 70 - total; perPage = 70;
            } else if (total <= 134) {
                pages = 2; remaining = 134 - total; perPage = 64;
            } else {
                pages = 2 + Math.ceil((total - 134) / 67);
                remaining = 134 + (pages - 2) * 67 - total;
                perPage = 67;
            }
        } else {
            if (total <= 160) {
                pages = 1; remaining = 160 - total; perPage = 160;
            } else if (total <= 306) {
                pages = 2; remaining = 306 - total; perPage = 146;
            } else {
                pages = Math.ceil(total / 153);
                remaining = pages * 153 - total;
                perPage = 153;
            }
        }

        return {
            typedCount: typedCount,
            charCount: total,
            pageCount: pages,
            remaining: remaining,
            perPage: perPage,
            limit: total + remaining,
            isUnicode: unicode,
            encoding: unicode ? 'Unicode' : 'GSM-7',
            encodingLabel: unicode ? 'فارسی (Unicode)' : 'انگلیسی (GSM-7)',
            appended: appended,
            text: typed,
            finalText: final,
            empty: typed.trim() === ''
        };
    }

    function fa(n) {
        return Number(n).toLocaleString('fa-IR');
    }

    function Editor(root) {
        var self = this;
        this.root = root;
        this.textarea = root.querySelector('textarea');
        this.listeners = [];
        this.advertising = root.getAttribute('data-advertising') !== '0';
        this.resolver = null;
        this.costPerPage = parseFloat(root.getAttribute('data-cost-per-page')) || 0;
        this.stats = null;

        var els = {};
        ['used', 'suffix', 'suffixnote', 'remaining', 'pages', 'encoding', 'cost', 'tariff', 'optout', 'bar', 'badge'].forEach(function (k) {
            els[k] = root.querySelector('[data-sms-counter="' + k + '"]');
        });
        this.els = els;

        var chips = root.querySelectorAll('[data-token]');
        this.chips = null;
        if (window.SmsParamChips && this.textarea) {
            this.chips = window.SmsParamChips.attach(this.textarea, chips);
        }

        var recalc = function () { self.update(); };
        this.textarea.addEventListener('input', recalc);
        this.textarea.addEventListener('change', recalc);
        if (this.chips && this.chips.editor) {
            this.chips.editor.addEventListener('input', recalc);
            this.chips.editor.addEventListener('keyup', recalc);
        }

        this.update();
    }

    Editor.prototype.getText = function () {
        return this.textarea.value;
    };

    Editor.prototype.getStats = function () {
        return this.stats;
    };

    Editor.prototype.setValue = function (text) {
        if (this.chips) {
            this.chips.setValue(text || '');
        } else {
            this.textarea.value = text || '';
        }
        this.update();
    };

    Editor.prototype.setAdvertising = function (flag) {
        flag = !!flag;
        if (flag === this.advertising) return;
        this.advertising = flag;
        if (this.els.optout) this.els.optout.style.display = this.advertising ? '' : 'none';
        if (this.els.suffix) this.els.suffix.style.display = this.advertising ? '' : 'none';
        this.root.classList.toggle('is-otp', !this.advertising);
        this.update();
    };

    Editor.prototype.setResolver = function (fn) {
        this.resolver = typeof fn === 'function' ? fn : null;
        this.update();
    };

    Editor.prototype.setCostPerPage = function (n) {
        this.costPerPage = Number(n) || 0;
        this.update();
    };

    Editor.prototype.onChange = function (fn) {
        if (typeof fn === 'function') {
            this.listeners.push(fn);
            if (this.stats) fn(this.stats, this);
        }
    };

    Editor.prototype.update = function () {
        var raw = this.textarea.value || '';
        var text = this.resolver ? this.resolver(raw) : raw;
        var s = segment(text, this.advertising);
        s.rawText = raw;
        s.costPerPage = this.costPerPage;
        // Empty advertising message still counts the mandatory opt-out (1 page) -> pages x tariff.
        // Empty OTP (no suffix) has nothing to send -> 0.
        s.cost = (s.empty && !this.advertising) ? 0 : s.pageCount * this.costPerPage;
        this.stats = s;

        var e = this.els;
        // One number: the full final length (typed text + mandatory suffix when advertising).
        if (e.used) e.used.textContent = fa(s.charCount);
        var showSuffix = this.advertising;
        if (e.suffix) e.suffix.style.display = showSuffix ? '' : 'none';
        if (e.suffixnote) e.suffixnote.style.display = showSuffix ? '' : 'none';
        if (e.remaining) e.remaining.textContent = fa(s.remaining);
        if (e.pages) e.pages.textContent = fa(s.pageCount);
        if (e.badge) e.badge.textContent = fa(s.pageCount) + ' صفحه';
        if (e.encoding) e.encoding.textContent = s.encodingLabel;
        if (e.tariff) e.tariff.textContent = this.costPerPage ? fa(this.costPerPage) : '-';
        if (e.cost) e.cost.textContent = this.costPerPage ? fa(s.cost) : '-';
        if (e.optout) {
            e.optout.style.display = this.advertising ? '' : 'none';
        }
        if (e.bar) {
            // warn when the user is in the last few characters before a new page (cost grows)
            e.bar.classList.toggle('is-near-limit', !s.empty && s.remaining <= 5);
            e.bar.classList.toggle('is-multipage', s.pageCount > 1);
        }

        for (var i = 0; i < this.listeners.length; i++) {
            try { this.listeners[i](s, this); } catch (err) { if (window.console) console.error(err); }
        }
        var evt;
        try {
            evt = new CustomEvent('smseditor:change', { bubbles: true, detail: s });
        } catch (err) {
            evt = document.createEvent('CustomEvent');
            evt.initCustomEvent('smseditor:change', true, false, s);
        }
        this.root.dispatchEvent(evt);
    };

    function get(idOrEl) {
        var el = typeof idOrEl === 'string'
            ? (document.getElementById(idOrEl) || document.querySelector('[data-sms-editor][data-for="' + idOrEl + '"]'))
            : idOrEl;
        if (!el) return null;
        var root = el.hasAttribute && el.hasAttribute('data-sms-editor') ? el : el.closest('[data-sms-editor]');
        if (!root) return null;
        if (!root.__smsEditor) {
            root.__smsEditor = new Editor(root);
        }
        return root.__smsEditor;
    }

    window.SmsMessageEditor = { get: get, segment: segment, isUnicode: isUnicode, OPT_OUT: OPT_OUT };
})(window, document);
