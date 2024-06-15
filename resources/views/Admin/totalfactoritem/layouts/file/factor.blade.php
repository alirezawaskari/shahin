<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#factors" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion">فاکتور</a>
        </h5>
    </div>
    <div id="factors" class="panel-collapse collapse in">
        <div class="panel-body">
            <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                @foreach ($items as $key => $factoritem)
                <li class="nav-item">
                    <a class="nav-link show {{ $key == 0 ? 'active' : ''}}" id="factoritem-{{ $key }}-tab"
                        data-toggle="tab" href="#factoritem-{{ $key }}" role="tab" aria-controls="factoritem-{{ $key }}"
                        aria-selected="false">{{
                        $factoritem['factoritemable']->title }}</a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content border p-4" id="myTabContent">
                @foreach ($items as $key => $factoritem)
                <div class="tab-pane fade p-t-20 {{ $key == 0 ? 'show active' : ''}} " id="factoritem-{{ $key }}"
                    role="tabpanel" aria-labelledby="factoritem-{{ $key }}-tab">
                    @if (!is_null($factoritem['centers']) || !is_null($factoritem['forms']))
                    @foreach ($factoritem['factorItems'] as $factor_item)
                    <div class="card-header row col-12 col-md-12 mb-3">
                        <div class="col-md-7">
                            <h5>{{ $factor_item['factoritemable']->title . ($factor_item['center'] ? ' (
                                '.$factor_item['center']->title.' ) ' : '').' - '. $factoritem['financial_plans_type']
                                }}</h5>
                        </div>
                        <div class="col-md-2">
                            <a class="btn border border-secondary mr-3 box-shadow-info" data-toggle="modal"
                                data-target="#changeFinancialPlansTypeModal"><i class="fa fa-edit"></i> تغییر طرح</a>
                        </div>
                        <div class="col-md-3">
                            <a class="btn border border-secondary mr-3 box-shadow-info"
                                href="../../dashboard/factorItem/{{ $factor_item['resnumber'] }}/edit"><i
                                    class="fa fa-edit"></i> امورات اجرایی پرونده</a>
                        </div>
                    </div>
                    <div class="row p-3 position-relative">
                        @if (!is_null($factoritem['forms']))
                        @foreach ($factoritem['forms'] as $form)
                        @if (in_array('panel_show_file' , $form->data_display_level))
                        @switch($form->type)
                        @case('factor_dentist')
                        <div class="col-12 col-md-12 mb-3">
                            <i class="fa fa-list"></i> {{ $form->help . ' - ' .
                            $factor_item->factor->user->name . ' ' .
                            $factor_item->factor->user->family }}
                        </div>
                        <div class="row col-md-12">
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
                                    <th>اقدام</th>
                                </tr>
                                @php($factor_item->serviceTariffs=$factor_item->serviceTariffs->sortBy('pivot.value'))
                                @foreach ($factor_item->serviceTariffs as $key => $service_tariff)
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
                                    <td>{{ $service_tariff->pivot->value }}
                                    </td>
                                    <td>
                                        {{ $service_tariff->pivot->user_id ?
                                        App\Models\User::getUser($service_tariff->pivot->user_id)->name . ' ' .
                                        App\Models\User::getUser($service_tariff->pivot->user_id)->family : 'ندارد'
                                        }}
                                    </td>
                                    <td>{{ $service_tariff->pivot->session_number }}
                                    </td>
                                    <td>{{ $service_tariff->pivot->treatment_duration }}
                                    </td>
                                    <td>{{ number_format($service_tariff->pivot->discount) }}
                                    </td>
                                    <td>{{ number_format($service_tariff->pivot->price -
                                        $service_tariff->pivot->discount) }}
                                        ریال
                                    </td>
                                    <td>{{ verta($service_tariff->pivot->created_at)->format('Y/m/d H:i:s') }}</td>
                                    <td>
                                        <a data-toggle="modal"
                                            data-target="#sessionModal_{{ $service_tariff->pivot->id }}"
                                            class="btn border border-secondary bg-white">جلسات</a>
                                        <div id="sessionModal_{{ $service_tariff->pivot->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myExtraLargeModalLabel"
                                            class="modal fade show" style="padding-right: 17px;" aria-hidden="true">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">جلسات</h5>
                                                        <button type="button" data-dismiss="modal" aria-label="Close"
                                                            class="close"><span aria-hidden="true">×</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div id="status" role="tabpanel" aria-labelledby="status-tab"
                                                            class="tab-pane fade p-t-20 active show">
                                                            <table
                                                                class="table table-bordered table-striped table-responsive-md">
                                                                <tr>
                                                                    @if(!is_null($factor_item->factoritemable->session_attributes) && isset($factor_item->factoritemable->session_attributes['session_titles']))
                                                                    @foreach($factor_item->factoritemable->session_attributes['session_titles'] as $session_title)
                                                                    <th>{{$session_title}}</th>
                                                                    @endforeach
                                                                    @else
                                                                    <th>تاریخ و ساعت</th>
                                                                    <th>شماره دندان</th>
                                                                    <th>درمان</th>
                                                                    <th>توضیحات</th>
                                                                    <th>پزشک</th>
                                                                    <th>دستیار</th>
                                                                    <th>مبلغ (ریال)</th>
                                                                    <th>وضعیت</th>
                                                                    <th>ثبت کننده</th>
                                                                    <th>تایید کننده</th>
                                                                    @permission('session-delete')
                                                                    <th>اقدام</th>
                                                                    @endpermission
                                                                    @endif
                                                                </tr>
                                                                @foreach($factor_item->sessions->where('factor_item_service_tariff_id' , $service_tariff->pivot->id) as $session)
                                                                <tr>
                                                                    <td>{{ verta($session->date)->format('%d %B ، %Y -
                                                                        H:i:s') }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->factorItemServiceTariff ?
                                                                        $session->factorItemServiceTariff->value :
                                                                        'ندارد' }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->factorItemServiceTariff &&
                                                                        $session->factorItemServiceTariff->serviceTariff
                                                                        ?
                                                                        $session->factorItemServiceTariff->serviceTariff->name
                                                                        : 'ندارد' }}
                                                                    </td>
                                                                    <td>{{ $session->description }}</td>
                                                                    <td>
                                                                        @if (isset($session->agent))
                                                                        {{ $session->agent->name }}
                                                                        {{ $session->agent->family }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if (isset($session->assistant))
                                                                        {{ $session->assistant->name }}
                                                                        {{ $session->assistant->family }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        {{ number_format($session->price) }}
                                                                    </td>
                                                                    <td>
                                                                        {{ App\Models\Session::status($session->status)
                                                                        }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->user->family }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->confirmer ?
                                                                        $session->confirmer->family : 'ندارد' }}
                                                                    </td>
                                                                    @permission('session-delete')
                                                                    <td>
                                                                        <div data-v-15965e3b="" class="dropdown dropup">
                                                                            <a href="#" data-toggle="dropdown"
                                                                                aria-haspopup="true"
                                                                                aria-expanded="false"
                                                                                class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                                                                <i aria-hidden="true"
                                                                                    class="fa fa-ellipsis-v"></i>
                                                                            </a>
                                                                            <div class="dropdown-menu">
                                                                                @permission('session-confirm')
                                                                                <form class="confirm_form" method="get"
                                                                                    action="{{ route('session.confirm', [$session->id]) }}">
                                                                                    @csrf
                                                                                    <input type="button"
                                                                                        class="dropdown-item pointer confirm_submit"
                                                                                        id="{{ $session->id }}"
                                                                                        value="تایید و واریز به کیف پول">
                                                                                </form>
                                                                                @endpermission
                                                                                @permission('session-delete')
                                                                                <form class="delete_form" method="post"
                                                                                    action="{{ route('session.destroy', [$session->id]) }}">
                                                                                    @method('DELETE')
                                                                                    @csrf
                                                                                    <input type="button"
                                                                                        class="dropdown-item pointer delete_submit"
                                                                                        id="{{ $session->id }}"
                                                                                        value="حذف">
                                                                                </form>
                                                                                @endpermission
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    @endpermission
                                                                </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            <div class="mb-2">
                                <div class="badge badge-primary font-size-13 mb-3 p-2">
                                    جمع فاکتور بدون اعمال تخفیف
                                    :
                                    {{
                                    number_format($factor_item->serviceTariffs()->sum('factor_item_service_tariff.price'))
                                    }}
                                    ریال
                                </div>
                            </div>
                            <div class="mb-2 mr-2">
                                <div class="badge badge-danger font-size-13 mb-3 p-2">
                                    جمع تخفیف
                                    :
                                    {{
                                    number_format($factor_item->serviceTariffs()->sum('factor_item_service_tariff.discount')
                                    - $factor_item->discount)
                                    }}
                                    ریال
                                </div>
                            </div>
                        </div>
                        @break
                        @case('factor_building')
                        <div class="col-12 col-md-12 mb-3">
                            <i class="fa fa-list"></i> {{ $form->help . ' - ' .
                            $factor_item->factor->user->name . ' ' .
                            $factor_item->factor->user->family }}
                        </div>
                        <div class="row col-md-12">
                            <table class="table table-bordered table-striped table-responsive-md">
                                <tr>
                                    <th>#</th>
                                    <th>وضعیت اجرا</th>
                                    <th>نوع خدمت</th>
                                    <th>مقدار(بر اساس متراژ - عدد)</th>
                                    <th>تعداد مراجعات </th>
                                    <th>طول دوره اجرا(روز)</th>
                                    <th>تخفیف (ریال)</th>
                                    <th>مبلغ (ریال)</th>
                                    <th>تاریخ ثبت</th>
                                    <th>اقدام</th>
                                </tr>
                                @php($factor_item->serviceTariffs=$factor_item->serviceTariffs->sortBy('pivot.value'))
                                @foreach ($factor_item->serviceTariffs as $key => $service_tariff)
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
                                    <td>{{ $service_tariff->pivot->value }}
                                    </td>
                                    <td>{{ $service_tariff->pivot->session_number }}
                                    </td>
                                    <td>{{ $service_tariff->pivot->treatment_duration }}
                                    </td>
                                    <td>{{ number_format($service_tariff->pivot->discount) }}
                                    </td>
                                    <td>{{ number_format($service_tariff->pivot->price -
                                        $service_tariff->pivot->discount) }}
                                        ریال
                                    </td>
                                    <td>{{ verta($service_tariff->pivot->created_at)->format('Y/m/d H:i:s') }}</td>
                                    <td>
                                        <a data-toggle="modal"
                                            data-target="#sessionModal_{{ $service_tariff->pivot->id }}"
                                            class="btn border border-secondary bg-white">جلسات</a>
                                        <div id="sessionModal_{{ $service_tariff->pivot->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myExtraLargeModalLabel"
                                            class="modal fade show" style="padding-right: 17px;" aria-hidden="true">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">جلسات</h5>
                                                        <button type="button" data-dismiss="modal" aria-label="Close"
                                                            class="close"><span aria-hidden="true">×</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div id="status" role="tabpanel" aria-labelledby="status-tab"
                                                            class="tab-pane fade p-t-20 active show">
                                                            <table
                                                                class="table table-bordered table-striped table-responsive-md">
                                                                <tr>
                                                                    <th>تاریخ و ساعت</th>
                                                                    <th>شماره دندان</th>
                                                                    <th>درمان</th>
                                                                    <th>توضیحات</th>
                                                                    <th>پزشک</th>
                                                                    <th>دستیار</th>
                                                                    <th>مبلغ (ریال)</th>
                                                                    <th>وضعیت</th>
                                                                    <th>ثبت کننده</th>
                                                                    <th>تایید کننده</th>
                                                                    @permission('session-delete')
                                                                    <th>اقدام</th>
                                                                    @endpermission
                                                                </tr>
                                                                @foreach($factor_item->sessions->where('factor_item_service_tariff_id' , $service_tariff->pivot->id) as $session)
                                                                <tr>
                                                                    <td>{{ verta($session->date)->format('%d %B ، %Y -
                                                                        H:i:s') }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->factorItemServiceTariff ?
                                                                        $session->factorItemServiceTariff->value :
                                                                        'ندارد' }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->factorItemServiceTariff &&
                                                                        $session->factorItemServiceTariff->serviceTariff
                                                                        ?
                                                                        $session->factorItemServiceTariff->serviceTariff->name
                                                                        : 'ندارد' }}
                                                                    </td>
                                                                    <td>{{ $session->description }}</td>
                                                                    <td>
                                                                        @if (isset($session->agent))
                                                                        {{ $session->agent->name }}
                                                                        {{ $session->agent->family }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if (isset($session->assistant))
                                                                        {{ $session->assistant->name }}
                                                                        {{ $session->assistant->family }}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        {{ number_format($session->price) }}
                                                                    </td>
                                                                    <td>
                                                                        {{ App\Models\Session::status($session->status)
                                                                        }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->user->family }}
                                                                    </td>
                                                                    <td>
                                                                        {{ $session->confirmer ?
                                                                        $session->confirmer->family : 'ندارد' }}
                                                                    </td>
                                                                    @permission('session-delete')
                                                                    <td>
                                                                        <div data-v-15965e3b="" class="dropdown dropup">
                                                                            <a href="#" data-toggle="dropdown"
                                                                                aria-haspopup="true"
                                                                                aria-expanded="false"
                                                                                class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                                                                <i aria-hidden="true"
                                                                                    class="fa fa-ellipsis-v"></i>
                                                                            </a>
                                                                            <div class="dropdown-menu">
                                                                                @permission('session-confirm')
                                                                                <form class="confirm_form" method="get"
                                                                                    action="{{ route('session.confirm', [$session->id]) }}">
                                                                                    @csrf
                                                                                    <input type="button"
                                                                                        class="dropdown-item pointer confirm_submit"
                                                                                        id="{{ $session->id }}"
                                                                                        value="تایید و واریز به کیف پول">
                                                                                </form>
                                                                                @endpermission
                                                                                @permission('session-delete')
                                                                                <form class="delete_form" method="post"
                                                                                    action="{{ route('session.destroy', [$session->id]) }}">
                                                                                    @method('DELETE')
                                                                                    @csrf
                                                                                    <input type="button"
                                                                                        class="dropdown-item pointer delete_submit"
                                                                                        id="{{ $session->id }}"
                                                                                        value="حذف">
                                                                                </form>
                                                                                @endpermission
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    @endpermission
                                                                </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            <div class="mb-2">
                                <div class="badge badge-primary font-size-13 mb-3 p-2">
                                    جمع فاکتور بدون اعمال تخفیف
                                    :
                                    {{
                                    number_format($factor_item->serviceTariffs()->sum('factor_item_service_tariff.price'))
                                    }}
                                    ریال
                                </div>
                            </div>
                            <div class="mb-2 mr-2">
                                <div class="badge badge-danger font-size-13 mb-3 p-2">
                                    جمع تخفیف
                                    :
                                    {{
                                    number_format($factor_item->serviceTariffs()->sum('factor_item_service_tariff.discount')
                                    - $factor_item->discount)
                                    }}
                                    ریال
                                </div>
                            </div>
                        </div>
                        @break
                        @case('factor')
                        <div class="col-12 col-md-12 mb-3">
                            {{ $form->help }}
                        </div>
                        <div class="row col-md-12">
                            <table class="table table-bordered table-striped table-responsive-md">
                                @foreach ($factor_item->products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->pivot->value }}
                                    </td>
                                    <td> {{ number_format($product->pivot->price) }}
                                        ریال
                                    </td>
                                    <td>
                                        <a href="{{ url('service/factor-delete/' . $product->pivot->id) }}">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            <div class="col-12 mb-2">
                                <div class="badge badge-primary font-size-13 mb-3 p-2">
                                    جمع فاکتور بدون اعمال تخفیف
                                    :
                                    {{ number_format($factor_item['total_price']+$factor_item['discount']) }}
                                    ریال
                                </div>
                            </div>
                        </div>
                        @break
                        @case('factor_service')
                        <div class="col-12 col-md-12 mb-3">
                            {{ $form->help }}
                        </div>
                        <div class="row col-md-12">
                            <table class="table table-bordered table-striped table-responsive-md">
                                @foreach ($factor_item->serviceTariffs as $service_tariff)
                                <tr>
                                    <td>{{ $service_tariff->name }}</td>
                                    <td>{{ $service_tariff->pivot->value }}
                                    </td>
                                    <td> {{ number_format($service_tariff->pivot->price) }}
                                        ریال
                                    </td>
                                    <td>
                                        <a href="{{ url('service/factor-delete/' . $service_tariff->pivot->id) }}"><i
                                                class="fa fa-trash" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            <div class="col-12 mb-2">
                                <div class="badge badge-primary font-size-13 mb-3 p-2">
                                    جمع فاکتور بدون اعمال تخفیف
                                    :
                                    {{ number_format($factor_item['total_price']+$factor_item['discount']) }}
                                    ریال
                                </div>
                            </div>
                        </div>
                        @break
                        @case('other-price')
                        @if (isset($factor_item['factor_values'][$form->name]))
                        <div class="col-12 col-md-4 mb-2">
                            <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                            <p class="form-control {{ $form->class }}" name="item[{{ $form->name }}]">
                                {{ number_format($factor_item['factor_values'][$form->name]) }}
                            </p>
                        </div>
                        @endif
                        @break
                        @case('factor-image')
                        @if (isset($factor_item['factor_image']))
                        @foreach ($factor_item['factor_image'] as $image)
                        <div class="card col-md-3">
                            <a href="{{ asset($image) }}" target="_blank">
                                <embed src="{{ $image }}" class="card-img-top" height="150px">
                            </a>
                        </div>
                        @endforeach
                        @endif
                        @break
                        @case('file')
                        @if (isset($factor_item['factor_values'][$form->name]))
                        <div class="col-md-3 mb-3">
                            <a href="{{ asset($factor_item['factor_values'][$form->name]) }}" target="_blank">
                                <img src="{{ $factor_item['factor_values'][$form->name] }}" class="card-img-top"
                                    height="150px">
                            </a>
                        </div>
                        @endif
                        @break
                        @case('multi_file')
                        @if (isset($factor_item['factor_values'][$form->name]))
                        @foreach ($factor_item['factor_values'][$form->name] as $image)
                        <div class="col-md-3 mb-3">
                            <a href="{{ asset($image) }}" target="_blank">
                                <img src="{{ $image }}" class="card-img-top" height="150px">
                            </a>
                        </div>
                        @endforeach
                        @endif
                        @break
                        @endswitch
                        @endif
                        @endforeach
                        @endif
                        <div class="success-overly position-absolute mx-3"></div>
                    </div>
                    @endforeach
                    @else
                    <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                        <div>
                            @if (!is_null($factoritem['service_item']))
                            <span>{{ $factoritem['factoritemable']->title . '-' . $factoritem['service_item']->title
                                }}</span>
                            @else
                            <span>{{ $factoritem['factoritemable']->title }}</span>
                            @endif
                        </div>
                        <i class="fa fa-check font-18 text-success"></i>
                    </div>
                    @endif
                    <form class="form-horizontal" role="form" method="post"
                        action="{{ !$factoritem['total_price'] ? route('factorItem.createFactor') : route('factorItem.calculate') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $factoritem['id'] }}">
                        <input type="hidden" name="item[id]" value="{{ $factoritem['id'] }}">
                        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">

                        <div class="row">
                            <div class="col-12 col-md-3 mb-2">
                                <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                                @if($factoritem['total_price'])
                                <p class="form-control">
                                    {{ number_format($factoritem['total_price'] +
                                    (isset($factoritem['price_difference']['total']) ?
                                    $factoritem['price_difference']['total'] : 0)) }} </p>
                                @else
                                <input class="form-control" value="{{ number_format($factoritem['total_price']) }}"
                                    name="totalPrice" oninput="separatePrices(this)">
                                @endif
                            </div>
                            @if($item['confirm'])
                            <div class="col-12 col-md-3 mb-2" data-toggle="tooltip" data-placement="bottom"
                                title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
                                <label>مبلغ کل اعتبار پرونده(ریال)</label>
                                <p class="form-control border-success">
                                    {{ number_format($factoritem['total_price']) }} </p>
                            </div>
                            @endif
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ تخفیف</label>
                                <input class="form-control" value="{{ number_format($factoritem['discount']) }}"
                                    name="discount" type="text" oninput="separatePrices(this)">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ اختلاف حساب</label>
                                <p class="form-control">
                                    {{ isset($factoritem['price_difference']['total']) ?
                                    number_format($factoritem['price_difference']['total']) : 0 }}
                                </p>
                            </div>

                            <div class="col-12 col-md-3 mb-2">
                                <label>تعداد اقساط</label>
                                <input class="form-control" value="{{ $factoritem['installments_num'] }}"
                                    name="installments_num" type="number">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>مبلغ اقساط(ریال)</label>
                                <p class="form-control">
                                    {{ number_format($factoritem['installments_price']) }}
                                </p>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>پیش پرداخت(ریال)</label>
                                <input class="form-control"
                                    value="{{ number_format($factoritem['prepayment']['price']) }}" name="prepayment"
                                    type="text" oninput="separatePrices(this)">
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                <label> نوع پرداخت</label>
                                <br>
                                نقد <input type="radio" name="type" value="0" {{ $factoritem['type']=='0' ? 'checked'
                                    : '' }}>
                                اقساط<input type="radio" name="type" value="1" {{ $factoritem['type']=='1' ? 'checked'
                                    : '' }}>
                            </div>
                        </div>
                        @permission('financial-info-file-edit')
                        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm']
                            ? 'disabled' : '' }}>
                        @endpermission
                    </form>
                </div>
                @php($factorItem = $factoritem)
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
