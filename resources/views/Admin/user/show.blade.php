@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link show active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="false">اطلاعات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">مدارک</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">فاکتور پرداخت شده</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade p-t-20 active show" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive">
                        @if(auth()->user()->admin_organization_id)
                            <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">عنوان</th>
                                <th scope="col">مقدار</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th scope="row">نام</th>
                                <td>{{$user->name}} {{ $user->family }}</td>
                            </tr>
                            <tr>
                                <th scope="row">موبایل</th>
                                <td>{{$user->phone }}</td>
                            </tr>
                            <tr>
                                <th scope="row">اعتبار حساب کاربر</th>
                                <td> {{number_format($user->wallet) }}  ریال </td>
                            </tr>
                            <tr>
                                <th scope="row">شماره پرسنلی</th>
                                <td>{{$user->user_code }}</td>
                            </tr>
                            <tr>
                                <th scope="row">کد ملی</th>
                                <td>{{$user->melicode }}</td>
                            </tr>
                            </tbody>
                        </table>
                        @else
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">عنوان</th>
                                    <th scope="col">مقدار</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th scope="row">نام</th>
                                    <td>{{$user->name}} {{ $user->family }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">موبایل</th>
                                    <td>{{$user->phone }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">کد ملی</th>
                                    <td>{{$user->melicode }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">شماره شناسنامه</th>
                                    <td>{{$user->shenasnamecode  }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">کد کاربری</th>
                                    <td>{{$user->token  }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">استان و شهر</th>
                                    <td>{{ $user->province_id != null ? \App\Models\Province::find($user->province_id)->name : '' }} - {{ $user->city_id != null ? \App\Models\City::find($user->city_id)->name : '' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">تلفن ثابت</th>
                                    <td>{{ $user->call }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">کد لاله کارت</th>
                                    <td style="direction: ltr;">{{ $user->lalecard_code }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">ایمیل</th>
                                    <td>{{ $user->email  }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">کد پستی</th>
                                    <td>{{$user->postalcode  }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">آدرس</th>
                                    <td>{{ $user->address  }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">کالسنتر</th>
                                    <td>{{ $user->callcenter_id != null ? \App\Models\User::find($user->callcenter_id)->family : 'ندارد'  }} </td>
                                </tr>
                                <tr>
                                    <th scope="row">خدمت درخواستی</th>
                                    <td>
                                        @if(!is_null($user->service_id))
                                            {{ \App\Models\Service::find($user->service_id)->withTrashed()->first()->title }}
                                        @elseif(!is_null($user->callcenter_id))
                                            {{'کالسنتر: ' .  \App\Models\User::find($user->callcenter_id)->family}}
                                        @else
                                            مستقیم سایت
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">نمایندگی</th>
                                    <td>{{$user->representation ? $user->representation()->withTrashed()->first()->fa_name : 'م تهران' }}</td>
                                </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
                <div class="tab-pane fade p-t-20" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="card-group">
                        <div class="card">
                            @if(is_null($user->imgmeli))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->imgmeli) }}">
                                    <img src="{{ asset($user->imgmeli) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">کارت ملی</h5>
                            </div>
                        </div>
                        <div class="card">
                            @if(is_null($user->imgshenasname))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->imgshenasname) }}">
                                    <img src="{{ asset($user->imgshenasname) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">شناسنامه</h5>
                            </div>
                        </div>
                        <div class="card">
                            @if(is_null($user->avatar))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->avatar) }}">
                                    <img src="{{  asset($user->avatar) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">عکس پرسنلی</h5>
                            </div>
                        </div>
                        <div class="card">
                            @if(is_null($user->imgcatch))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->imgcatchر) }}">
                                    <img src="{{ asset($user->imgcatch) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">قبض گاز یا برق</h5>
                            </div>
                        </div>
                        <div class="card">
                            @if(is_null($user->imglease))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->imglease) }}">
                                    <img src="{{ asset($user->imglease) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">اجاره نامه یا سند ملکی</h5>
                            </div>
                        </div>
                        <div class="card">
                            @if(is_null($user->imgczech))
                                <p class="my-4 text-danger text-center">ثبت نشده</p>
                            @else
                                <a href="{{ asset($user->imgczech) }}">
                                    <img src="{{ asset($user->imgczech) }}" class="card-img-top">
                                </a>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">چک یا فیش حقوقی</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade p-t-20" id="contact" role="tabpanel" aria-labelledby="contact-tab">

                    <div class="table-responsive w-100">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">شناسه</th>
                                <th scope="col">مبلغ</th>
                                <th scope="col">کالسنتر</th>
                                <th scope="col">تاریخ</th>
                                <th scope="col">وضعیت</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($factors as $factor)
                                <tr>
                                    <td>{{ $factor->resnumber !='' ? $factor->resnumber : 'ندارد'}}</td>

                                    <td>{{ number_format($factor->price) }}ریال
                                    </td>

                                    <td>
                                        {{ !is_null($factor->callcenter_id) ? \App\Models\User::where('id' , $factor->callcenter_id)->withTrashed()->first()->family: 'ندارد' }}
                                    </td>

                                    <td>
                                        {{ verta($factor->created_at)->format('%d %B ، %Y - H:i:s') }}
                                    </td>
                                    <td>{{ $factor->status == true ? 'پرداخت شده' : 'در انتظار پرداخت' }}</td>

                                    <td>
                                        <a href="{{ route('factor.edit' , [$factor->resnumber ]) }}"
                                           class="btn btn-light">مشاهده کامل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
