<div class="card border border-secondary col-12 mt-5 pt-4 col-l main ">
    <div class="card-body">
        <div class="col-md-12">
                @foreach($item['forms'] as $form)
                @switch($form->type)
                @case('factor_dentist')
                    <div class="card-header">
                        <h4>{{$form->help}}</h4>
                    </div>
                <table class="table table-bordered table-striped table-responsive-md">
                    <tr>
                        <th>وضعیت اجرا</th>
                        <th>نوع خدمت</th>
                        <th>شماره دندان</th>
                        <th>نام پزشک</th>
                        <th>تعداد جلسات درمان</th>
                        <th>طول درمان(روز)</th>
                        <th>تخفیف (ریال)</th>
                        <th>مبلغ (ریال)</th>
                    </tr>
                    @foreach($item['factor_Item_Service_tariffs'] as $factor_item_service_tariff)
                        <tr>
                            <td>
                                {!! \App\Helpers\Helper::iconIsItOrNot($factor_item_service_tariff->status) !!}
                            </td>
                            <td>
                                {{$factor_item_service_tariff->serviceTariff->name}} ( {{ number_format($factor_item_service_tariff->serviceTariff->price) }} ریال - تعرفه {{
                                    $factor_item_service_tariff->serviceTariff->tariff->title }} )
                            </td>
                            <td>
                                <p class="text-nowrap">{{ $factor_item_service_tariff->value }}</p>
                            </td>

                            <td>
                                {{isset($factor_item_service_tariff->user)?$factor_item_service_tariff->user->name.' '.$factor_item_service_tariff->user->family:''}}

                            </td>
                            <td>
                                <span>{{ $factor_item_service_tariff->session_number }}</span>
                            <td>
                                <span>{{ $factor_item_service_tariff->treatment_duration }}</span>
                            </td>
                            <td>
                                <span>{{ number_format($factor_item_service_tariff->discount) }}</span></td>
                            <td>{{ number_format($factor_item_service_tariff->price - $factor_item_service_tariff->discount) }}
                            </td>
                        </tr>
                    @endforeach
                </table>
{{--                <button class="btn btn-success px-3">ویرایش<i class="fa fa-edit" aria-hidden="true"></i></button>--}}
                <div class="badge badge-primary font-size-13 mb-3 p-2">جمع فاکتور بدون اعمال تخفیف
                    : {{ number_format($item['detail']['sum_factor_item_service_tariff_price']) }}
                    ریال
                </div>
                @break
                @endswitch
                @endforeach
        </div>
    </div>
</div>
<script>
    @if(session()->get('factorItem_update')=='success')
    toastr.success('با موفقیت انجام شد!');
    @endif
</script>
