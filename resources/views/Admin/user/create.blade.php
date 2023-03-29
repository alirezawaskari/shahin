@extends('Admin.master')

@section('content')
@if(auth()->user()->admin_organization_id)
    <div class="card col-md-4 m-auto">
        <div class="card-body">
            <h5 id="exampleModalLabel" class="modal-title">
                <span>ثبت کاربر جدید</span>
            </h5>
            <hr>
            <form class="form-horizontal" role="form" method="post" action="{{route('user.store')}}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12">
                        @include('Admin.layouts.errors')
                    </div>
                    <div class="col-12 mb-3">
                        <input type="text" name="name" class="form-control" placeholder="نام">
                    </div>
                    <div class="col-12 mb-3">
                        <input type="text" name="family" class="form-control" placeholder="نام خانوادگی">
                    </div>
                    <div class="col-12 mb-3">
                        <input type="text" class="form-control" name="phone" placeholder="موبایل">
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <select class="form-control" name="province_id" id="province_id">
                            <option value="" disabled selected>انتخاب استان</option>
                            @foreach(\App\Models\Province::get() as $pro)
                            <option value="{{$pro->id}}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <select class="form-control" name="city_id" id="city_id">
                            <option value="" disabled selected>انتخاب شهر</option>
                            @foreach(\App\Models\City::get() as $pro)
                            <option value="{{$pro->id}}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <input type="text" name="credit" class="form-control" oninput="separatePrices(this)" placeholder="میزان اعتبار(ریال)">
                    </div>
                    <input type="hidden" name="level" value="organ">

					@if(auth()->user()->admin_organization_id)
						<input type="hidden" name="organization_id" value="{{ auth()->user()->admin_organization_id }}">
					@else
					    <div class="col-12 col-md-4 mb-3">
                        <label>سازمان</label>
                        <select class="form-control" name="organization_id">
                            <option value="">
                                بدون سازمان
                            </option>
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>
					@endif

                    <div class="col-12 col-md-4 mb-3">
                        <button type="submit" class="btn btn-success">ثبت</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="card col-md-4 m-auto">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('user.store')}}" enctype="multipart/form-data">
                @csrf
                @include('Admin.layouts.errors')
                <div class="row">
                    <div class="col-12">
                        @include('Admin.layouts.errors')
                    </div>
                    <div class="col-12 mb-3">
                        <label>شماره پرسنلی</label>
                        <input type="number" name="user_code" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>نام</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>نام خانوادگی</label>
                        <input type="text" name="family" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>واحد</label>
                        <input type="text" name="part" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>نوع استخدام</label>
                        <input type="text" name="type_employ" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>نام پدر</label>
                        <input type="text" name="father" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>موبایل</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="col-12 mb-3">
                        <label>کد ملی</label>
                        <input type="text" name="melicode" class="form-control">
                    </div>
                    <div class="col-12 mb-3">
                        <label>میزان اعتبار (ریال)</label>
                        <input type="text" name="wallet" class="form-control">
                    </div>
                    <input type="hidden" name="level" value="organ">
                    <div class="col-12 mb-3">
                        <label>نمایندگی</label>
                        <select class="form-control" name="representation_id">
                            <option value="">
                                بدون نمایندگی (دفتر تهران)
                            </option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}">{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>
					@if(auth()->user()->admin_organization_id)
						<input type="hidden" name="organization_id" value="{{ auth()->user()->admin_organization_id }}">
					@else
					    <div class="col-12 mb-3">
                        <label>سازمان</label>
                        <select class="form-control" name="organization_id">
                            <option value="">
                                بدون سازمان
                            </option>
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>
					@endif
                    <div class="col-12 mb-3">
                        <label>رمز عبور جدید</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="col-12 mb-3">
                        <label>تکرار رمز عبور جدید</label>
                        <input type="password" class="form-control" name="password_confirmation">
                    </div>
                    @permission('user-edit')
                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-success">ثبت</button>
                    </div>
                    @endpermission
                </div>
            </form>
        </div>
    </div>
@endif
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
    <script>
    @if (session()->get('message'))
    toastr.error('{{ session()->get('message') }}');
    @endif
    </script>
@endsection
