<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#factors" class="accordion-toggle" data-toggle="collapse"
               data-parent="#accordion">فاکتور</a>
        </h5>
    </div>
    <div id="factors" class="panel-collapse collapse in">
        <div class="panel-body">
            <div class="card col-12 mt-2 pt-4 col-l main border">
                @if (!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
                    <div class="row p-3 position-relative">
                        @if (!is_null($factorItem['forms']))
                            @foreach ($factorItem['factorItems'] as $factoritem)
                                <div class="card-header row col-12 col-md-12 mb-3">
                                    <div class="col-md-8">
                                        <h5>{{ $factoritem['factoritemable']->title . ($factoritem['center'] ? ' (
                                '.$factoritem['center']->title.' ) ' : '') }}</h5>
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn border border-secondary mr-3 box-shadow-info" data-toggle="modal"
                                           data-target="#changeFinancialPlansTypeModal"><i
                                                class="fa fa-edit"></i> تغییر طرح</a>
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn border border-secondary mr-3 box-shadow-info"
                                           href="../../dashboard/factorItem/{{ $factoritem['resnumber'] }}/edit"><i
                                                class="fa fa-edit"></i> امور اجرایی پرونده</a>
                                    </div>
                                </div>
                                @foreach ($factorItem['forms'] as $form)
                                    @if (in_array('panel_show_file' , $form->data_display_level))
                                        @switch($form->type)
                                            @case('factor_dentist')
                                                <div class="col-12 col-md-12 mb-3">
                                                    <i class="fa fa-list"></i> {{ ' طرح درمان - ' .
                        $factoritem->factor->user->name . ' ' .
                        $factoritem->factor->user->family }}
                                                </div>
                                                <div class="row col-md-12">
                                                    <table
                                                        class="table table-bordered table-striped table-responsive-md">
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
                                                        @foreach ($factoritem->serviceTariffs as $key => $service_tariff)
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
                                                                    App\Models\User::getUser($service_tariff->pivot->user_id)->name
                                                                    . ' ' .
                                                                    App\Models\User::getUser($service_tariff->pivot->user_id)->family
                                                                    : 'ندارد' }}
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
                                                                <td>{{
                                    verta($service_tariff->pivot->created_at)->format('Y/m/d
                                    H:i:s') }}</td>
                                                                <td>
                                                                    <a data-toggle="modal"
                                                                       data-target="#sessionModal_{{ $service_tariff->pivot->id }}"
                                                                       class="btn border border-secondary bg-white">جلسات</a>
                                                                    <div
                                                                        id="sessionModal_{{ $service_tariff->pivot->id }}"
                                                                        tabindex="-1" role="dialog"
                                                                        aria-labelledby="myExtraLargeModalLabel"
                                                                        class="modal fade show"
                                                                        style="padding-right: 17px;" aria-hidden="true">
                                                                        <div class="modal-dialog modal-xl">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title"
                                                                                        id="exampleModalLabel">
                                                                                        جلسات</h5>
                                                                                    <button type="button"
                                                                                            data-dismiss="modal"
                                                                                            aria-label="Close"
                                                                                            class="close"><span
                                                                                            aria-hidden="true">×</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div id="status" role="tabpanel"
                                                                                         aria-labelledby="status-tab"
                                                                                         class="tab-pane fade p-t-20 active show">
                                                                                        <table
                                                                                            class="table table-bordered table-striped table-responsive-md">
                                                                                            <tr>
                                                                                                @if(!is_null($factoritem->factoritemable->session_attributes) && isset($factoritem->factoritemable->session_attributes['session_titles']) )
                                                                                                    @foreach($factoritem->factoritemable->session_attributes['session_titles'] as $session_title)
                                                                                                        <th>{{$session_title}}</th>
                                                                                                    @endforeach
                                                                                                @else
                                                                                                    <th>تاریخ و ساعت
                                                                                                    </th>
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
                                                                                            @foreach ($factoritem->sessions->where('factor_item_service_tariff_id' , $service_tariff->pivot->id) as $session)
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
                                                                                                        <div
                                                                                                            data-v-15965e3b=""
                                                                                                            class="dropdown dropup">
                                                                                                            <a href="#"
                                                                                                               data-toggle="dropdown"
                                                                                                               aria-haspopup="true"
                                                                                                               aria-expanded="false"
                                                                                                               class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                                                                                                <i aria-hidden="true"
                                                                                                                   class="fa fa-ellipsis-v"></i>
                                                                                                            </a>
                                                                                                            <div
                                                                                                                class="dropdown-menu">
                                                                                                                @permission('session-confirm')
                                                                                                                <form
                                                                                                                    class="confirm_form"
                                                                                                                    method="get"
                                                                                                                    action="{{ route('session.confirm', [$session->id]) }}">
                                                                                                                    @csrf
                                                                                                                    <input
                                                                                                                        type="button"
                                                                                                                        class="dropdown-item pointer confirm_submit"
                                                                                                                        id="{{ $session->id }}"
                                                                                                                        value="تایید و واریز به کیف پول">
                                                                                                                </form>
                                                                                                                @endpermission
                                                                                                                @permission('session-delete')
                                                                                                                <form
                                                                                                                    class="delete_form"
                                                                                                                    method="post"
                                                                                                                    action="{{ route('session.destroy', [$session->id]) }}">
                                                                                                                    @method('DELETE')
                                                                                                                    @csrf
                                                                                                                    <input
                                                                                                                        type="button"
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
                                                            number_format($factoritem->serviceTariffs()->sum('factor_item_service_tariff.price'))
                                                            }}
                                                            ریال
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 mr-2">
                                                        <div class="badge badge-danger font-size-13 mb-3 p-2">
                                                            جمع تخفیف
                                                            :
                                                            {{
                                                            number_format($factoritem->serviceTariffs()->sum('factor_item_service_tariff.discount')
                                                            - $factoritem->discount)
                                                            }}
                                                            ریال
                                                        </div>
                                                    </div>
                                                </div>
                                                @include('Admin.factoritem.dentist.list')
                                                @break
                                            @case('factor_building')
                                                <div class="col-12 col-md-12 mb-3">
                                                    <i class="fa fa-list"></i> {{ $form->help . ' - ' .
                        $factoritem->factor->user->name . ' ' .
                        $factoritem->factor->user->family }}
                                                </div>
                                                <div class="row col-md-12">
                                                    <table
                                                        class="table table-bordered table-striped table-responsive-md">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>وضعیت اجرا</th>
                                                            <th>نوع خدمت</th>
                                                            <th>مقدار(بر اساس متراژ - عدد)</th>
                                                            <th>تعداد مراجعات</th>
                                                            <th>طول دوره اجرا(روز)</th>
                                                            <th>تخفیف (ریال)</th>
                                                            <th>مبلغ (ریال)</th>
                                                            <th>تاریخ ثبت</th>
                                                            <th>اقدام</th>
                                                        </tr>
                                                        @foreach ($factoritem->serviceTariffs as $key => $service_tariff)
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
                                                                <td>{{
                                    verta($service_tariff->pivot->created_at)->format('Y/m/d
                                    H:i:s') }}</td>
                                                                <td>
                                                                    <a data-toggle="modal"
                                                                       data-target="#sessionModal_{{ $service_tariff->pivot->id }}"
                                                                       class="btn border border-secondary bg-white">جلسات</a>
                                                                    <div
                                                                        id="sessionModal_{{ $service_tariff->pivot->id }}"
                                                                        tabindex="-1" role="dialog"
                                                                        aria-labelledby="myExtraLargeModalLabel"
                                                                        class="modal fade show"
                                                                        style="padding-right: 17px;" aria-hidden="true">
                                                                        <div class="modal-dialog modal-xl">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title"
                                                                                        id="exampleModalLabel">
                                                                                        جلسات</h5>
                                                                                    <button type="button"
                                                                                            data-dismiss="modal"
                                                                                            aria-label="Close"
                                                                                            class="close"><span
                                                                                            aria-hidden="true">×</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div id="status" role="tabpanel"
                                                                                         aria-labelledby="status-tab"
                                                                                         class="tab-pane fade p-t-20 active show">
                                                                                        <table
                                                                                            class="table table-bordered table-striped table-responsive-md">

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
                                                            number_format($factoritem->serviceTariffs()->sum('factor_item_service_tariff.price'))
                                                            }}
                                                            ریال
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 mr-2">
                                                        <div class="badge badge-danger font-size-13 mb-3 p-2">
                                                            جمع تخفیف
                                                            :
                                                            {{
                                                            number_format($factoritem->serviceTariffs()->sum('factor_item_service_tariff.discount')
                                                            - $factoritem->discount)
                                                            }}
                                                            ریال
                                                        </div>
                                                    </div>
                                                </div>
                                                @include('Admin.factoritem.building.list')
                                                @break
                                            @case('factor')
                                                <div class="row col-md-12">
                                                    <table
                                                        class="table table-bordered table-striped table-responsive-md">
                                                        <tr>
                                                            <th>تصویر</th>
                                                            <th>نام کالا</th>
                                                            <th>ویژگی</th>
                                                            <th>مبلغ</th>
                                                            <th>اقدام</th>
                                                        </tr>
                                                        @foreach ($factorItem['products'] as $product)
                                                            <tr>
                                                                <td><img
                                                                        src="{{ env('APP_URL').'/'.$product->product->image }}"
                                                                        height="100px" width="100px"></td>
                                                                <td>{{ $product->product->name }}</td>
                                                                <td>{{ $product->attribute_item->attribute->title.' :
                                    '.$product->attribute_item->title }}</td>
                                                                <td> {{ number_format($product->price) }}
                                                                    ریال
                                                                </td>
                                                                <td>
                                                                    <a
                                                                        href="{{ url('service/factor-delete/' . $product->product_id) }}"><i
                                                                            class="fa fa-trash" aria-hidden="true"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                                @break
                                            @case('factor_service')
                                                <div class="col-12 col-md-12 mb-3">
                                                    {{ $form->help }}
                                                </div>
                                                <div class="row col-md-12">
                                                    <table
                                                        class="table table-bordered table-striped table-responsive-md">
                                                        @foreach ($factorItem['service_tariffs'] as $service_tariff)
                                                            <tr>
                                                                <td>{{ $service_tariff->name }}</td>
                                                                <td>{{ $service_tariff->pivot->value }}
                                                                </td>
                                                                <td> {{ number_format($service_tariff->pivot->price) }}
                                                                    ریال
                                                                </td>
                                                                <td>
                                                                    <a
                                                                        href="{{ url('service/factor-delete/' . $service_tariff->pivot->id) }}"><i
                                                                            class="fa fa-trash" aria-hidden="true"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                                @break
                                            @case('other-price')
                                                @if (isset($factorItem['factor_values'][$form->name]))
                                                    <div class="col-12 col-md-4 mb-2">
                                                        <label for="item[{{ $form->name }}]">{{ $form->help }}</label>
                                                        <p class="form-control {{ $form->class }}"
                                                           name="item[{{ $form->name }}]">
                                                            {{ number_format($factorItem['factor_values'][$form->name]) }}
                                                        </p>
                                                    </div>
                                                @endif
                                                @break
                                            @case('factor-image')
                                                @if (isset($factorItem['factor_image']))
                                                    @foreach ($factorItem['factor_image'] as $image)
                                                        <div class="card col-md-3">
                                                            <a href="{{ asset($image) }}" target="_blank">
                                                                <embed src="{{ $image }}" class="card-img-top"
                                                                       height="150px">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                @break
                                            @case('multi_file')
                                                @if(isset($factorItem['factor_values'][$form->name]))
                                                    @foreach ($factorItem['factor_values'][$form->name] as $image)
                                                        <div class="col-md-3 mb-3">
                                                            <a href="{{ asset($image) }}" target="_blank">
                                                                <img src="{{ $image }}" class="card-img-top"
                                                                     height="150px">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                @break
                                            @case('factor_legal')
                                                @include('Admin.factoritem.legal.list')
                                                @break
                                        @endswitch
                                    @endif
                                @endforeach
                            @endforeach
                        @endif
                        <div class="success-overly position-absolute mx-3"></div>
                    </div>
                @else
                    <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                        <div>
                            @if (!is_null($factorItem['service_item']))
                                <span>{{ $factorItem['factoritemable']->title . '-' .
                            $factorItem['service_item']->title }}</span>
                            @else
                                <span>{{ $factorItem['factoritemable']->title }}</span>
                            @endif
                        </div>
                        <i class="fa fa-check font-18 text-success"></i>
                    </div>
                @endif
                <form class="form-horizontal" role="form" method="post"
                      action="{{ route('factorItem.calculate') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
                    <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                    <div class="row">
                        <div class="col-12 col-md-3 mb-2">
                            <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                            <input class="form-control" value="{{ number_format($factorItem['total_price']) }}"
                                   name="totalPrice" oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-3 mb-2" data-toggle="tooltip"
                             data-placement="bottom"
                             title=" درصورت گذاشتن  ' - (منفی)' این مبلغ از جمع کل فاکتور کسر می شود.">
                            <label>مبلغ تخفیف</label>
                            <input class="form-control"
                                   value="{{ number_format($factorItem['discount']) }}" name="discount"
                                   type="text" oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 mb-3">
                            <p class="alert alert-danger">همکار گرامی توجه داشته باشید ، درصورتی که فاکتوری از قبل برای
                                پرونده ثبت شده باشد امکان تغییر جمع کل فاکتور بصورت دستی وجود ندارد.</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label> نوع پرداخت</label>
                            <br>
                            نقد <input type="radio" name="type" value="0" {{ $factorItem['type']=='0'
                                ? 'checked' : '' }}>
                            اقساط<input type="radio" name="type" value="1" {{ $factorItem['type']=='1'
                                ? 'checked' : '' }}>
                        </div>
                    </div>
                    @permission('financial-info-file-edit')
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{
                        $item['confirm'] ? 'disabled' : '' }}>
                    @endpermission
                </form>
            </div>
        </div>
    </div>
</div>
@include('Admin.factoritem.layouts.file.changeFinancialPlansType')
