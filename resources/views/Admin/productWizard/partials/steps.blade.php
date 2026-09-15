{{--
    نوار مراحل ویزارد ایجاد محصول.

    ورودی‌ها:
      $current — کلید مرحله جاری: type | category | create | variation | done
      $kind    — product | service | null (وقتی هنوز نوع انتخاب نشده)

    مرحله «نوع» فقط برای کسب‌وکاری که هم کالا و هم خدمت دارد نمایش داده می‌شود و
    مرحله «تنوع» فقط برای کالا؛ چون خدمت تنوع ندارد.
--}}
@php
    $wizard_kind = $kind ?? null;
    $wizard_label = $wizard_kind === 'service' ? 'خدمت' : 'کالا';
    $asks_kind = app(\App\Http\Controllers\Admin\ProductWizardController::class)->kindOfBusiness() === null;

    $wizard_steps = [];
    if ($asks_kind) {
        $wizard_steps['type'] = 'انتخاب کالا یا خدمت';
    }
    $wizard_steps['category'] = 'ایجاد دسته‌بندی';
    // ویژگی‌ها روی دسته‌بندی تعریف می‌شوند و بعد سر کالا مقدار می‌گیرند؛ خدمت ویژگی ندارد.
    if ($wizard_kind !== 'service') {
        $wizard_steps['attributes'] = 'ویژگی‌های دسته‌بندی';
    }
    $wizard_steps['create'] = 'ثبت ' . $wizard_label;
    if ($wizard_kind !== 'service') {
        $wizard_steps['variation'] = 'ثبت تنوع کالا';
    }
    $wizard_steps['done'] = 'اتمام';

    $wizard_keys = array_keys($wizard_steps);
    $wizard_current_index = array_search($current, $wizard_keys, true);
@endphp

<div class="card mb-3">
    <div class="card-body py-3">
        <ol class="breadcrumb bg-transparent mb-0 p-0 flex-wrap">
            @foreach($wizard_steps as $wizard_key => $wizard_title)
                @php
                    $wizard_index = $loop->index;
                    $wizard_done = $wizard_current_index !== false && $wizard_index < $wizard_current_index;
                    $wizard_active = $wizard_key === $current;
                @endphp
                <li class="breadcrumb-item {{ $wizard_active ? 'active fw-bold' : '' }}">
                    <span class="badge rounded-pill {{ $wizard_active ? 'bg-primary' : ($wizard_done ? 'bg-success' : 'bg-secondary') }}">
                        {{ $wizard_index + 1 }}
                    </span>
                    <span class="{{ $wizard_done ? 'text-success' : ($wizard_active ? '' : 'text-muted') }}">{{ $wizard_title }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
