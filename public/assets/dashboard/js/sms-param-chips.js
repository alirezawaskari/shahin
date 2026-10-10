/**
 * SmsParamChips
 * -------------
 * Turns a plain <textarea> used for composing SMS message text into a
 * rich, contenteditable editor where Dynamic Parameters (e.g. "{نام کاربر}")
 * are rendered as small, colored, non-text-editable chip elements instead of
 * raw "{...}" brace text. The chip is removable as a single unit (click the
 * × on the chip, or Backspace/Delete right next to it) and cannot be edited
 * character-by-character.
 *
 * The original <textarea> is kept in the DOM (hidden) and its `.value` is
 * kept in sync with the editor on every change, with native `input`/`change`
 * events re-dispatched on it. This means any existing code that reads
 * `textarea.value` or listens for `input`/`change` on the textarea (character
 * counters, live phone-mockup previews, segmentation math, form submission)
 * keeps working unmodified.
 *
 * Plain vanilla JS/CSS, no framework — consistent with the rest of the SMS
 * module's send/compose screens.
 */
(function (window, document) {
    'use strict';

    var STYLE_ID = 'sms-param-chips-style';
    var TOKEN_RE = /(\{[^{}]{1,60}\})/g;
    var ZWSP = '​';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = [
            '.sms-param-editor {',
            '  min-height: 110px;',
            '  white-space: pre-wrap;',
            '  word-break: break-word;',
            '  overflow-y: auto;',
            '}',
            '.sms-param-editor:empty:before {',
            '  content: attr(data-placeholder);',
            '  color: #94a3b8;',
            '  pointer-events: none;',
            '}',
            '.sms-param-chip {',
            '  display: inline-flex;',
            '  align-items: center;',
            '  gap: 4px;',
            '  background: #e0e7ff;',
            '  color: #3730a3;',
            '  border: 1px solid #c7d2fe;',
            '  border-radius: 999px;',
            '  padding: 1px 6px 1px 4px;',
            '  margin: 0 1px;',
            '  font-size: 0.85em;',
            '  line-height: 1.6;',
            '  user-select: none;',
            '  white-space: nowrap;',
            '  direction: rtl;',
            '  vertical-align: baseline;',
            '}',
            '.sms-param-chip .sms-param-chip-remove {',
            '  display: inline-flex;',
            '  align-items: center;',
            '  justify-content: center;',
            '  width: 14px;',
            '  height: 14px;',
            '  border-radius: 50%;',
            '  background: rgba(55, 48, 163, 0.12);',
            '  cursor: pointer;',
            '  font-size: 0.8em;',
            '  line-height: 1;',
            '}',
            '.sms-param-chip .sms-param-chip-remove:hover {',
            '  background: rgba(220, 38, 38, 0.2);',
            '  color: #b91c1c;',
            '}',
            '.sms-param-palette-chip {',
            '  cursor: pointer;',
            '}',
        ].join('\n');
        document.head.appendChild(style);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildChip(token) {
        var chip = document.createElement('span');
        chip.className = 'sms-param-chip';
        chip.setAttribute('contenteditable', 'false');
        chip.setAttribute('data-token', token);

        var label = document.createElement('span');
        label.textContent = token;
        chip.appendChild(label);

        var remove = document.createElement('span');
        remove.className = 'sms-param-chip-remove';
        remove.setAttribute('role', 'button');
        remove.setAttribute('title', 'حذف متغیر');
        remove.setAttribute('aria-label', 'حذف متغیر');
        remove.textContent = '×';
        chip.appendChild(remove);

        return chip;
    }

    /**
     * Render a plain-text value (possibly containing "{token}" placeholders)
     * into the editor as a mix of text nodes and chip elements.
     */
    function renderValueIntoEditor(editor, value) {
        editor.innerHTML = '';
        if (!value) {
            return;
        }

        var lines = String(value).split('\n');
        lines.forEach(function (line, lineIndex) {
            if (lineIndex > 0) {
                editor.appendChild(document.createElement('br'));
            }
            var parts = line.split(TOKEN_RE);
            parts.forEach(function (part) {
                if (part === '') {
                    return;
                }
                if (TOKEN_RE.test(part) && /^\{[^{}]{1,60}\}$/.test(part)) {
                    editor.appendChild(buildChip(part));
                } else {
                    editor.appendChild(document.createTextNode(part));
                }
            });
        });
    }

    /**
     * Walk the editor's DOM and serialize it back to plain text, turning
     * chip elements back into their "{token}" form and <br>/<div> into
     * newlines (contenteditable browsers sometimes wrap new lines in <div>).
     */
    function serialize(node) {
        var out = '';
        node.childNodes.forEach(function (child) {
            if (child.nodeType === Node.TEXT_NODE) {
                out += child.textContent.split(ZWSP).join('');
            } else if (child.nodeType === Node.ELEMENT_NODE) {
                if (child.classList && child.classList.contains('sms-param-chip')) {
                    out += child.getAttribute('data-token');
                } else if (child.tagName === 'BR') {
                    out += '\n';
                } else if (child.tagName === 'DIV') {
                    out += (out.length ? '\n' : '') + serialize(child);
                } else {
                    out += serialize(child);
                }
            }
        });
        return out;
    }

    function dispatch(el, type) {
        var evt;
        try {
            evt = new Event(type, { bubbles: true });
        } catch (e) {
            evt = document.createEvent('Event');
            evt.initEvent(type, true, true);
        }
        el.dispatchEvent(evt);
    }

    function placeCaretAfter(node) {
        var range = document.createRange();
        range.setStartAfter(node);
        range.collapse(true);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    /**
     * Attach a chip-based rich editor on top of `textarea`.
     *
     * @param {HTMLTextAreaElement} textarea
     * @param {NodeList|Array} paletteButtons buttons/badges with [data-token] that insert a chip on click
     * @returns {{insertToken: function(string):void, sync: function():void, editor: HTMLElement}}
     */
    function attach(textarea, paletteButtons) {
        if (!textarea || textarea.dataset.smsParamChipsAttached === '1') {
            return null;
        }
        injectStyles();
        textarea.dataset.smsParamChipsAttached = '1';

        var editor = document.createElement('div');
        editor.className = 'sms-param-editor ' + (textarea.className || '');
        editor.setAttribute('contenteditable', 'true');
        editor.setAttribute('dir', 'rtl');
        editor.setAttribute('data-placeholder', textarea.getAttribute('placeholder') || '');
        editor.id = textarea.id ? (textarea.id + 'Editor') : '';

        renderValueIntoEditor(editor, textarea.value);

        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(editor, textarea);

        function sync() {
            textarea.value = serialize(editor);
            dispatch(textarea, 'input');
            dispatch(textarea, 'change');
        }

        function insertToken(token) {
            editor.focus();
            var sel = window.getSelection();
            var range;
            if (sel && sel.rangeCount > 0 && editor.contains(sel.getRangeAt(0).commonAncestorContainer)) {
                range = sel.getRangeAt(0);
            } else {
                range = document.createRange();
                range.selectNodeContents(editor);
                range.collapse(false);
            }
            range.deleteContents();

            var chip = buildChip(token);
            range.insertNode(chip);

            // Trailing zero-width space so the caret has somewhere to land
            // right after a chip (chips themselves are not editable).
            var spacer = document.createTextNode(ZWSP);
            chip.parentNode.insertBefore(spacer, chip.nextSibling);
            placeCaretAfter(spacer);

            sync();
        }

        editor.addEventListener('input', sync);

        editor.addEventListener('click', function (e) {
            var removeBtn = e.target.closest && e.target.closest('.sms-param-chip-remove');
            if (removeBtn) {
                var chip = removeBtn.closest('.sms-param-chip');
                if (chip) {
                    chip.remove();
                    sync();
                }
            }
        });

        // Backspace/Delete right next to a chip removes the whole chip
        // instead of chewing through its text one character at a time.
        editor.addEventListener('keydown', function (e) {
            if (e.key !== 'Backspace' && e.key !== 'Delete') {
                return;
            }
            var sel = window.getSelection();
            if (!sel || sel.rangeCount === 0 || !sel.isCollapsed) {
                return;
            }
            var range = sel.getRangeAt(0);
            var node = range.startContainer;
            var offset = range.startOffset;

            function isChip(n) {
                return n && n.nodeType === Node.ELEMENT_NODE && n.classList && n.classList.contains('sms-param-chip');
            }

            var target = null;
            if (e.key === 'Backspace') {
                if (node.nodeType === Node.TEXT_NODE && offset === 0) {
                    target = node.previousSibling;
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    target = node.childNodes[offset - 1];
                }
                if (isChip(target) || (target === null && isChip(node.previousSibling))) {
                    target = isChip(target) ? target : node.previousSibling;
                }
            } else {
                if (node.nodeType === Node.TEXT_NODE && offset === node.textContent.length) {
                    target = node.nextSibling;
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    target = node.childNodes[offset];
                }
            }

            if (isChip(target)) {
                e.preventDefault();
                target.remove();
                sync();
            }
        });

        // Enter inserts a line break instead of a new <div> block (keeps
        // serialization simple and matches a plain <textarea>'s behaviour).
        editor.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var sel = window.getSelection();
                if (sel && sel.rangeCount > 0) {
                    var range = sel.getRangeAt(0);
                    range.deleteContents();
                    var br = document.createElement('br');
                    range.insertNode(br);
                    placeCaretAfter(br);
                }
                sync();
            }
        });

        editor.addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
            sync();
        });

        (paletteButtons || []).forEach(function (btn) {
            btn.classList.add('sms-param-palette-chip');
            btn.addEventListener('click', function () {
                insertToken(btn.getAttribute('data-token'));
            });
        });

        function setValue(value) {
            renderValueIntoEditor(editor, value);
            sync();
        }

        return { insertToken: insertToken, sync: sync, setValue: setValue, editor: editor };
    }

    window.SmsParamChips = { attach: attach };
})(window, document);
