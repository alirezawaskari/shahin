@extends('site.profile.master')

@section('content2')
<div class="col-12 col-md-9 mt-4 mt-md-0">
    <div class="tab-content" id="nav-tabContent">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h4 class="fw-bold mb-0 text-dark"><i class="fa fa-envelope-open-o text-primary me-2"></i> پیام‌ها</h4>
                    <p class="text-muted small mb-0 mt-1">مشاهده پیامک‌های دریافتی و ارسالی سیستم، تفکیک شده بر اساس پیام‌های عمومی و محرمانه</p>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-4" id="messagesTab" role="tablist">
                        <li class="nav-item me-2">
                            <a class="nav-link active py-2 px-4" id="normal-tab" data-toggle="pill" href="#normalMessages" role="tab" aria-controls="normalMessages" aria-selected="true">
                                <i class="fa fa-envelope-o me-1"></i> پیام‌های عادی ({{ $normalMessages->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-4 {{ !$canViewConfidential ? 'disabled text-muted' : '' }}" id="confidential-tab" data-toggle="pill" href="#confidentialMessages" role="tab" aria-controls="confidentialMessages" aria-selected="false">
                                <i class="fa {{ $canViewConfidential ? 'fa-user-secret text-danger' : 'fa-lock' }} me-1"></i> 
                                پیام‌های محرمانه 
                                @if($canViewConfidential)
                                    ({{ $confidentialMessages->count() }})
                                @else
                                    <span class="badge badge-secondary small">قفل</span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="messagesTabContent">
                        <!-- Normal Messages Tab -->
                        <div class="tab-pane fade show active" id="normalMessages" role="tabpanel" aria-labelledby="normal-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border text-center" style="font-family: font;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 60px;">#</th>
                                            <th scope="col" class="text-center">فرستنده</th>
                                            <th scope="col" class="text-center">گروه</th>
                                            <th scope="col" class="text-right">متن پیام</th>
                                            <th scope="col" class="text-center">تاریخ</th>
                                            <th scope="col" class="text-center">وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($normalMessages as $msg)
                                            <tr>
                                                <td>{{ $msg->id }}</td>
                                                <td style="direction: ltr;">{{ $msg->sender_phone ?: ($msg->senderNumber?->number ?: 'سیستم') }}</td>
                                                <td>{{ $msg->group?->name ?: 'عمومی' }}</td>
                                                <td class="text-right" style="direction: rtl; white-space: pre-wrap;">{{ $msg->content }}</td>
                                                <td><small>{{ verta($msg->created_at)->format('Y/m/d H:i') }}</small></td>
                                                <td>
                                                    @if($msg->status === 'delivered' || $msg->delivery_status === 'delivered')
                                                        <span class="badge badge-success px-2 py-1">تحویل شد</span>
                                                    @elseif($msg->status === 'failed')
                                                        <span class="badge badge-danger px-2 py-1">ناموفق</span>
                                                    @else
                                                        <span class="badge badge-warning text-dark px-2 py-1">{{ $msg->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fa fa-inbox fa-2x mb-2 text-muted"></i>
                                                    <p class="mb-0">هیچ پیامی در این بخش وجود ندارد.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Confidential Messages Tab -->
                        <div class="tab-pane fade" id="confidentialMessages" role="tabpanel" aria-labelledby="confidential-tab">
                            @if($canViewConfidential)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border text-center" style="font-family: font;">
                                        <thead class="bg-light">
                                            <tr>
                                                <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                <th scope="col" class="text-center">فرستنده</th>
                                                <th scope="col" class="text-center">گروه محرمانه</th>
                                                <th scope="col" class="text-right">متن پیام</th>
                                                <th scope="col" class="text-center">تاریخ</th>
                                                <th scope="col" class="text-center">وضعیت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($confidentialMessages as $msg)
                                                <tr>
                                                    <td>{{ $msg->id }}</td>
                                                    <td style="direction: ltr;">{{ $msg->sender_phone ?: ($msg->senderNumber?->number ?: 'سیستم') }}</td>
                                                    <td><span class="badge badge-danger">{{ $msg->group?->name ?: 'محرمانه' }}</span></td>
                                                    <td class="text-right" style="direction: rtl; white-space: pre-wrap;">{{ $msg->content }}</td>
                                                    <td><small>{{ verta($msg->created_at)->format('Y/m/d H:i') }}</small></td>
                                                    <td>
                                                        @if($msg->status === 'delivered' || $msg->delivery_status === 'delivered')
                                                            <span class="badge badge-success px-2 py-1">تحویل شد</span>
                                                        @elseif($msg->status === 'failed')
                                                            <span class="badge badge-danger px-2 py-1">ناموفق</span>
                                                        @else
                                                            <span class="badge badge-warning text-dark px-2 py-1">{{ $msg->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <i class="fa fa-shield fa-2x mb-2 text-muted"></i>
                                                        <p class="mb-0">هیچ پیام محرمانه‌ای برای شما ثبت نشده است.</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning py-4 text-center">
                                    <i class="fa fa-lock fa-3x mb-2 text-warning"></i>
                                    <h5 class="fw-bold">دسترسی محدود شده است</h5>
                                    <p class="mb-0 text-muted">مشاهده پیام‌های محرمانه نیازمند سطح دسترسی مجاز در سامانه می‌باشد.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
