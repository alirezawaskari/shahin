@extends('Admin.master')

@section('title', 'فهرست پیامک‌ها')

@section('css')
<style>
    .msg-preview-cell {
        min-width: 250px;
        max-width: 350px;
    }
    .msg-preview-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        transition: all 0.2s ease;
    }
    .msg-preview-box:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .msg-preview-box.confidential-box {
        background: #fff5f5;
        border-color: #fecaca;
    }
    .msg-preview-box.confidential-box:hover {
        background: #fee2e2;
        border-color: #f87171;
    }
    .msg-preview-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #334155;
        font-size: 12.5px;
        cursor: pointer;
        flex: 1;
    }
    .msg-preview-text:hover {
        color: #0284c7;
    }
    .btn-open-mode {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .sms-modal-bubble {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-right: 4px solid #3b82f6 !important;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 13.5px;
        line-height: 1.8;
        white-space: pre-wrap;
        word-break: break-word;
        color: #1e293b;
        direction: rtl;
        text-align: right;
        max-height: 280px;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">فهرست پیامک‌ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست پیامک‌ها</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
            <form method="GET" action="{{ route('groupsms.index') }}" class="d-flex flex-wrap gap-2 m-0">
                <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- کلیه کسب‌وکارها (نمای سراسری) --</option>
                    @foreach($all_businesses as $b)
                        <option value="{{ $b->id }}" {{ $business && $business->id === $b->id ? 'selected' : '' }}>
                            {{ $b->title }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        @if($business)
            <span class="badge bg-info text-dark px-3 py-2">
                <i class="fa fa-building-o ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> کسب‌وکار: {{ $business->title }}
            </span>
        @endif
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-plus ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> ایجاد پیامک
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="btnGroupDrop1">
                <a class="dropdown-item" href="{{ route('admin.sms.single.index') }}">پیامک تکی</a>
                <a class="dropdown-item" href="{{ route('admin.sms.bulk.index') }}">پیامک گروهی</a>
                <a class="dropdown-item" data-toggle="modal" data-target="#sendConsentModal" data-bs-toggle="modal" data-bs-target="#sendConsentModal">پیامک ثبت رضایت مشتری</a>
            </div>
        </div>
    </div>
</div>

<div class="card position-relative border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="table-id">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>عنوان</th>
                    <th>موبایل</th>
                    <th>پوشه / دسته‌بندی</th>
                    <th>نوع طبقه‌بندی</th>
                    <th>نحوه ارسال</th>
                    <th>متن پیام</th>
                    <th>تاریخ ارسال</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th class="text-center" style="width: 140px;">اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupsms as $key => $groupsm)
                @php
                    $isConfidential = $groupsm->isConfidential();
                @endphp
                <tr>
                    <td class="text-center text-muted small">
                        {{ $key + 1 }}
                    </td>

                    <td class="fw-semibold">
                        {{ $groupsm->title ?: '-' }}
                    </td>

                    <td>
                        <span dir="ltr" class="font-monospace">{{ $groupsm->phones }}</span>
                    </td>

                    {{-- Folder / Category --}}
                    <td>
                        @if($groupsm->group)
                            <span class="badge bg-light text-dark border">
                                <i class="fa fa-folder-o text-primary ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i>
                                {{ $groupsm->group->name }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    {{-- Type (Classification: Classified / Regular) --}}
                    <td>
                        @if($isConfidential)
                            <span class="badge bg-danger">
                                <i class="fa fa-lock ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> محرمانه
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                عادی
                            </span>
                        @endif
                    </td>

                    {{-- Sending Channel --}}
                    <td>
                        @if ($groupsm->type == 'single')
                            <span class="badge bg-light text-secondary border">ارسال تکی</span>
                        @elseif($groupsm->type == 'groupsService')
                            <span class="badge bg-info text-dark">گروهی خدمات</span>
                        @else
                            <span class="badge bg-primary">گروهی فهرست شماره‌ها</span>
                        @endif
                    </td>

                    {{-- Clamped Message with Opening Mode Trigger --}}
                    <td class="msg-preview-cell">
                        <div class="msg-preview-box {{ $isConfidential ? 'confidential-box' : '' }}">
                            @if($isConfidential)
                                <span class="text-danger fw-bold small d-flex align-items-center"
                                      style="cursor: pointer;"
                                      title="پیامک محرمانه است - برای مشاهده روی دکمه مشاهده کلیک کنید"
                                      data-toggle="modal"
                                      data-target="#messageModal-{{ $groupsm->id }}"
                                      data-bs-toggle="modal"
                                      data-bs-target="#messageModal-{{ $groupsm->id }}">
                                    <i class="fa fa-lock text-danger ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i>
                                    [متن محرمانه و محافظت‌شده]
                                </span>
                            @else
                                <span class="msg-preview-text"
                                      title="برای مشاهده متن کامل کلیک کنید"
                                      role="button"
                                      data-toggle="modal"
                                      data-target="#messageModal-{{ $groupsm->id }}"
                                      data-bs-toggle="modal"
                                      data-bs-target="#messageModal-{{ $groupsm->id }}">
                                    <i class="fa fa-comment-o text-muted ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i>
                                    {{ Str::limit(preg_replace('/\s+/', ' ', str_replace(['\r\n', '\r', '\n', "\r\n", "\r", "\n"], ' ', $groupsm->message)), 34, '...') }}
                                </span>
                            @endif
                            <button type="button"
                                    class="btn {{ $isConfidential ? 'btn-outline-danger' : 'btn-outline-primary' }} btn-open-mode"
                                    data-toggle="modal"
                                    data-target="#messageModal-{{ $groupsm->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#messageModal-{{ $groupsm->id }}"
                                    title="حالت بازشو (مشاهده جزئیات و متن)">
                                <i class="fa {{ $isConfidential ? 'fa-lock' : 'fa-eye' }} ms-1" style="margin-left: 3px !important; margin-right: 0 !important;"></i> مشاهده
                            </button>
                        </div>

                        {{-- Opening Mode Modal per message --}}
                        <div class="modal fade" id="messageModal-{{ $groupsm->id }}" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel-{{ $groupsm->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content shadow-lg border-0 text-start" style="background-color: #ffffff !important;">
                                    <div class="modal-header {{ $isConfidential ? 'bg-danger text-white' : 'bg-light text-dark' }} d-flex justify-content-between align-items-center py-3">
                                        <h6 class="modal-title fw-bold mb-0" id="messageModalLabel-{{ $groupsm->id }}">
                                            <i class="fa {{ $isConfidential ? 'fa-lock' : 'fa-envelope-open' }} ms-2" style="margin-left: 8px !important; margin-right: 0 !important;"></i>
                                            {{ $groupsm->title ?: 'مشاهده متن پیامک' }}
                                            @if($isConfidential)
                                                <span class="badge bg-white text-danger me-2">طبقه‌بندی محرمانه</span>
                                            @endif
                                        </h6>
                                        <button type="button" class="btn-close close {{ $isConfidential ? 'btn-close-white text-white' : '' }}" data-dismiss="modal" data-bs-dismiss="modal" aria-label="بستن"></button>
                                    </div>
                                    <div class="modal-body p-3">
                                        <div class="row g-2 mb-3 p-2 bg-light rounded border text-muted small">
                                            <div class="col-6">
                                                <span class="text-secondary">گیرنده / شماره‌ها:</span>
                                                <div dir="ltr" class="font-monospace text-dark fw-bold mt-1 text-truncate">{{ $groupsm->phones }}</div>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-secondary">نوع ارسال:</span>
                                                <div class="mt-1">
                                                    @if ($groupsm->type == 'single')
                                                        <span class="badge bg-secondary">ارسال تکی</span>
                                                    @elseif($groupsm->type == 'groupsService')
                                                        <span class="badge bg-info text-dark">گروهی خدمات</span>
                                                    @else
                                                        <span class="badge bg-primary">گروهی شماره‌ها</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <span class="text-secondary">پوشه / دسته‌بندی:</span>
                                                <div class="mt-1">
                                                    @if($groupsm->group)
                                                        <span class="badge bg-light text-dark border">📁 {{ $groupsm->group->name }}</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <span class="text-secondary">طبقه‌بندی:</span>
                                                <div class="mt-1">
                                                    @if($isConfidential)
                                                        <span class="badge bg-danger"><i class="fa fa-lock ms-1"></i> محرمانه</span>
                                                    @else
                                                        <span class="badge bg-secondary">عادی</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <span class="text-secondary">تاریخ ثبت:</span>
                                                <div class="text-dark mt-1">{{ verta($groupsm->created_at)->format('%d %B %Y - H:i:s') }}</div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <span class="text-secondary">وضعیت:</span>
                                                <div class="mt-1">
                                                    @if ($groupsm->status == 0)
                                                        <span class="badge bg-danger">پیش‌نویس</span>
                                                    @elseif($groupsm->status == 1)
                                                        <span class="badge bg-warning text-dark">آماده ارسال</span>
                                                    @else
                                                        <span class="badge bg-success">ارسال شده</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <label class="form-label small fw-bold text-secondary mb-1">
                                            <i class="fa fa-file-text-o text-muted ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> متن کامل پیامک:
                                        </label>

                                        @if(!$isConfidential)
                                            {{-- Regular message: visible to everyone --}}
                                            <div class="sms-modal-bubble mb-2" id="modalText-{{ $groupsm->id }}">{!! nl2br(e(str_replace('\n', "\n", $groupsm->message))) !!}</div>

                                            <div class="d-flex justify-content-between align-items-center small text-muted mt-2">
                                                <span>
                                                    تعداد کاراکتر: <strong class="text-primary">{{ mb_strlen($groupsm->message) }}</strong>
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyModalMessage({{ $groupsm->id }})">
                                                    <i class="fa fa-copy ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> کپی متن
                                                </button>
                                            </div>
                                        @elseif($canViewConfidential)
                                            {{-- Classified message & User has privilege: can open and view context --}}
                                            <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-2">
                                                <i class="fa fa-shield text-danger fa-lg ms-2" style="margin-left: 8px !important; margin-right: 0 !important;"></i>
                                                <div>
                                                    <strong>دسترسی محرمانه مجاز:</strong> شما به دلیل داشتن مجوز دسترسی، مجاز به مشاهده متن این پیامک هستید.
                                                </div>
                                            </div>
                                            <div class="sms-modal-bubble mb-2 border-danger" style="border-right-color: #dc3545 !important; background-color: #fff5f5;" id="modalText-{{ $groupsm->id }}">{!! nl2br(e(str_replace('\n', "\n", $groupsm->message))) !!}</div>

                                            <div class="d-flex justify-content-between align-items-center small text-muted mt-2">
                                                <span>
                                                    تعداد کاراکتر: <strong class="text-danger">{{ mb_strlen($groupsm->message) }}</strong>
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyModalMessage({{ $groupsm->id }})">
                                                    <i class="fa fa-copy ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> کپی متن
                                                </button>
                                            </div>
                                        @else
                                            {{-- Classified message & User LACKS privilege: text is strictly withheld --}}
                                            <div class="alert alert-danger py-3 px-3 text-center mb-2">
                                                <i class="fa fa-lock fa-2x text-danger mb-2 d-block"></i>
                                                <h6 class="fw-bold mb-1">دسترسی به متن این پیامک محدود شده است</h6>
                                                <p class="small text-muted mb-0">این پیام دارای طبقه‌بندی محرمانه است و شما مجوز لازم برای مشاهده متن آن را ندارید.</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer py-2 bg-light">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    @if ($groupsm->type == 'single')
                    <td class="text-muted small">
                        ندارد
                    </td>
                    @else
                    <td class="small">
                        {{ verta($groupsm->send_at)->format('%d %B ، %Y') }}
                    </td>
                    @endif

                    <td class="small">
                        {{ verta($groupsm->created_at)->format('%d %B ، %Y - H:i:s') }}
                    </td>

                    <td>
                        @if ($groupsm->status == 0)
                            <span class="badge bg-danger">پیش نویس</span>
                        @elseif($groupsm->status == 1)
                            <span class="badge bg-warning text-dark">آماده ارسال</span>
                        @else
                            <span class="badge bg-success">ارسال شده</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            @if ($groupsm->type != 'single')
                                <a href="{{ route('groupsms.edit', [$groupsm->id]) }}" class="btn btn-primary btn-sm py-1 px-2" title="ویرایش">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            @endif

                            <form class="delete_form d-inline mb-0" method="post"
                                action="{{ route('groupsms.destroy', [$groupsm->id]) }}">
                                @method('DELETE')
                                @csrf

                                <input type="button" class="btn btn-danger btn-sm py-1 px-2 delete_submit" id="{{ $groupsm->id }}"
                                    value="حذف" />
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="p-3">
            @include('Admin.layouts.pagination')
        </div>
    </div>
</div>

<div class="modal fade" id="sendConsentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-start">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">ارسال پیامک ثبت رضایت مشتری</h5>
                <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form action="{{ route('sms.sendConsent') }}" method="POST">
                <div class="modal-body">
                    @csrf

                    <div class="col-12 mb-3">
                        <label class="form-label small fw-bold">عنوان</label>
                        <input type="text" value="ارسال لینک ثبت رضایتمندی"
                               class="form-control"
                               name="title" required>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label small fw-bold">متن پیام</label>
                        <textarea required name="message" id="" cols="30" rows="4" class="form-control">عضو محترم خدمات رفاهی لاله جهت ثبت رضایت خود از مجموعه لاله از طریق لینک زیر اقدام فرمایید: https://trustseal.enamad.ir/?id=40920&Code=6abmm9WQ9521uXon0exd</textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label small fw-bold">شماره‌ها</label>
                        <textarea required placeholder="لطفا شماره های خود را با کامت (,) جدا کنید" name="phones" id="" cols="30" rows="4" class="form-control"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary btn-sm">ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
<script>
function copyModalMessage(id) {
    const el = document.getElementById('modalText-' + id);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'متن پیامک کپی شد',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            alert('متن پیامک کپی شد');
        }
    }).catch(function() {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('متن پیامک کپی شد');
    });
}
</script>
@endsection
