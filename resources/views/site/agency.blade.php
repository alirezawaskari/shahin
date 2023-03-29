@extends('site.master')

@section('title')
    دریافت کارگزاری
@endsection

@section('content')

    <div class="article">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'ثبت نام جهت دریافت کارگزاری','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{--article--}}
        <div class="article-section agency pb-5 bg-gray">
            <div class="container">
                <div class="row">
                    <div class="card shadow w-100 bg-white article-box overflow-hidden border-0">
                        <div class="card-body">
                            <form action="{{ url('agency') }}" method="POST">
                                @csrf
                                <div class="col-12 col-md-6 mx-auto my-3">
                                    @foreach($errors->all() as $error)
                                        <div class="alert alert-danger" role="alert">
                                            {{ $error }}
                                        </div>
                                    @endforeach

                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>مشخصات فردی:</h6>
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">نام و نام خانوادگی</label>
                                        <input class="form-control" type="text" name="name" placeholder="نام و نام خانوادگی" required value="{{ auth()->user()->name }} {{ auth()->user()->family }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">میزان سن</label>
                                        <input class="form-control" type="text" name="age" placeholder="میزان سن" required value="{{ !empty($agency ) ? $agency->age : ''  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">کد ملی</label>
                                        <input class="form-control" type="text" name="melicode" placeholder="کد ملی" value="{{ !empty($agency ) ? $agency->melicode : auth()->user()->melicode  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">وضعیت تاهل</label>
                                        <select name="married" class="form-control">
                                            <option value="1">متاهل</option>
                                            <option value="0">مجرد</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">میزان تحصیلات</label>
                                        <input class="form-control" type="text" name="education" placeholder="میزان تحصیلات" value="{{ !empty($agency ) ? $agency->education : ''  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">رشته تحصیلی</label>
                                        <input class="form-control" type="text" name="field_education" placeholder="رشته تحصیلی" value="{{ !empty($agency ) ? $agency->field_education : ''  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">شماره ثابت</label>
                                        <input class="form-control" type="text" name="call" placeholder="شماره ثابت" value="{{ !empty($agency ) ? $agency->call : auth()->user()->call  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">شماره موبایل</label>
                                        <input class="form-control" type="text" name="phone" placeholder="شماره موبایل" required value="{{ auth()->user()->phone }}">
                                    </div>
                                    <div class="col-12 col-md-12">
                                        <h6>محل مورد نظر جهت دریافت مجوز بهره برداری کارگزاری:</h6>
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">استان</label>
                                        <input class="form-control" type="text" name="province" placeholder="استان" required value="{{ !empty($agency ) ? $agency->province : ''  }}">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="">شهر</label>
                                        <input class="form-control" type="text" name="city" placeholder="شهر" required value="{{ !empty($agency ) ? $agency->city : ''  }}">
                                    </div>
                                    <div class="col-12 col-md-6 mb-2">
                                        <label for="">آدرس</label>
                                        <input class="form-control" type="text" name="address" placeholder="آدرس" required value="{{ !empty($agency ) ? $agency->address : ''  }}">
                                    </div>
                                </div>
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <input type="submit" class="btn btn-success px-4" value="ثبت اطلاعات">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
