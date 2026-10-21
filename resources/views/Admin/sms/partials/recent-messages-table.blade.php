{{-- Latest messages with forward («ارجاع») action. Param: $recentMessages. --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>شناسه</th>
                    <th>گیرنده</th>
                    <th>سرشماره</th>
                    <th>نوع</th>
                    <th>بخشی از متن</th>
                    <th>تعداد صفحه</th>
                    <th>هزینه (ریال)</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentMessages as $msg)
                    <tr>
                        <td>#{{ $msg->id }}</td>
                        <td>
                            <span dir="ltr"><strong>{{ $msg->recipient_phone }}</strong></span>
                            @if($msg->user)
                                <div class="text-muted small">{{ $msg->user->name }} {{ $msg->user->family }}</div>
                            @endif
                        </td>
                        <td><span dir="ltr">{{ $msg->sender_phone }}</span></td>
                        <td>
                            <span class="badge {{ $msg->service_type === 'otp' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ \App\Support\SmsLabels::panel($msg->service_type) }}
                            </span>
                        </td>
                        <td style="max-width: 280px;" class="text-truncate" title="{{ ($msg->content_redacted ?? false) ? 'محرمانه - دسترسی محدود' : $msg->content }}">
                            @if($msg->content_redacted ?? false)
                                <span class="text-muted"><i class="fa fa-lock me-1"></i> محرمانه - دسترسی محدود</span>
                            @else
                                {{ $msg->content }}
                            @endif
                        </td>
                        <td>{{ $msg->page_count ?: 1 }}</td>
                        <td>{{ $msg->cost !== null ? number_format((float) $msg->cost) : '-' }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ \App\Support\SmsLabels::status($msg->status) }}</span>
                        </td>
                        <td class="small text-muted">{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary forward-msg-btn"
                                data-msg-id="{{ $msg->id }}"
                                data-msg-content="{{ ($msg->content_redacted ?? false) ? '' : e($msg->content) }}"
                                data-msg-redacted="{{ ($msg->content_redacted ?? false) ? '1' : '0' }}"
                                data-msg-type="{{ $msg->service_type }}"
                                data-bs-toggle="modal" data-bs-target="#forwardDirectModal">
                                <i class="fa fa-share me-1"></i> ارجاع به مخاطب
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
