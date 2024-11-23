<div class="card-header row col-12 col-md-12 mb-3">
    <div class="col-md-10">
        <h5>{{ $factorItem['factoritemable']->title . ($factorItem['center'] ? ' (
            '.$factorItem['center']->title.' ) ' : '') }}</h5>
    </div>
    <div class="col-md-2">
        <a class="btn border border-secondary mr-3" onclick="printDiv('printableArea')"><i class="fa fa-print ml-1"></i>
            پرینت فرم </a>
    </div>
</div>
<div id="printableArea" class="bg-white">
    <div class="border-contract pr-5 pb-3 mb-3">
        <div class="row">
            <div class="col-md-3 mt-4">
                <p> تاریخ : {{ verta(date("Y/m/d H:i"))->format('%d / %m / %Y - H:i') }} </p>
                <p> شماره پرونده : {{ $factorItem['file_number'] }} </p>
                <p> شماره فاکتور : {{ $factorItem['resnumber'] }} </p>
            </div>
            <div class="col-md-6 mt-5 text-center">
                <h3> خدمات رفاهی لاله</h3>
                <h5>{{ $factorItem['center'] ? $factorItem['center']->title : '' }}</h5>
            </div>
            <div class="col-md-3 mt-3">
                <img src="{{ asset('assets/dashboard/images/laleh_icon.png') }}" width="150px">
            </div>
        </div>
    </div>
    <div class="border-contract p-4">
        @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
        <div class="row p-3 position-relative font-size-14 font-weight-bold">
            @if(!is_null($factorItem['forms']))
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_print_form' , $form->data_display_level))
            @switch($form->type)
            @case('number')
            <div class="col-12 col-md-3 mb-1">
                <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                <input type="number" class="form-print" placeholder="{{ $form->placeholder }}"
                    value="{{ $user[$form->name] }}" disabled>
            </div>
            @break
            @case('text')
            <div class="col-3 mb-1">
                <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                <input type="text" class="form-print" placeholder="{{ $form->placeholder }}"
                    value="{{ $user[$form->name] }}" disabled>
            </div>
            @break
            @case('textarea')
            <div class="col-12 col-md-12 mb-1">
                <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                <input type="text" class="form-print"
                    value="{{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : '' }}"
                    disabled>
            </div>
            @break
            @case('checkbox')
            <div class="col-12 col-md-12 mb-3">
                {{ $form->help }} :
                <br>
                @foreach($form->beforeformitems as $beforeformitem)
                {{ $beforeformitem->title }}
                @if(isset($factorItem['factor_values'][$form->name]))
                <input type="{{ $form->type }}" name="item[{{ $form->name }}][]" value="{{ $beforeformitem->value }}" {{
                    in_array($beforeformitem->value , $factorItem['factor_values'][$form->name]) ? 'checked' : '' }}>
                @else
                <input type="{{ $form->type }}" name="item[{{ $form->name }}][]" value="{{ $beforeformitem->value }}">
                @endif
                @endforeach
            </div>
            @break
            @case('factor_dentist')
            <div class="row col-md-12 mb-3 arrow-holder ">
                <div class="margin-change-box border border-dark rounded">
                    <span class="margin-plus" onclick="marginPlus(this)">(<i class="fa fa-arrow-down"
                            aria-hidden="true"></i>) </span>
                    <span class="margin-minus" onclick="marginMinus(this)">(<i class="fa fa-arrow-up"
                            aria-hidden="true"></i>) </span>
                </div>
            </div>

            <div class="row col-md-12 mb-3  ">
                <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                <table class="table table-bordered table-striped table-responsive-md">
                    <tr>
                        <th>نوع خدمت</th>
                        <th>شماره دندان</th>
                        <th>تعداد جلسات درمان</th>
                        <th>طول درمان(روز)</th>
                        <th>تخفیف (ریال)</th>
                        <th>مبلغ (ریال)</th>
                    </tr>
                    @foreach($factorItem['service_tariffs'] as $service_tariff)
                    <tr>
                        <td>{{ $service_tariff->name }}</td>
                        <td>{{ $service_tariff->pivot->value }}</td>
                        <td>{{ $service_tariff->pivot->session_number }}</td>
                        <td>{{ $service_tariff->pivot->treatment_duration }}</td>
                        <td>{{ number_format($service_tariff->pivot->discount) }}
                        </td>
                        <td>{{ number_format($service_tariff->pivot->price -
                            $service_tariff->pivot->discount) }}
                            ریال
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="col-12 mb-2">
                    <div class="btn badge-success mb-3">
                        جمع فاکتور بدون اعمال تخفیف
                        :
                        {{ number_format($factorItem['service_tariffs']->sum('price')) }}
                        ریال
                    </div>
                </div>
            </div>
            <div class="row col-md-12 mb-3 arrow-holder ">
                <div class="margin-change-box border border-dark rounded">
                    <span class="margin-plus" onclick="marginPlus(this)">(<i class="fa fa-arrow-down"
                            aria-hidden="true"></i>) </span>
                    <span class="margin-minus" onclick="marginMinus(this)">(<i class="fa fa-arrow-up"
                            aria-hidden="true"></i>) </span>
                </div>
            </div>

            <div class="row col-md-12 mb-3">
                <label>جلسات</label>
                <table class="table table-bordered table-striped table-responsive-md">
                    <tr>
                        <th>تاریخ و ساعت</th>
                        <th>شماره دندان</th>
                        <th>توضیحات</th>
                        <th>پزشک</th>
                        <th>دستیار</th>
                        <th>مبلغ (ریال)</th>
                        <th>ثبت کننده</th>
                    </tr>
                    @foreach($factorItem['sessions'] as $session)
                    <tr>
                        <td>{{ verta($session->date)->format('%d %B ، %Y - H:i:s') }}</td>
                        <td>
                            @foreach($factorItem['service_tariffs'] as $service_tariff)
                            {{ $session->factor_item_service_tariff_id == $service_tariff->pivot->id ?
                            $service_tariff->pivot->value: '' }}
                            @endforeach
                        </td>
                        <td>{{ $session->description }}</td>
                        <td>
                            @if(isset($session->agent))
                            {{ $session->agent->name }} {{ $session->agent->family }}
                            @endif
                        </td>
                        <td>
                            @if(isset($session->assistant))
                            {{ $session->assistant->name }} {{ $session->assistant->family }}
                            @endif
                        </td>
                        <td>
                            {{ number_format($session->price) }}
                        </td>
                        <td>
                            {{ $session->user->family }}
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="col-12 mb-2">
                    <div class="btn badge-success mb-3">
                        جمع مبلغ جلسات
                        :
                        {{ number_format($factorItem['sessions']->sum('price')) }}
                        ریال
                    </div>
                </div>
            </div>
            <div class="row col-md-12 mb-3 arrow-holder ">
                <div class="margin-change-box border border-dark rounded">
                    <span class="margin-plus" onclick="marginPlus(this)">(<i class="fa fa-arrow-down"
                            aria-hidden="true"></i>) </span>
                    <span class="margin-minus" onclick="marginMinus(this)">(<i class="fa fa-arrow-up"
                            aria-hidden="true"></i>) </span>
                </div>
            </div>
            @break
            @case('factor_service')
            <div class="col-12 col-md-12 mb-3">
                {{ $form->help }}
            </div>
            <div class="row col-md-12">
                <table class="table table-bordered table-striped table-responsive-md">
                    @foreach ($factorItem['service_tariffs'] as $service_tariff)
                    <tr>
                        <td>{{ $service_tariff->name }}</td>
                        <td> {{ number_format($service_tariff->pivot->price) }}
                            ریال
                        </td>
                    </tr>
                    @endforeach

                </table>
            </div>
            @break
            @case('other-price')
            @if(isset($factorItem['factor_values'][$form->name]))
            <div class="col-12 col-md-4 mb-2">
                <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                <p class="form-control {{ $form->class }}" name="item[{{ $form->name }}]">{{
                    number_format($factorItem['factor_values'][$form->name]) }}</p>
            </div>
            @endif
            @break
            @case('radio')
            <div class="col-12 col-md-12 mb-3">
                {{ $form->help }} :
                <br>
                @foreach($form->beforeformitems as $beforeformitem)
                {{ $beforeformitem->title }}
                @if(isset($factorItem['factor_values'][$form->name]))
                <input type="{{ $form->type }}" name="item[{{ $form->name }}]" value="{{ $beforeformitem->value }}" {{
                    $beforeformitem->value == $factorItem['factor_values'][$form->name] ? 'checked' : '' }} required>
                @else
                <input type="{{ $form->type }}" name="item[{{ $form->name }}]" value="{{ $beforeformitem->value }}"
                    required>
                @endif
                @endforeach
            </div>
            @break
            @case('rules')
            <div class="col-12">
                <h4>مفاد قرارداد : </h4>
                {!! $form->value !!}
                <p>توجه : متقاضی محترم جهت بازدید و بررسی پرونده خدمات دریافتی فوق و هرگونه ارتباط با کادر {{
                    $factorItem['center'] ? $factorItem['center']->title : '' }} می توانید از طریق پیامک ارسال شده با
                    نام کاربری و
                    رمز عبور ارسال شده اقدام نمائید. </p>
            </div>
            @break
            @endswitch
            @endif
            @endforeach
            @endif
            <div class="success-overly position-absolute mx-3"></div>
        </div>
        @endif
        <div class="card col-12 mt-2 pt-4 col-l main border">
            <h4 class="mb-3 card-header">اطلاعات مالی (نوع پرداخت : {{ $factorItem['type'] == 0 ? 'نقد' : 'اقساط' }})
            </h4>
            <div class="row">
                @if ($factorItem['type'] == 1 && $factorItem['confirm'])
                <div class="col-3 mb-2">
                    <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                    <p class="form-control">
                        {{ number_format($item['total_price'] +
                        (isset($item['price_difference']['total']) ?
                        $item['price_difference']['total'] : 0)) }} </p>
                </div>
                <div class="col-3 mb-2" data-toggle="tooltip" data-placement="bottom">
                    <label>مبلغ کل اعتبار پرونده(ریال)</label>
                    <p class="form-control border-success">
                        {{ number_format($item['total_price']) }} </p>
                </div>
                @else
                <div class="col-3 mb-2">
                    <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                    <p class="form-control">
                        {{ number_format($item['total_price']) }} </p>
                </div>
                @endif
                @if($factorItem['type'] == 1)
                <div class="col-2 mb-2">
                    <label>تعداد اقساط</label>
                    <p class="form-control">{{$item['installments_num']}}</p>
                </div>

                <div class="col-2 mb-2">
                    <label>پیش پرداخت(ریال)</label>
                    <p class="form-control">{{number_format($item['prepayment']['price'])}}</p>
                </div>

                <div class="col-3 mb-2">
                    <label>تعداد مراحل پرداخت پیش پرداخت</label>
                    <p class="form-control">{{ isset($item['prepayment']['num']) ? $item['prepayment']['num'] : 1}}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
        <div class="row p-3 position-relative font-size-14 font-weight-bold">
            @if(!is_null($factorItem['forms']))
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_print_form' , $form->data_display_level))
            @switch($form->type)
            @case('confirm')
            <div class="col-3">
                <div class="card border-contract p-2 text-center">
                    @if(isset($factorItem['factor_values'][$form->name]) && $factorItem['factor_values'][$form->name] !=
                    "")
                    <a href="{{ asset(\App\Models\User::getUser($factorItem['factor_values'][$form->name])->imgsignature) }}"
                        target="_blank">
                        <img src="{{ \App\Models\User::getUser($factorItem['factor_values'][$form->name])->imgsignature }}"
                            class="card-img-top" height="150px">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">{{
                            \App\Models\User::getUser($factorItem['factor_values'][$form->name])->family.' -
                            '.$form->help }}</h5>
                    </div>
                    @else
                    <img class="card-img-top" height="150px">
                    <div class="card-body">
                        <h5 class="card-title">{{ $form->help }}</h5>
                    </div>
                    @endif
                </div>
            </div>
            @break
            @case('multi-select')
            <div class="col-12">
                @if($form->name == 'doctor')
                <p>نام پزشک مشاور: </p>
                <div class="row mb-3">
                    @foreach($doctors as $doctor)
                    @if(isset($factorItem['factor_values']['doctor']))
                    @if(in_array( $doctor->id, $factorItem['factor_values']['doctor']))
                    <div class="col-12 col-md-3">
                        <div class="card border-contract p-2 text-center">
                            @if($doctor->imgsignature)
                            <a href="{{ asset($doctor->imgsignature) }}" target="_blank">
                                <img src="{{ $doctor->imgsignature }}" class="card-img-top" height="150px">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title">{{ $doctor->name.' '.$doctor->family }}</h5>
                            </div>
                            @else
                            <img class="card-img-top" height="150px">
                            <div class="card-body">
                                <h5 class="card-title">{{ $doctor->name.' '.$doctor->family }}</h5>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endforeach
                </div>
                @else
                @foreach($form->beforeformitems as $select_item)
                <p value="{{ $select_item->value }}" {{ $user[$form->name] == $select_item->value ? 'selected' : ''
                    }}>{{ $select_item->title }}</p>
                @endforeach
                @endif
            </div>
            @break
            @endswitch
            @endif
            @endforeach
            @endif
            <div class="success-overly position-absolute mx-3"></div>
        </div>
        @endif
        <div class="col-4">
            <div class="card border-contract p-2 text-center">
                @if(isset($user->imgsignature))
                <a href="{{ asset($user->imgsignature) }}" target="_blank">
                    <img src="{{ $user->imgsignature }}" class="card-img-top" height="150px">
                </a>
                <div class="card-body">
                    <h4 class="col-12 card-title">امضا/ تاریخ /اثرانگشت خدمات گیرنده</h4>
                </div>
                @else
                <img class="card-img-top" height="150px">
                <div class="card-body">
                    <h4 class="col-12 card-title">امضا/ تاریخ /اثرانگشت خدمات گیرنده</h4>
                </div>
                @endif
            </div>
        </div>
        <div class="row col-md-12 mb-3 arrow-holder ">
            <div class="margin-change-box border border-dark rounded">
                <span class="margin-plus" onclick="marginPlus(this)">(<i class="fa fa-arrow-down"
                        aria-hidden="true"></i>) </span>
                <span class="margin-minus" onclick="marginMinus(this)">(<i class="fa fa-arrow-up"
                        aria-hidden="true"></i>) </span>
            </div>
        </div>
        <div></div>
    </div>
</div>
