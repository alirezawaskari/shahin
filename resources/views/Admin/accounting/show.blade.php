@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>حسابرسی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">حسابرسی کاربران</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="container bg-white">
        <div class="row p-20">
            <div class="col-md-1">
                <img src="{{asset($user->avatar)}}" alt="{{$user->name.' '.$user->family}}" style="width:100%">
            </div>
            <div class="col-md-3  ">
                <h4>
                    <table class="table ">
                        <tr>
                            <td>نام و نام خانوادگی :</td>
                            <td>{{$user->name.' '.$user->family}}</td>
                        </tr>
                        <tr>
                            <td>شماره موبایل :</td>
                            <td>{{$user->phone}}</td>
                        </tr>
                        <tr>
                            <td>تعداد پرونده :</td>
                            <td>{{count($items)}}</td>
                        </tr>
                    </table>
                </h4>
            </div>
        </div>
        <br>
    </div>
    @php($i=0)
    @foreach($items as $item)
        <br>
        <div class="btn btn-outline-primary m-2">
            <div  type="button" data-toggle="collapse" data-target="#multiCollapse{{$item['id']}}" aria-expanded="false" aria-controls="multiCollapse{{$item['id']}}" >
                {{'پرونده شماره '.++$i.":"}}  {{ isset($item['service']) ? $item['service']->title : ''}} <i class="fa fa-hand-pointer-o"></i>
            </div>
        </div>
        <div class="border border-secondary bg-white  card-body collapse multi-collapse" id="multiCollapse{{$item['id']}}">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="container card">
                        <h5 class="card-header">اطلاعات پرونده</h5>
                    </div>
                    <div class="container">
                        <div class="row text-dark">
                            <div class="col-12 col-md-3 border-right">
                                <table class="table bg-white">
                                    <tr class="font-weight-bold">
                                        <td> نوع خدمت :</td>
                                        <td>
                                            <span> {{ isset($item['service']) ? $item['service']->title : ''}}</span>
                                        </td>
                                    </tr>
                                </table>
                                <table class="table ">
                                    @if($item['supervisor_id'])
                                        <tr>
                                            <td> سرپرست :</td>
                                            <td>{{ $item['detail']['factor_item']->supervisor->factor->user->name.'
                                    '.$item['detail']['factor_item']->supervisor->factor->user->family}}</td>
                                        </tr>
                                    @endif
                                </table>

                            </div>
                            <div class="col-md-3 border-right">
                                <table class="table">
                                    <tr>
                                        <td> شماره پرونده :</td>
                                        <td>{{ $item['file_number'] ? $item['file_number'] : 'ندارد' }}</td>
                                    </tr>
                                    <tr>
                                        <td> وضعیت اجرایی پرونده :</td>
                                        <td><span class="alert alert-primary">{{ $item['status_user'] }}</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td> نوع پرداخت :</td>
                                        <td>{{ $item['type'] ? 'اقساط' : 'نقد' }}</td>
                                    </tr>
                                    <tr>
                                        <td> عملیات :</td>
                                        <td>
                                            <a href="{{route('factorItem.show',$item['detail']['factor_item']->resnumber)}}">
                                                <span class="btn btn-primary btn-sm"> امور اداری و مالی پرونده</span>
                                            </a>
                                            <a href="{{route('factorItem.edit',$item['detail']['factor_item']->resnumber)}}">
                                                <span class="btn btn-primary btn-sm">امور اجرایی پرونده</span>
                                            </a>

                                                 <span class="btn btn-primary btn-sm factor-item-create-comment" data-toggle="modal"
                                                     data-target="#modalComment" data-id="{{$item['detail']['factor']->id}}">ثبت توضیح جدید</span>

                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container card">
                <div class="card-header">
                    <h5 class="card-title"><i data-toggle="modal" data-target="#helpModal" class="fa fa-question-circle"
                                              style="font-size:38px;"></i>وضعیت مالی پرونده ({{ ($item['type'] ? ' اقساطی ' : ' نقدی ').($item['confirm'] ? '-
    تایید شده ' : '') }})</h5>
                </div>

                @include('Admin.factoritem.layouts.financialInfo')

                @if ($item['type'] == 1)
                    @include('Admin.factoritem.installment.titles',['expire_installments'=>$item['expire_installments']])
                @endif
                @include('Admin.factoritem.installment.list',['installments'=>$item['detail']['installments']])
                @if($item['service_tariffs']->count()>0)
                    @include('Admin.accounting.layouts.service_tariff',
                        ['center_service_tariffs'=>$item['detail']['center_service_tariffs'] ])
                    @include('Admin.accounting.layouts.factorItemServiceTariff')
                @endif
                @if($item['detail']['sessions']->count()>0)
                    @include('Admin.accounting.layouts.sessions',['sessions'=>$item['detail']['sessions']])
                @endif
                @include('Admin.accounting.layouts.payments')

            </div>
        </div>

    @endforeach
    <div class="container">
        <div class="row">
            {{--<!--            @foreach($factor_items as $factor_item)--}}
            <div class="col-md-12">

            </div>
            {{--            @endforeach-->--}}
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="exampleModalLong" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="container mt-2 modal-content card">
                <div class="modal-header card-header">
                    <h5 class="modal-title " id="exampleModalLongTitle">توضیحات ثبت شده برای کاربر
                        : {{$user->name.' '.$user->family}} <span class="text-dark user-fullname"></span></h5>
                </div>
                <div class="modal-body table-responsive card-body width-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ثبت کننده</th>
                            <th scope="col">تاریخ ثبت</th>
                            <th scope="col">گزارش ها</th>
                        </tr>
                        </thead>
                        <tbody class="histories-table-body">
                        @php($i=0)
                        @foreach($comments as $comment)
                            <tr>
                                <td>{{++$i}}</td>
                                <td>{{$comment['user']}}</td>
                                <td>{{$comment['date']}}</td>
                                <td class="long-text-multiline">{!! $comment['description'] !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">خروج</button>
                </div>
            </div>
        </div>
    </div>
    <div id="helpModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" class="modal fade show"
         style="padding-right: 17px;" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">راهنمایی</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div id="status" role="tabpanel" aria-labelledby="status-tab"
                         class="tab-pane fade p-t-20 active show">
                        <div class="row">
                            <div class="col-12 p-4 alert-warning">
                                <p> اعتبار کاربر : شامل مجموع حواله ، موجودی کیف پول کاربر و مبلغ پرداختی پرونده با کسر
                                    خدمات ارائه شده به کاربر می باشد.</p>
                                <p>بدهکاری ضمنی : شامل خدمتی که به کاربر ارائه شده اما واریزی انجام نشده است. لازم به
                                    ذکر می
                                    باشد که کاربر موظف به پرداخت مبلغ در اولین حضور در مرکز می باشد.</p>
                                <p>بدهکاری کل : شامل مبلغی که کاربر موظف به پرداخت آن در چند مرحله می باشد.( مانند :
                                    هزینه
                                    پیش پرداخت در پرونده اقساطی - قسط عقب افتاده - اختلاف حساب - پرداخت هزینه در پرونده
                                    های
                                    نقدی)</p>
                                <p>توجه : کاربر موظف است بدهکاری ضمنی پرونده را در اسرع وقت با هر یک از روش های پرداخت
                                    موجود
                                    پرداخت نماید. در غیر اینصورت امکان ارائه خدمت برای کاربر وجود ندارد.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style=" cursor: pointer;position:fixed;bottom:53px;right: 5px" class="text-primary"  aria-hidden="true" data-toggle="modal" data-target="#exampleModalLong">
        <i style=" font-size: 50px" class="fa fa-weixin"></i>
        <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
        <span style="background: white"> تاریخچه پیگیری</span>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modalComment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت توضیح جدید : </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('factor.comment')}}" id="store-comment">
                    <div class="modal-body">
                        <textarea required class="form-control" name="comment" id="comment-body" cols="30" rows="10" placeholder="توضیح خود را بنویسید..."></textarea>
                         <input type="hidden" id="factor-id-input" value="" name="factor_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
                        <span id="do-store-comment"  class="btn btn-primary">ثبت</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
    <script src="/assets/dashboard/js/debtors.js?v=1.0"></script>
    <script src="{{ asset('/assets/dashboard/js/factorModal.js?v=1.0') }}"></script>
@endsection
