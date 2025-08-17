<h4 class="mb-3 card-header"><i class="fas fa-list"></i> طرح درمان </h4>
<table class="table table-bordered table-striped table-responsive-md">
    <tr>
        <th>#</th>
        <th>وضعیت اجرا</th>
        <th>نوع خدمت</th>
        <th>شماره دندان</th>
        <th>نام پزشک</th>
        <th>تعداد جلسات درمان</th>
        <th>طول درمان(روز)</th>
        <th>تخفیف (ریال)</th>
        <th>مبلغ (ریال)</th>
        <th>تاریخ ثبت</th>
        <th>حذف</th>
    </tr>
    @php($factorItem['service_tariffs']=$factorItem['service_tariffs']->sortBy('pivot.value'))
    @foreach ($factorItem['service_tariffs'] as $service_tariff)
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
            <p class="text-nowrap">{{ $service_tariff->pivot->value }}</p>
        </td>
        <td>
            <select class="form-control" name="user_id[]">
                <option value="">بدون پزشک</option>
                @foreach ($center_agents as $doctor)
                <option value="{{ $doctor->id }}" {{ $doctor->id == $service_tariff->pivot->user_id ?'selected' : '' }}
                    >
                    {{ $doctor->name }} {{ $doctor->family }}
                </option>
                @endforeach
            </select>
        </td>

        <input type="hidden" value="{{ $service_tariff->pivot->id }}" name="id[]" class="form-control">
        <td><input type="number" value="{{ $service_tariff->pivot->session_number }}" name="session_number[]"
                class="form-control" min="1"></td>
        <td><input type="number" value="{{ $service_tariff->pivot->treatment_duration }}" name="treatment_duration[]"
                class="form-control" min="1"></td>
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
</table>
<button class="btn btn-success px-3">ویرایش<i class="fa fa-edit" aria-hidden="true"></i>
</button>
<div class="badge badge-primary font-size-13 mb-3 p-2">جمع فاکتور بدون اعمال تخفیف
    : {{ number_format($factorItem->serviceTariffs()->sum('factor_item_service_tariff.price')) }}
    ریال
</div>
