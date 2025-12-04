<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#factors" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion">فاکتور</a>
        </h5>
    </div>
    <div id="factors" class="panel-collapse collapse in">
        <div class="panel-body">
            <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                @foreach ($items as $key => $factor_item)
                <li class="nav-item">
                    <a class="nav-link show {{ $key == 0 ? 'active' : ''}}" id="factoritem-{{ $key }}-tab"
                        data-toggle="tab" href="#factoritem-{{ $key }}" role="tab" aria-controls="factoritem-{{ $key }}"
                        aria-selected="false">{{
                        $factor_item['factoritemable']->title }}</a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content border p-4" id="myTabContent">
                @foreach ($items as $key => $factor_item)
                <div class="tab-pane fade p-t-20 {{ $key == 0 ? 'show active' : ''}} " id="factoritem-{{ $key }}"
                    role="tabpanel" aria-labelledby="factoritem-{{ $key }}-tab">
                    @if (!is_null($factor_item['centers']) || !is_null($factor_item['forms']))
                    @foreach ($factor_item['factorItems'] as $factorItem)
                    <div class="row col-12 col-md-12 mb-3">
                            <h5>{{ $factorItem['factoritemable']->title . ($factorItem['center'] ? ' (
                                '.$factorItem['center']->title.' ) ' : '').' - '. $factor_item['financial_plans_type']
                                }}</h5>
                    </div>
                    <div class="card-header row col-12 mb-4">
                        <div class="col-12 col-md-3">
                        @if(!$factorItem['total_price'] || $factorItem['status_factor'] == 14 || $factorItem['status_factor'] == 4)
                        <a class="btn border border-secondary box-shadow-info"
                                data-toggle="modal" data-target="#changeFactoritemableModal"><i
                            class="fa fa-edit"></i> تغییر خدمت | دسته بندی</a>
                            @include('Admin.factoritem.layouts.file.changeFactorItemAble')
                        @endif
                        </div>
                        <div class="col-12 col-md-3">
                            <a class="btn border border-secondary box-shadow-info" data-toggle="modal"
                                data-target="#changeFinancialPlansTypeModal"><i class="fa fa-edit"></i> تغییر طرح</a>
                        </div>
                        <div class="col-12 col-md-3">
                            <a class="btn border border-secondary box-shadow-info"
                                href="../../dashboard/factorItem/{{ $factorItem['resnumber'] }}/edit"><i
                                    class="fa fa-edit"></i> امور اجرایی پرونده</a>
                        </div>
                    </div>
                    <div class="row p-3 position-relative">
                        @if (!is_null($factor_item['forms']))
                        @foreach ($factor_item['forms'] as $form)
                        @if (in_array('panel_show_file' , $form->data_display_level))
                        @include('Admin.factoritem.layouts.formItem')
                        @endif
                        @endforeach
                        @endif
                        <div class="success-overly position-absolute mx-3"></div>
                    </div>
                    @endforeach
                    @else
                    <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                        <div>
                            @if (!is_null($factor_item['service_item']))
                            <span>{{ $factor_item['factoritemable']->title . '-' . $factor_item['service_item']->title
                                }}</span>
                            @else
                            <span>{{ $factor_item['factoritemable']->title }}</span>
                            @endif
                        </div>
                        <i class="fa fa-check font-18 text-success"></i>
                    </div>
                    @endif
                    <form class="form-horizontal" role="form" method="post"
                        action="{{ $factor_item['total_price'] == 0 ? route('factorItem.createFactor', [ $factorItem['resnumber']]) : route('factorItem.calculate') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $factor_item['id'] }}">
                        <input type="hidden" name="item[id]" value="{{ $factor_item['id'] }}">
                        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">

                        <div class="row">
                            <div class="col-12 col-md-3 mb-2">
                                <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                                <input class="form-control" value="{{ number_format($factor_item['total_price'] +
                                    (isset($factor_item['price_difference']['total']) ?
                                    $factor_item['price_difference']['total'] : 0)) }}" name="totalPrice"
                                    oninput="separatePrices(this)">
                            </div>
                            @if($item['confirm'])
                            <div class="col-12 col-md-3 mb-2" data-toggle="tooltip" data-placement="bottom"
                                title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
                                <label>مبلغ کل اعتبار پرونده(ریال)</label>
                                <p class="form-control border-success">
                                    {{ number_format($factor_item['total_price']) }} </p>
                            </div>
                            @endif
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ تخفیف</label>
                                <input class="form-control" value="{{ number_format($factor_item['discount']) }}"
                                    name="discount" type="text" oninput="separatePrices(this)">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ اختلاف حساب</label>
                                <p class="form-control">
                                    {{ isset($factor_item['price_difference']['total']) ?
                                    number_format($factor_item['price_difference']['total']) : 0 }}
                                </p>
                            </div>

                            <div class="col-12 col-md-3 mb-2">
                                <label>تعداد اقساط</label>
                                <input class="form-control" value="{{ $factor_item['installments_num'] }}"
                                    name="installments_num" type="number">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ اقساط(ریال)</label>
                                <p class="form-control">
                                    {{ number_format($factor_item['installments_price']) }}
                                </p>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>پیش پرداخت(ریال)</label>
                                <input class="form-control"
                                    value="{{ number_format($factor_item['prepayment']['price']) }}" name="prepayment"
                                    type="text" oninput="separatePrices(this)">
                            </div>
                            <div class="col-12 mb-3">
                                <p class="alert alert-danger">همکار گرامی توجه داشته باشید ، درصورتی که فاکتوری از قبل
                                    برای پرونده ثبت شده باشد امکان تغییر جمع کل فاکتور بصورت دستی وجود ندارد.</p>
                            </div>
                            <div class="col-12 col-md-12 mb-3">
                                <label> نوع پرداخت</label>
                                <br>
                                نقد <input type="radio" name="type" value="0" {{ $factor_item['type']=='0' ? 'checked'
                                    : '' }}>
                                اقساط<input type="radio" name="type" value="1" {{ $factor_item['type']=='1' ? 'checked'
                                    : '' }}>
                            </div>
                        </div>
                        @permission('financial-info-file-edit')
                        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm']
                            ? 'disabled' : '' }}>
                        @endpermission
                    </form>
                </div>
                @include('Admin.factoritem.layouts.file.changeFinancialPlansType')
                @endforeach
            </div>
            <div class="card col-12 mt-3 pt-4 col-l main border bg-light">
                <h4 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> اطلاعات مالی پرونده کل
                </h4>
                <form class="form-horizontal" role="form" method="post"
                    action="{{ route('totalFactorItem.calculate') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item['id'] }}">
                    <div class="row">
                        <div class="col-12 col-md-3 mb-2">
                            <label>جمع کل با اعمال تخفیف(ریال)</label>
                            <p class="form-control">
                                {{ number_format($item['total_price'] +
                                (isset($item['price_difference']['total']) ?
                                $item['price_difference']['total'] : 0)) }} </p>
                        </div>
                        @if($item['confirm'])
                        <div class="col-12 col-md-3 mb-3" data-toggle="tooltip" data-placement="bottom"
                            title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
                            <label>مبلغ کل اعتبار پرونده(ریال)</label>
                            <p class="form-control border-success">
                                {{ number_format($item['total_price']) }} </p>
                        </div>
                        @endif
                        <div class="col-12 col-md-3 mb-2">
                            <label>مبلغ تخفیف</label>
                            <p class="form-control">{{ number_format($item['discount']) }}</p>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label>مبلغ اختلاف حساب</label>
                            <p class="form-control">
                                {{ isset($item['price_difference']['total']) ?
                                number_format($item['price_difference']['total']) : 0 }}
                            </p>
                        </div>

                        <div class="col-12 col-md-3 mb-2">
                            <label>تعداد اقساط</label>
                            <p class="form-control">{{ $item['installments_num'] }}</p>
                        </div>

                        <div class="col-12 col-md-3 mb-2">
                            <label>پیش پرداخت(ریال)</label>
                            <p class="form-control">{{ number_format($item['prepayment']['price']) }}</p>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label>تعداد مراحل پرداخت پیش پرداخت</label>
                            <input class="form-control"
                                value="{{ isset($item['prepayment']['num']) ? $item['prepayment']['num'] : 1 }}"
                                name="prepayment_num" type="number" max="10" min="0">
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label>درصد جریمه روزانه</label>
                            <input class="form-control"
                                value="{{ isset($item['percent_fines']) ? $item['percent_fines'] : 2 }}"
                                name="percent_fines" type="number" min="2">
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label>تاریخ تشکیل پرونده</label>
                            <date-picker value="{{ $item['approval_date'] }}" format="YYYY/M/D HH:mm:ss"
                                name="created_at" type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="new_date">
                            </date-picker>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label>مبلغ ضمانت مورد نیاز</label>
                            <p class="form-control date mb-2">{{ $item['guarantee_price'] }}</p>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نوع ضمانت</label>
                            <br>
                            @foreach ($item['guarantee_types_list'] as $key => $guarantee_type)
                            {{ $totalFactorItem::GuaranteeTYPE[$guarantee_type] }} <input type="radio"
                                name="guarantee_type" value="{{ $guarantee_type }}" {{
                                $item['guarantee_type']==$guarantee_type ? 'checked' : '' }}>
                            @endforeach
                        </div>
                    </div>
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm'] ? 'disabled'
                        : '' }}>
                </form>
            </div>
        </div>
    </div>
</div>
