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
            {{-- print view: no form, no save --}}
            @include('Admin.factoritem.layouts.formItem', ['formItemUploads' => false])
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
                    {{-- <a href="{{ \App\Models\User::getUser($factorItem['factor_values'][$form->name])->imgsignature }}"
                        target="_blank">
                        <img src="{{ \App\Models\User::getUser($factorItem['factor_values'][$form->name])->imgsignature }}"
                            class="card-img-top" height="150px">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">{{
                            \App\Models\User::getUser($factorItem['factor_values'][$form->name])->family.' -
                            '.$form->help }}</h5>
                    </div> --}}
                    @else
                    <img class="card-img-top" height="150px">
                    <div class="card-body">
                        <h5 class="card-title">{!! $form->help !!}</h5>
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
                            <a href="{{ $doctor->imgsignature }}" target="_blank">
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
            @case('rules')
            <div class="col-12">
                <h4>مفاد قرارداد : </h4>
                {!! $form->help !!}
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
        <div class="col-4">
            <div class="card border-contract p-2 text-center">
                {{-- truthiness, not isset(): imgsignature is an accessor returning '' when there
                     is no image, and isset('') is true. --}}
                @if($user->imgsignature)
                <a href="{{ $user->imgsignature }}" target="_blank">
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
