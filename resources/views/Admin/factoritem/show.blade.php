@extends('Admin.master')
@section('title')پرونده {{ $user['family'] }}@endsection

@section('content')
@include('Admin.factoritem.layouts.info')

<div class="card">
    <div class="card-body">
        @permission('file-detail')
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show active" id="factor-tab" data-toggle="tab" href="#factor" role="tab"
                    aria-controls="factor" aria-selected="false">پرونده</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="turn-ratings-tab" data-toggle="tab" href="#turnRatings" role="tab"
                    aria-controls="turnRatings" aria-selected="false">نوبت دهی</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="basic-document-tab" data-toggle="tab" href="#basic-document" role="tab"
                    aria-controls="basic-document" aria-selected="true">مدارک اولیه</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                    aria-controls="contact" aria-selected="false">توضیحات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="return-payment-tab" data-toggle="tab" href="#returnPayment" role="tab"
                    aria-controls="returnPayment" aria-selected="false">عودت وجه</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="factor" role="tabpanel" aria-labelledby="contact-tab">
                <div id="accordion" class="panel-group">
                @include('Admin.factoritem.show.factor')
                    @if ($factorItem['factoritemable']->session)
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#sessions" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">جلسات</a>
                            </h5>
                        </div>
                        <div id="sessions" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="card col-12 mt-2 pt-4 col-l main border">
                                    @foreach ($factorItem['factorItems'] as $factoritem)
                                    <div class="col-12 col-md-12 mb-3"><i class="fa fa-calendar" aria-hidden="true"></i>
                                        {{ ' جلسات - ' . $factoritem->factor->user->name . ' ' .
                                        $factoritem->factor->user->family }}
                                    </div>
                                    <table class="table table-bordered table-striped table-responsive-md">
                                        <tr>
                                            @if(!is_null($factoritem->factoritemable->session_attributes) && isset($factoritem->factoritemable->session_attributes['session_titles']) )
                                                @foreach($factoritem->factoritemable->session_attributes['session_titles'] as $session_title)
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
                                        @foreach ($factoritem->sessions->where('status', '!=', false) as $session)
                                        <tr>
                                            <td>{{ verta($session->date)->format('%d %B ، %Y - H:i:s') }}
                                            </td>
                                            <td>
                                                {{ $session->factorItemServiceTariff ?
                                                $session->factorItemServiceTariff->value : 'ندارد' }}
                                            </td>
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
                                    <div class="col-12 mb-2">
                                        <div class="badge badge-primary font-size-13 mb-3 p-2">
                                            جمع مبلغ جلسات
                                            :
                                            {{ number_format($factoritem->sessions->where('status' , '!=' ,
                                            false)->sum('price')) }}
                                            ریال
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($factorItem['purchase_type'] == 0)
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#address" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">آدرس ارسال</a>
                            </h5>
                        </div>
                        <div id="address" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <h5> آدرس : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->address : 'ثبت نشده' }}</h5>
                                                <h5> استان : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->province->name : 'ثبت نشده' }}</h5>
                                                <h5> شهر : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->city->name : 'ثبت نشده' }}</h5>
                                                <h5> پلاک : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->plaque : 'ثبت نشده' }}</h5>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <h5> واحد : {{ $factorItem[ 'address' ] ? $factorItem[ 'address' ]->unit
                                                    : 'ثبت نشده' }}</h5>
                                                <h5> کد پستی : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->postal_code : 'ثبت نشده' }}</h5>
                                                <h5> نام گیرنده : {{ $factorItem[ 'address' ] ? $factorItem[ 'address'
                                                    ]->user->name.'
                                                    '.$factorItem[ 'address' ]->user->family : 'ثبت نشده' }}</h5>
                                                <h5> شماره تماس گیرنده : {{ $factorItem[ 'address' ] ? $factorItem[
                                                    'address' ]->user->phone : 'ثبت
                                                    نشده' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#payments" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">پرداخت های انجام شده</a>
                            </h5>
                        </div>
                        <div id="payments" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.factoritem.layouts.payments')
                            </div>
                        </div>
                    </div>
                    @if ($factorItem['total_price'] != 0)
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#prepaymentLink" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">ثبت و ارسال قبض پرداخت هزینه فاکتور</a>
                            </h5>
                        </div>
                        <div id="prepaymentLink" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.factoritem.layouts.file.prepaymentLink')
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#registerform" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">فرم ثبت نام</a>
                            </h5>
                        </div>
                        <div id="registerform" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.factoritem.layouts.print')
                            </div>
                        </div>
                    </div>
            </div>
            </div>
            <div class="tab-pane fade p-t-20" id="turnRatings" role="tabpanel" aria-labelledby="home-tab">
                @include('Admin.factoritem.show.determineCentering')
            </div>
            <div class="tab-pane fade p-t-20" id="basic-document" role="tabpanel" aria-labelledby="basic-document-tab">
                @include('Admin.factoritem.show.basicDocument')
            </div>
                @include('Admin.factoritem.layouts.histories',['user_histories'=>$factorItem['user_histories'],'factor'=>$factorItem['factor']])
            <div class="tab-pane fade p-t-20" id="returnPayment" role="tabpanel" aria-labelledby="return-payment-tab">
                @include('Admin.factoritem.layouts.file.returnPayment')
            </div>
        </div>
        @endpermission
        @permission('file')
        @include('Admin.factoritem.layouts.print')
        @endpermission
    </div>
</div>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/factorItem.js?v=2.1') }}"></script>
<script src="{{ asset('/assets/dashboard/js/factorModal.js?v=1.0') }}"></script>
<script>
    $(document).ready( function () {
        $('#datatable-history-blade').DataTable({searching: false});
    } );
</script>
@endsection
