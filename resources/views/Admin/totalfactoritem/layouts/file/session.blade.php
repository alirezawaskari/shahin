<div class="panel">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a href="#sessions" class="accordion-toggle collapsed" data-toggle="collapse"
                data-parent="#accordion">جلسات</a>
        </h5>
    </div>
    <div id="sessions" class="panel-collapse collapse">
        <div class="panel-body">
            <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                @foreach ($items as $key => $factoritem)
                <li class="nav-item">
                    <a class="nav-link show {{ $key == 0 ? 'active' : ''}}" id="session-{{ $key }}-tab"
                        data-toggle="tab" href="#session-{{ $key }}" role="tab" aria-controls="session-{{ $key }}"
                        aria-selected="false">{{
                        $factoritem['factoritemable']->title }}</a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content border p-4" id="myTabContent">
                @foreach ($items as $key => $factoritem)
                <div class="tab-pane fade p-t-20 {{ $key == 0 ? 'show active' : ''}} " id="session-{{ $key }}"
                    role="tabpanel" aria-labelledby="session-{{ $key }}-tab">
                    <div class="card-header row col-12 col-md-12 mb-3">
                        <div class="col-md-10">
                            <h5>{{ $factoritem['factoritemable']->title . ($factoritem['center'] ? ' (
                                '.$factoritem['center']->title.' ) ' : '') }}</h5>
                        </div>
                        <div class="col-md-2">
                            <a class="btn border border-secondary mr-3 box-shadow-info"
                                href="../../dashboard/factorItem/{{ $factoritem['resnumber'] }}/edit"><i
                                    class="fa fa-edit"></i> ویراش پرونده</a>
                        </div>
                    </div>
                    @foreach ($factoritem['factorItems'] as $factor_item)
                    <div class="col-12 col-md-12 mb-3"><i class="fa fa-calendar" aria-hidden="true"></i>
                        {{ ' جلسات - ' . $factor_item->factor->user->name . ' ' .
                        $factor_item->factor->user->family }}
                    </div>
                    <table class="table table-bordered table-striped table-responsive-md">
                        <tr>
                            @if(!is_null($factor_item->factoritemable->session_attributes) && isset($factor_item->factoritemable->session_attributes['session_titles']) )
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
                        @foreach ($factor_item->sessions->where('status', '!=', false) as $session)
                        <tr>
                            <td>{{ verta($session->date)->format('%d %B ، %Y - H:i:s') }}
                            </td>
                            @if($factor_item->factoritemable->slug != 'beauty')
                            <td>
                                {{ $session->factorItemServiceTariff ?
                                $session->factorItemServiceTariff->value : 'ندارد' }}
                            </td>
                            @endif
                            <td>
                                {{ $session->factorItemServiceTariff &&
                                $session->factorItemServiceTariff->serviceTariff ?
                                $session->factorItemServiceTariff->serviceTariff->name : 'ندارد' }}
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
                                {{ App\Models\Session::status($session->status) }}
                            </td>
                            <td>
                                {{ $session->user->family }}
                            </td>
                            <td>
                                {{ $session->confirmer ? $session->confirmer->family : 'ندارد' }}
                            </td>
                            @permission('session-delete')
                            <td>
                                <div data-v-15965e3b="" class="dropdown dropup">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        @permission('session-confirm')
                                        <form class="confirm_form" method="get"
                                            action="{{ route('session.confirm', [$session->id]) }}">
                                            @csrf
                                            <input type="button" class="dropdown-item pointer confirm_submit"
                                                id="{{ $session->id }}" value="تایید و واریز به کیف پول">
                                        </form>
                                        @endpermission
                                        @permission('session-delete')
                                        <form class="delete_form" method="post"
                                            action="{{ route('session.destroy', [$session->id]) }}">
                                            @method('DELETE')
                                            @csrf
                                            <input type="button" class="dropdown-item pointer delete_submit"
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
                    <div class="col-12 mb-2">
                        <div class="badge badge-primary font-size-13 mb-3 p-2">
                            جمع مبلغ جلسات
                            :
                            {{ number_format($factor_item->sessions->where('status' , '!=' ,
                            false)->sum('price')) }}
                            ریال
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
