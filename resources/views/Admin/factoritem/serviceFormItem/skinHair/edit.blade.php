<h4 class="mb-3 card-header"><i class="fas fa-list"></i> طرح درمان </h4>
<table class="table table-bordered table-striped table-responsive-md">
    <tr>
        <th>ردیف</th>
        <th>میزان انجام کار</th>
        <th>عنوان</th>
        <th>توضیحات</th>
        <th>مقدار(بر اساس سی سی - واحد)</th>
        <th>تعداد مراجعات </th>
        <th>طول دوره اجرا(روز)</th>
        <th>تخفیف (ریال)</th>
        <th>تعرفه مصرف کننده (ریال)</th>
        @permission('view_profit_factorItem')
        <th>تعرفه خرید سجا (ریال)</th>
        <th>نرخ اختلاف خرید</th>
        @endpermission
        <th>تاریخ ثبت</th>
        <th>جزئیات</th>
    </tr>
    @foreach ($factorItem->serviceTariffs as $key => $service_tariff)
    <tr>
        <td>{{ $loop->iteration }}</td>
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
        <td>
            <select class="form-control" name="service_tariff_id[]">
                @foreach ($center_service_tariffs as $temp)
                <option value="{{ $temp->id }}" {{ $temp->id == $service_tariff->id ? 'selected' : ''}}>
                    {{ $temp->name }} ( {{ number_format($temp->price) }} ریال -
                    تعرفه {{ $temp->tariff->title }} )
                </option>
                @endforeach
            </select>
            <p class="text-danger">{{ isset($message[$service_tariff->pivot->id]) ? $message[$service_tariff->pivot->id]
                : ''}}</p>
        </td>
        <td>
            <input type="text" value="{{ $service_tariff->pivot->description }}" name="description[]"
                class="form-control" maxlength="100">
        </td>
        <td>
            <input type="number" value="{{ $service_tariff->pivot->value }}" name="value[]" class="form-control"
                min="1" />
        </td>

        <input type="hidden" value="{{ $service_tariff->pivot->id }}" name="id[]" class="form-control" />
        <td>
            <input type="number" value="{{ $service_tariff->pivot->session_number }}" name="session_number[]"
                class="form-control" min="1" />
        </td>
        <td>
            <input type="number" value="{{ $service_tariff->pivot->treatment_duration }}" name="treatment_duration[]"
                class="form-control" min="1" />
        </td>
        <td>
            <input type="text" value="{{ number_format($service_tariff->pivot->discount) }}" name="discount[]"
                class="form-control" oninput="separatePrices(this)">
        </td>
        <td>
            @if ($factorItem['factoritemable']->is_change_factor_price)
            <input type="text"
                value="{{ number_format($service_tariff->pivot->price - $service_tariff->pivot->discount) }}"
                name="price[]" class="form-control" oninput="separatePrices(this)">
            @else
            {{ number_format($service_tariff->pivot->price - $service_tariff->pivot->discount) }}
            @endif
        </td>
        @permission('view_profit_factorItem')
        <td>{{ number_format($service_tariff->purchase_price*$service_tariff->pivot->value) }}</td>
        <td>
            {{ App\Models\CenterServiceTariff::setCompanyProfit($service_tariff , $service_tariff->pivot->price -
            $service_tariff->pivot->discount) }}
        </td>
        @endpermission
        <td>{{
            verta($service_tariff->pivot->created_at)->format('Y/m/d
            H:i:s') }}</td>
        <td>
            <a class="factor_item_service_tariff_delete" data-id="{{ $service_tariff->pivot->id }}">
                <i class="fa fa-trash font-size-18" aria-hidden="true"></i>
            </a>
        </td>
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
            number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.price'))
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
        <th></th>
    </tr>
</table>
<div class="col-12 mb-2">
    <button class="btn btn-success px-3">ویرایش<i class="fa fa-edit" aria-hidden="true"></i>
    </button>
</div>
<div class="row">
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
