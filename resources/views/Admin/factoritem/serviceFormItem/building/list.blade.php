@if(isset($factorItem->serviceTariffs))
<div class="col-12 col-md-12 mb-3">
    <i class="fa fa-list"></i>{{ 'فاکتور - ' .
    $factorItem->factor->user->name . ' ' .
    $factorItem->factor->user->family }}
</div>
<div class="row col-md-12">
    <table class="table table-bordered table-striped table-responsive-md">
        <tr>
            <th>ردیف</th>
            <th>میزان انجام کار</th>
            <th>عنوان</th>
            <th>توضیحات</th>
            <th>مقدار(بر اساس متراژ - عدد)</th>
            <th>تعداد مراجعات </th>
            <th>طول دوره اجرا(روز)</th>
            <th>تخفیف (ریال)</th>
            <th>تعرفه مصرف کننده (ریال)</th>
            <th>تعرفه عرضه به اعضاء لاله (ریال)</th>
            <th>اختلاف خرید اولیه (ریال)</th>
            @permission('view_profit_factorItem')
            <th>تعرفه خرید سجا (ریال)</th>
            <th>نرخ اختلاف خرید</th>
            @endpermission
            <th>تاریخ ثبت</th>
        </tr>
        @foreach ($factorItem->serviceTariffs as $key => $service_tariff)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>
                <ul class="circle work-step"
                    onclick="stepWorkDiagram({{ $service_tariff->pivot->session_number }} , {{ $service_tariff->pivot->id }} , {{ App\Models\Session::where('factor_item_service_tariff_id' , $service_tariff->pivot->id)->where('status' , 1)->count() }})">
                    @for($i = 1 ; $i <= $service_tariff->pivot->session_number ; $i++)
                        <li id="part_{{ $service_tariff->pivot->id.'_'.$i }}">
                            <div class="text"></div>
                        </li>
                        @endfor
                </ul>
                <div class="circle smaller-circle">
                    <p id="percent_{{ $service_tariff->pivot->id }}">
                    <p>
                </div>
            </td>
            <td>{{ $service_tariff->name }}
            </td>
            <td>{{ $service_tariff->pivot->description }}</td>
            <td>{{ $service_tariff->pivot->value }}
            </td>
            <td>{{ $service_tariff->pivot->session_number }}
            </td>
            <td>{{ $service_tariff->pivot->treatment_duration }}
            </td>
            <td>{{ number_format($service_tariff->pivot->discount) }}
            </td>
            <td>{{ number_format($service_tariff->consumer_price) }}</td>
            <td>{{ number_format($service_tariff->pivot->price -
                $service_tariff->pivot->discount) }}
            </td>
            <td>{{ number_format($service_tariff->consumer_price-$service_tariff->pivot->price -
                $service_tariff->pivot->discount) }}</td>
            @permission('view_profit_factorItem')
            <td>{{ number_format($service_tariff->purchase_price) }}</td>
            <td>
                {{ App\Models\CenterServiceTariff::setCompanyProfit($service_tariff ,
                $service_tariff->pivot->price - $service_tariff->pivot->discount) }}
            </td>
            @endpermission
            <td>{{ verta($service_tariff->pivot->created_at)->format('Y/m/d H:i:s') }}</td>
        </tr>
        @endforeach
        <tr class="bg-secondary">
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>{{
                number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.discount')
                - $factorItem->discount)
                }}</th>
            <th>{{
                number_format($factorItem->serviceTariffs()->sum('consumer_price'))
                }}</th>
            <th>{{
                number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.price'))
                }}</th>
            <th>{{
                number_format($factorItem->serviceTariffs()->sum('consumer_price')-$factorItem->serviceTariffs()->sum('factor_item_service_tariff.price'))
                }}</th>
            @permission('view_profit_factorItem')
            <th>{{
                number_format($factorItem->serviceTariffs()->sum('purchase_price'))
                }}</th>
            <th>{{
                App\Models\CenterServiceTariff::setAverageCompanyProfit($factorItem->serviceTariffs ,
                $factorItem->serviceTariffs()->sum('factor_item_service_tariff.price') -
                $factorItem->serviceTariffs()->sum('factor_item_service_tariff.discount')
                - $factorItem->discount )
                }}
                %</th>
            @endpermission
            <th></th>
        </tr>
    </table>
    <div class="mb-2">
        <div class="badge badge-primary font-size-13 mb-3 p-2">
            جمع فاکتور بدون اعمال تخفیف
            :
            {{
            number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.price'))
            }}
            ریال
        </div>
    </div>
    <div class="mb-2 mr-2">
        <div class="badge badge-danger font-size-13 mb-3 p-2">
            جمع تخفیف
            :
            {{
            number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.discount')
            - $factorItem->discount)
            }}
            ریال
        </div>
    </div>
    @permission('view_profit_factorItem')
    <div class="mb-2 mr-2">
        <div class="badge badge-success font-size-13 mb-3 p-2">
            نرخ اختلاف خرید
            :
            {{
            App\Models\CenterServiceTariff::setAverageCompanyProfit($factorItem->serviceTariffs ,
            $factorItem->serviceTariffs()->sum('factor_item_service_tariff.price') -
            $factorItem->serviceTariffs()->sum('factor_item_service_tariff.discount')
            - $factorItem->discount )
            }}
            %
        </div>
    </div>
    @endpermission
</div>
@endif
