<div class="col-12 col-md-12 mb-3">
    <i class="fa fa-list"></i>{{ 'طرح درمان - ' .
    $factorItem->factor->user->name . ' ' .
    $factorItem->factor->user->family }}
</div>
<div class="row col-md-12">
    <table class="table table-bordered table-striped table-responsive-md">
        <tr>
            <th>#</th>
            <th>وضعیت اجرا</th>
            <th>نوع خدمت</th>
            <th>توضیحات</th>
            <th>شماره دندان</th>
            <th>نام پزشک</th>
            <th>تعداد جلسات درمان</th>
            <th>طول درمان(روز)</th>
            <th>تخفیف (ریال)</th>
            <th>مبلغ (ریال)</th>
            @permission('view_profit_factorItem')
            <th>مبلغ فروش مرکز (ریال)</th>
            <th>سود شرکت</th>
            @endpermission
            <th>تاریخ ثبت</th>
            <th>اقدام</th>
        </tr>
        @php($factorItem->serviceTariffs=$factorItem->serviceTariffs->sortBy('pivot.value'))
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
            </td>
            @permission('view_profit_factorItem')
            <td>{{ number_format($service_tariff->purchase_price) }}</td>
            <td>
                {{ App\Models\CenterServiceTariff::setCompanyProfit($service_tariff ,
                $service_tariff->pivot->price - $service_tariff->pivot->discount) }}
            </td>
            @endpermission
            <td>{{ verta($service_tariff->pivot->created_at)->format('Y/m/d H:i:s') }}</td>
            <td>
                <a data-toggle="modal" data-target="#sessionModal_{{ $service_tariff->pivot->id }}"
                    class="btn border border-secondary bg-white">جلسات</a>
                <div id="sessionModal_{{ $service_tariff->pivot->id }}" tabindex="-1" role="dialog"
                    aria-labelledby="myExtraLargeModalLabel" class="modal fade show" style="padding-right: 17px;"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">جلسات</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true">×</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="status" role="tabpanel" aria-labelledby="status-tab"
                                    class="tab-pane fade p-t-20 active show">
                                    <table class="table table-bordered table-striped table-responsive-md">
                                        <tr>
                                            @if(!is_null($factorItem->factoritemable->session_attributes)
                                            &&
                                            isset($factorItem->factoritemable->session_attributes['session_titles']))
                                            @foreach($factorItem->factoritemable->session_attributes['session_titles']
                                            as $session_title)
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
                                        @foreach($factorItem->sessions->where('factor_item_service_tariff_id'
                                        , $service_tariff->pivot->id) as $session)
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
                                                    <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false"
                                                        class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
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
                                                                id="{{ $session->id }}" value="حذف">
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
        <tr class="bg-secondary">
            <th></th>
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
            سود شرکت
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