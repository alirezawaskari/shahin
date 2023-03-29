@extends('Admin.master')

@section('content')

<form class="form-horizontal" role="form" method="post" action="{{route('resume.store')}}">
    @csrf
    <div class="card">
        <div class="card-body">
            @include('Admin.layouts.errors')
            <div class="row">

                <div class="col-12 col-md-4 mb-3">
                    <label>نام</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>ایمیل</label>
                    <input type="text" class="form-control" name="email" value="{{ old('email') }}">
                </div>


                <div class="col-12 col-md-4 mb-3">
                    <label>موبایل</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>متن پیام</label>
                    <textarea type="text" class="form-control" name="text">{{ old('text') }}</textarea>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>توضیحات</label>
                    <textarea type="text" class="form-control" name="description">{{ old('description') }}</textarea>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>موقعیت شغلی</label>
                    <select class="form-control selectpicker" name="recruitment_notice_id">
                        @foreach ($recruitment_notices as $recruitment_notice)
                        <option value="{{ $recruitment_notice->id }}">{{ $recruitment_notice->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت</label>
                    <select class="form-control selectpicker" name="status" id="status">
                        <option value="0">
                            جدید
                        </option>
                        <option value="5">
                            درحال بررسی
                        </option>
                        <option value="1">
                            قرار مصاحبه اولیه
                        </option>
                        <option value="2">
                            تعلیق شده
                        </option>
                        <option value="3">
                            تایید شده
                        </option>
                        <option value="4">
                            رد شده
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>ارجاع به واحد مربوطه</label>
                    <select class="form-control" name="card_id">
                        <option value="0">انتخاب کنید</option>
                        @foreach($cards as $card)
                        <option value="{{ $card->id }}">{{
                            $card->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class='col-12 col-md-4 mb-3 file file--upload'>
                    <label for='resume'>
                        <i class="fa fa-cloud-upload"></i>انتخاب رزومه
                    </label>
                    <input name="resume" type="file" id="resume"
                        class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                    <div id="resume_src" class="show"></div>
                    <input type="hidden" id="resume" class="path" name="urlresume" value="">
                </div>
            </div>
        </div>
    </div>
    <div class="col-10">
        <input type="submit" class="btn btn-success border-0" value="ثبت">
    </div>
</form>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/factorItem.js')}}"></script>
@endsection
