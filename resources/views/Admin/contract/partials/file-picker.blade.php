{{--
    Shared file picker for the contract media forms. Include it ONCE per page, inside the page's
    @section('js') / @section('scripts') (those are yielded after </main>). Never inside @section('content'):
    the content is compiled by the Vue app mounted on <main>, which drops <script> tags and re-renders the nodes.

    Inputs opt in with: data-file-picker, data-notice-id (optional), and ContractMediaService::pickerAttributes().
    One delegated change listener on document handles every such input, at any time. Per-input state lives in
    a WeakMap; the notice and list nodes are looked up or re-created on every use, so a re-render cannot leave
    the picker with stale nodes.
--}}
@once
<script>
(function () {
    'use strict';
    if (window.ContractFilePicker) { return; }

    var SELECTOR = 'input[type=file][data-file-picker]';
    var ALLOWED = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    var DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    var states = new WeakMap();

    function fa(value) {
        return String(value)
            .replace(/[0-9]/g, function (d) { return DIGITS[+d]; })
            .replace('.', '٫');
    }

    function megabytes(bytes) {
        return fa(Math.round((bytes / 1048576) * 10) / 10) + ' مگابایت';
    }

    function sizeLabel(bytes) {
        if (bytes >= 1048576) { return megabytes(bytes); }
        return fa(Math.max(1, Math.round(bytes / 1024))) + ' کیلوبایت';
    }

    function totalBytes(files) {
        return files.reduce(function (sum, file) { return sum + file.size; }, 0);
    }

    function readConfig(input) {
        return {
            max: parseInt(input.getAttribute('data-max-files'), 10) || 5,
            maxFile: parseInt(input.getAttribute('data-max-file-bytes'), 10) || 10485760,
            uploadMax: parseInt(input.getAttribute('data-upload-max-bytes'), 10) || 0,
            postMax: parseInt(input.getAttribute('data-post-max-bytes'), 10) || 0,
            // Files the record already holds on the server (0 unless the page says so); they count toward the limit.
            existing: parseInt(input.getAttribute('data-existing-files'), 10) || 0
        };
    }

    function stateOf(input) {
        var state = states.get(input);
        if (!state) {
            state = {
                picked: Array.prototype.slice.call(input.files || []),
                ownNotice: null,
                defaultText: null,
                list: null
            };
            states.set(input, state);
        }
        return state;
    }

    // The notice element is looked up by id each time (Vue may have replaced it); our own one is the fallback.
    function noticeFor(input, state, cfg) {
        var id = input.getAttribute('data-notice-id');
        var notice = id ? document.getElementById(id) : null;

        if (!notice || !notice.isConnected) {
            if (state.ownNotice && state.ownNotice.isConnected) {
                notice = state.ownNotice;
            } else {
                notice = document.createElement('div');
                notice.className = 'small text-muted mt-1';
                notice.textContent = 'حداکثر می‌توانید تا ' + fa(cfg.max) + ' فایل را به‌صورت هم‌زمان انتخاب فرمایید.';
                input.insertAdjacentElement('afterend', notice);
                state.ownNotice = notice;
                state.defaultText = null;
            }
        }

        if (state.defaultText === null) { state.defaultText = notice.textContent; }
        return notice;
    }

    function listFor(notice, state) {
        if (!state.list) {
            state.list = document.createElement('ul');
            state.list.className = 'list-unstyled small mt-2 mb-0';
        }
        if (!state.list.isConnected || state.list.previousElementSibling !== notice) {
            notice.insertAdjacentElement('afterend', state.list);
        }
        return state.list;
    }

    // «n از ۵ فایل»: saved files of the record plus the new picks.
    function counterText(cfg, state) {
        var text = fa(cfg.existing + state.picked.length) + ' از ' + fa(cfg.max) + ' فایل';
        return cfg.existing ? text + ' (شامل ' + fa(cfg.existing) + ' فایل ذخیره‌شده)' : text;
    }

    function checkFile(file, cfg) {
        var dot = file.name.lastIndexOf('.');
        var ext = dot < 0 ? '' : file.name.slice(dot + 1).toLowerCase();
        if (ALLOWED.indexOf(ext) === -1) {
            return 'فرمت «' + file.name + '» مجاز نیست؛ فقط pdf، doc، docx، jpg، jpeg و png پذیرفته می‌شود.';
        }
        if (cfg.uploadMax && file.size > cfg.uploadMax) {
            return 'حجم «' + file.name + '» از حد مجاز سرور (' + megabytes(cfg.uploadMax) + ') بیشتر است و پذیرفته نشد.';
        }
        if (file.size > cfg.maxFile) {
            return 'حجم «' + file.name + '» بیش از ' + megabytes(cfg.maxFile) + ' است و پذیرفته نشد.';
        }
        return '';
    }

    // Keeps input.files equal to the kept list (the native list is replaced on every pick).
    function sync(input, state) {
        var transfer = new DataTransfer();
        state.picked.forEach(function (file) { transfer.items.add(file); });
        input.files = transfer.files;
    }

    function render(input, state, errors) {
        var cfg = readConfig(input);
        var notice = noticeFor(input, state, cfg);
        var list = listFor(notice, state);

        while (list.firstChild) { list.removeChild(list.firstChild); }

        state.picked.forEach(function (file, index) {
            var row = document.createElement('li');
            row.className = 'd-flex justify-content-between align-items-center gap-2 py-1 border-bottom';

            var name = document.createElement('span');
            name.className = 'text-truncate';
            name.setAttribute('dir', 'ltr');
            name.textContent = file.name;

            var meta = document.createElement('span');
            meta.className = 'text-muted text-nowrap';
            meta.textContent = sizeLabel(file.size);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger py-0 px-2';
            remove.setAttribute('aria-label', 'حذف فایل ' + file.name);
            remove.textContent = '×';
            remove.addEventListener('click', function () {
                state.picked.splice(index, 1);
                sync(input, state);
                render(input, state, []);
            });

            row.appendChild(name);
            row.appendChild(meta);
            row.appendChild(remove);
            list.appendChild(row);
        });

        if (errors.length) {
            notice.className = 'small text-danger mt-1';
            notice.textContent = errors.join(' ');
        } else if (state.picked.length) {
            notice.className = 'small text-success mt-1';
            notice.textContent = counterText(cfg, state) + ' انتخاب شده است.';
        } else if (cfg.existing) {
            notice.className = 'small text-success mt-1';
            notice.textContent = counterText(cfg, state) + '.';
        } else {
            notice.className = 'small text-muted mt-1';
            notice.textContent = state.defaultText;
        }
    }

    function handleChange(input) {
        var cfg = readConfig(input);
        var state = stateOf(input);
        var incoming = Array.prototype.slice.call(input.files || []);
        // Cleared here (a programmatic clear fires no change event); sync() puts the kept list back.
        // A cancelled dialog (empty change) therefore keeps the earlier picks.
        input.value = '';

        var errors = [];
        incoming.forEach(function (file) {
            var problem = checkFile(file, cfg);
            if (problem) { errors.push(problem); return; }

            if (cfg.existing + state.picked.length >= cfg.max) {
                errors.push('حداکثر ' + fa(cfg.max) + ' فایل مجاز است؛ «' + file.name + '» اضافه نشد.');
                return;
            }

            if (cfg.postMax && totalBytes(state.picked) + file.size > cfg.postMax) {
                errors.push('مجموع حجم فایل‌ها از حد مجاز سرور (' + sizeLabel(cfg.postMax) + ') بیشتر است؛ «' + file.name + '» اضافه نشد.');
                return;
            }

            state.picked.push(file);
        });

        sync(input, state);
        render(input, state, errors);
    }

    // Inputs already on the page get their notice and list at load; later ones on their first change.
    function ensureAll() {
        if (typeof DataTransfer === 'undefined') { return; }
        Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), function (input) {
            render(input, stateOf(input), []);
        });
    }

    // One delegated listener: survives any re-render of the inputs or their neighbours.
    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!input || !input.matches || !input.matches(SELECTOR)) { return; }
        // Old browsers without DataTransfer keep the native picker; the server still validates every file.
        if (typeof DataTransfer === 'undefined') { return; }
        handleChange(input);
    });

    window.ContractFilePicker = { ensure: ensureAll };

    if (document.readyState === 'complete') {
        ensureAll();
    } else {
        window.addEventListener('load', ensureAll);
    }
})();
</script>
@endonce
