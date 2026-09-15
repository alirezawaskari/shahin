{{--
    نوار مراحل ویزارد ایجاد محصول — قابل کلیک، با دکمه‌های «قبلی» و «بعدی».

    ورودی‌ها:
      $current — کلید مرحله جاری: type | category | attributes | create | variation | done
      $kind    — product | service | null (پیش‌فرض: نوعی که ویزارد در session دارد)

    آدرس هر مرحله از زمینه ویزارد می‌آید (ProductWizardService::steps). مرحله‌ای که هنوز
    پیش‌نیازش معلوم نیست (مثلا ثبت کالا پیش از انتخاب دسته‌بندی) غیرفعال نمایش داده می‌شود.
--}}
@php
    $wizard_steps = \App\Services\V1\Admin\ProductWizardService::steps($current, $kind ?? null);
    $wizard_active = collect($wizard_steps)->search(fn($step) => $step['state'] === 'active');
    $wizard_prev = $wizard_active !== false && $wizard_active > 0 ? $wizard_steps[$wizard_active - 1] : null;
    $wizard_next = $wizard_active !== false ? ($wizard_steps[$wizard_active + 1] ?? null) : null;
@endphp

<div class="card mb-3">
    <div class="card-body py-3 d-flex align-items-center flex-wrap gap-2">
        @if($wizard_prev)
            @if($wizard_prev['url'])
                <a href="{{ $wizard_prev['url'] }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-right"></i> قبلی
                </a>
            @else
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="fa fa-arrow-right"></i> قبلی
                </button>
            @endif
        @endif

        <ol class="breadcrumb bg-transparent mb-0 p-0 flex-wrap flex-grow-1">
            @foreach($wizard_steps as $wizard_step)
                @php
                    $wizard_badge = ['active' => 'bg-primary', 'done' => 'bg-success', 'todo' => 'bg-secondary'][$wizard_step['state']];
                    $wizard_text = ['active' => 'fw-bold', 'done' => 'text-success', 'todo' => 'text-muted'][$wizard_step['state']];
                @endphp
                <li class="breadcrumb-item {{ $wizard_step['state'] === 'active' ? 'active' : '' }}">
                    @if($wizard_step['url'] && $wizard_step['state'] !== 'active')
                        <a href="{{ $wizard_step['url'] }}" class="text-decoration-none">
                            <span class="badge rounded-pill {{ $wizard_badge }}">{{ $wizard_step['number'] }}</span>
                            <span class="{{ $wizard_step['state'] === 'done' ? 'text-success' : '' }}">{{ $wizard_step['title'] }}</span>
                        </a>
                    @else
                        <span @if(! $wizard_step['url'] && $wizard_step['state'] !== 'active') title="ابتدا مرحله قبل را انجام دهید" @endif>
                            <span class="badge rounded-pill {{ $wizard_badge }}">{{ $wizard_step['number'] }}</span>
                            <span class="{{ $wizard_text }}">{{ $wizard_step['title'] }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>

        @if($wizard_next)
            @if($wizard_next['url'])
                <a href="{{ $wizard_next['url'] }}" class="btn btn-sm btn-primary">
                    بعدی <i class="fa fa-arrow-left"></i>
                </a>
            @else
                <button type="button" class="btn btn-sm btn-primary" disabled title="ابتدا این مرحله را کامل کنید">
                    بعدی <i class="fa fa-arrow-left"></i>
                </button>
            @endif
        @endif
    </div>
</div>
