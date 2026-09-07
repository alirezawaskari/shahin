{{--
    The single contract block shown in registration invoices, purchase invoices and
    the employees section, so the three cannot drift apart.

    Expects:
      $usage         ?ContractUsage  the resolved association (carries number + date)
      $contract      ?Contract       optional override; defaults to the usage's contract
      $contractBody  ?string         optional pre-rendered html (a confirmed invoice
                                     passes its contract_views snapshot so an edited
                                     bank contract never rewrites a signed one)
      $exportParams  ?array          context for the export link (factor_item_id, …)
      $title         ?string         heading override
--}}
@php
    $contract = $contract ?? optional($usage)->contract;
    $exportParams = $exportParams ?? [];
    $body = $contractBody ?? optional($contract)->content;
    $exportUrl = $contract
        ? '/dashboard/contract/' . $contract->id . '/export' . ($usage ? '/' . $usage->id : '')
            . (count($exportParams) ? '?' . http_build_query($exportParams) : '')
        : null;
@endphp

{{-- Renders for a snapshot alone too: an invoice confirmed before the contract bank
     existed has contract html but no Contract row, and must not go blank. --}}
@if($contract || $body)
    <div class="card contract-display">
        <div class="card-body">
            <div class="x_title">
                <h4>{{ $title ?? 'قرارداد' }}</h4>
                <div class="clearfix"></div>
            </div>

            <div class="row mb-3">
                @if($contract)
                    <div class="col-12 col-md-4">
                        <strong>عنوان قرارداد:</strong> {{ $contract->title }}
                    </div>
                @endif
                @if($usage && $usage->contract_number)
                    <div class="col-12 col-md-4">
                        <strong>شماره قرارداد:</strong> {{ $usage->contract_number }}
                    </div>
                @endif
                @if($usage && $usage->contract_date)
                    <div class="col-12 col-md-4">
                        <strong>تاریخ قرارداد:</strong> {{ verta($usage->contract_date)->format('Y/m/d') }}
                    </div>
                @endif
            </div>

            @if($body)
                <div class="contract-body border rounded p-3 mb-3">
                    {!! $body !!}
                </div>
            @endif

            @if($exportUrl)
                <a href="{{ $exportUrl }}" target="_blank" class="btn btn-info">خروجی قرارداد</a>
            @endif
            @if($contract && $contract->hasOriginalFile())
                <a href="/dashboard/contract/{{ $contract->id }}/download" class="btn btn-secondary">
                    دانلود فایل اصلی
                </a>
            @endif
        </div>
    </div>
@endif
