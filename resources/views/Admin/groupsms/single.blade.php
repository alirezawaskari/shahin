@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('sms.single.store')}}">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ old('title') }}"
                               class="form-control"
                               name="title" required>
                    </div>
                    <div class="col-12  mb-3">
                        <div class="alert alert-danger">
                            همکار گرامی لطفا توجه فرمایید به دلیل قوانین جدید اپراتورها  جهت ارسال موفق پیامک ، استفاده از کلمه لغو11 در انتهای متن پیامک الزامی می باشد.
                        </div>
                    </div>

                    <div class="col-12  mb-3">
                        <label>متن پیام</label>
                        <textarea required name="message" id="" cols="30" rows="10" class="form-control"></textarea>
                    </div>



                    <div class="col-12  mb-3">
                        <label>شماره ها</label>
                        <textarea required placeholder="لطفا شماره های خود را با کامت (,) جدا کنید" name="phones" id="" cols="30" rows="10" class="form-control"></textarea>
                    </div>


                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
