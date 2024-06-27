@include('Admin.factoritem.show.turnRatings')

<div class="card border border-info rounded box">
    <div class="card-header">
        <h4>افزودن زمان نوبت بعدی</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('session.store')}}" method="POST">
            @csrf

            @include('Admin.layouts.errors')
            <div class="row mb-5">
                <input type="hidden" name="factor_item_id" value="{{ $factorItem['id'] }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <div class="col-12 col-md-4">
                    <label>انتخاب تاریخ و ساعت نوبت درمان</label>
                    <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm"
                            name="date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                    </date-picker>
                </div>
                <div class="col-12 col-md-4">
                    <label>انتخاب پزشک</label>
                    <select class="form-control" name="agent_id" required>
                        <option selected disabled>لطفا پزشک مورد نظر را انتخاب کنید</option>
                        <option value="0">بدون پزشک</option>
                        @foreach($center_agents as $center_agent)
                            <option value="{{ $center_agent->id }}">
                                {{ $center_agent->name }} {{ $center_agent->family }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label>توضیحات قبل از انجام کار</label>
                    <textarea class="form-control" name="description_before"></textarea>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="customChecksession" name="check">
                        <label class="custom-control-label" for="customChecksession">نوبت تعیین شده برای کاربر
                            پیامک شود.</label>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" id="submit" class="btn btn-success px-4">ثبت</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border border-dark rounded box">
    <div class="card-header">
        <h4>تکمیل جلسه</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-success d-none" id="msg_div">
            <span id="res_message"></span>
        </div>
        @foreach($factorItem['sessions']->where('status' , 0) as $session)
            <div class="col-12 col-md-12 card">
                <div class="card-body">
                    <form id="contact_us{{ $session->id }}" action="javascript:void(0)" novalidate autocomplete="off">
                        <h6 class="text-center">تاریخ و ساعت : {{ verta($session->date)->format('%d %B ، %Y - H:i') }}</h6>
                        <div class="row">
                            <div class="col-12 col-md-3 mb-2">
                                <label>طرح درمان</label>
                                <select class="form-control select2" name="factor_item_service_tariff_id[]"
                                        id="factor_item_service_tariff_id{{$session->id}}" multiple required>
                                    <option value="0">بدون درمان</option>
                                    @foreach($factorItem['service_tariffs'] as $service_tariff)
                                        <option value="{{ $service_tariff->pivot->id }}" {{ $service_tariff->pivot->status ?
                                'disabled' : ''
                                }}>{{ $service_tariff->pivot->value.'('.$service_tariff->name.')'}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label>انتخاب پزشک</label>
                                <select class="form-control" name="agent_id" id="agent_id{{$session->id}}" required>
                                    <option value="" selected disabled>لطفا پزشک مورد نظر را انتخاب کنید</option>
                                    <option value="0">بدون پزشک</option>
                                    @foreach($center_agents as $center_agent)
                                        <option value="{{ $center_agent->id }}" {{ $session->agent_id == $center_agent->id ? 'selected' : ''}}>
                                            {{ $center_agent->name }} {{ $center_agent->family }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>دستیار</label>
                                <select class="form-control" name="assistant_id" id="assistant_id{{$session->id}}" required>
                                    <option value="" selected disabled>لطفا دستیار مورد نظر را انتخاب کنید</option>
                                    <option value="0">بدون دستیار</option>
                                    @foreach($assistants as $assistant)
                                        <option value="{{ $assistant->id }}">
                                            {{ $assistant->name }} {{ $assistant->family }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label>هزینه لابراتوار</label>
                                <input type="text" class="form-control" name="laboratory_price"
                                       id="laboratory_price{{$session->id}}" oninput="separatePrices(this)">
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <label>توضیحات قبل از انجام کار</label>
                                <textarea class="form-control">{{ $session->description_before }}</textarea>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <label>توضیحات بعد از انجام کار</label>
                                <textarea class="form-control" name="description" id="description{{$session->id}}"></textarea>
                            </div>
                        </div>
                        <input type="submit" value="ثبت" id="submit{{$session->id}}" data-id="{{ $session->id }}"
                               class="btn btn-success mt-4" onclick="updateSession(this)" {{ $session->status ?
                'disabled' : '' }}>
                    </form>
                    @permission('session-delete')
                    <form class="session_form delete_form" method="post" action="{{ route('session.destroy', [$session->id]) }}">
                        @method('DELETE')
                        @csrf
                        <input type="button" class="btn btn-danger delete_submit mt-2" id="{{ $session->id }}" value="حذف">
                    </form>
                    @endpermission
                </div>
            </div>
        @endforeach
    </div>
</div>


<div class="card border border-dark rounded box">
    <div class="card-header">
    <h4>جلسات انجام شده</h4>
    </div>
    <div class="header-body">
        <table class="table table-bordered table-striped table-responsive-md">
            <tr>
                @if(!is_null($factorItem->factoritemable->session_attributes) && isset($factorItem->factoritemable->session_attributes['session_titles']) )
                    @foreach($factorItem->factoritemable->session_attributes['session_titles'] as $session_title)
                        <th>{{$session_title}}</th>
                    @endforeach
                @else
                <th>تاریخ و ساعت</th>
                <th>شماره دندان</th>
                <th>درمان</th>
                <th>توضیحات</th>
                <th>پزشک</th>
                <th>دستیار</th>
                <th>هزینه لابراتوار (ریال)</th>
                <th>وضعیت</th>
                <th>ثبت کننده</th>
                <th>تایید کننده</th>
                @permission('session-delete')
                <th>اقدام</th>
                @endpermission
                @endif
            </tr>
            @foreach($factorItem['sessions']->whereIn('status' , [1,2,6]) as $session)
                <tr>
                    <td>{{ verta($session->date)->format('%d %B ، %Y - H:i') }}</td>
                    @if($factorItem->factoritemable->slug != 'beauty')
                    <td>
                        {{ $session->factorItemServiceTariff ? $session->factorItemServiceTariff->value: 'ندارد'}}
                    </td>
                    @endif
                    <td>
                        {{ $session->factorItemServiceTariff && $session->factorItemServiceTariff->serviceTariff ? $session->factorItemServiceTariff->serviceTariff->name : 'ندارد' }}
                    </td>
                    <td>{{ $session->description }}</td>
                    <td>
                        {{ $session->agent ? $session->agent->name .' '. $session->agent->family : 'ندارد'}}
                    </td>
                    <td>
                        {{ $session->assistant ? $session->assistant->name .' '. $session->assistant->family : 'ندارد' }}
                    </td>
                    <td>
                        {{ number_format($session->laboratory_price) }}
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
                                    <input type="button" class="dropdown-item pointer confirm_submit" id="{{ $session->id }}"
                                           value="تایید و واریز به کیف پول">
                                </form>
                                @endpermission
                                @permission('session-delete')
                                <form class="delete_form" method="post"
                                      action="{{ route('session.destroy', [$session->id]) }}">
                                    @method('DELETE')
                                    @csrf
                                    <input type="button" class="dropdown-item pointer delete_submit" id="{{ $session->id }}"
                                           value="حذف">
                                </form>
                                @endpermission
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
        <div class="col-12 mb-2">
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                جمع مبلغ جلسات
                :
                {{ number_format($factorItem['sessions']->where('status' , '!=' , false)->sum('price')) }}
                ریال
            </div>
        </div>
        <div class="col-12 p-4 alert-warning">
            <p>راهنمایی : </p>
            <p>1 - ابتدا از قسمت افزودن زمان نوبت بعدی جلسه بعدی حضور کاربر را ثبت کنید.</p>
            <p>2 - بعد از انجام جلسه از قسمت تکمیل جلسه اقدام انجام شده در جلسه را همراه با توضیحات ثبت کنید.</p>
            <p>3 - جلسه ثبت شده نیاز به تایید مدیر دارد.</p>
            <p>توجه : انتخاب نام پزشک و دستیار الزامی می باشد، درصورتی که جلسه بدون دستیار یا پزشک بود گزینه بدون دستیار و یا گزینه بدون پزشک را انتخاب کنید.</p>
        </div>
    </div>
</div>




