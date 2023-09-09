@extends('Admin.master')

@section('content')

<form class="form-horizontal" role="form" method="post" action="{{route('resume.update' , [$resume->id ])}}">
    @csrf
    @method('PATCH')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md mt-5">
                <thead>
                    <tr>
                        <th>آپدیت کننده</th>
                        <th>تاریخ</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                    <tr>
                        <td>{{ $comment->user->name.' '.$comment->user->family }}</td>
                        <td>
                            {{ $comment->created_at }}
                        </td>
                        <td>{{ $comment->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @include('Admin.layouts.errors')
            <div class="row">

                <div class="col-12 col-md-3 mb-3">
                    <label>نام</label>
                    <input type="text" value="{{ $resume->name }}" class="form-control" name="name" required>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>ایمیل</label>
                    <input type="text" value="{{ $resume->email }}" class="form-control" name="email">
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>موبایل</label>
                    <input type="text" value="{{ $resume->phone }}" class="form-control" name="phone" required>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>سابقه بیمه(درصورت داشتن بیمه براساس ماه)</label>
                    <input type="number" value="{{ $resume->bime_history }}" name="bime_history" class="form-control" min="0" required>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>نوع شغل درخواستی</label>
                    <input type="text" value="{{ $resume->work_type }}" name="work_type" class="form-control" required>
                </div>

                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-6 mb-3">
                    <label>نمایندگی *</label>
                    <select name="representation_id" class="custom-select">
                        @foreach($representations as $representation)
                            <option value="">نمایندگی کل</option>
                            <option value="{{$representation->id}}" {{ $representation->id == $resume->representation_id ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-4 mb-3">
                    <label>سوابق شغلی (با ذکر نام محل کار و سمت شغلی مربوطه)</label>
                    <textarea name="work_history" class="form-control" required>{{ $resume->work_history }}</textarea>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>متن پیام</label>
                    <textarea type="text" class="form-control" name="text">{{ $resume->text }}</textarea>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>توضیحات</label>
                    <textarea type="text" class="form-control" name="description"></textarea>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>موقعیت شغلی</label>
                    <select class="form-control selectpicker" name="recruitment_notice_id">
                        @foreach ($recruitment_notices as $recruitment_notice)
                        <option value="{{ $recruitment_notice->id }}" {{ $resume->recruitment_notice_id ==
                            $recruitment_notice->id ? 'selected' : '' }}>{{ $recruitment_notice->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت</label>
                    <select class="form-control selectpicker" name="status" id="status">
                        <option value="0" {{ $resume->status == 0 ? 'selected' : '' }}>
                            جدید
                        </option>
                        <option value="5" {{ $resume->status == 5 ? 'selected' : '' }}>
                            درحال بررسی
                        </option>
                        <option value="1" {{ $resume->status == 1 ? 'selected' : '' }}>
                            قرار مصاحبه اولیه
                        </option>
                        <option value="2" {{ $resume->status == 2 ? 'selected' : '' }}>
                            تعلیق شده
                        </option>
                        <option value="3" {{ $resume->status == 3 ? 'selected' : '' }}>
                            تایید شده
                        </option>
                        <option value="4" {{ $resume->status == 4 ? 'selected' : '' }}>
                            رد شده
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>ارجاع به واحد مربوطه</label>
                    <select class="form-control" name="card_id">
                        <option value="0">انتخاب کنید</option>
                        @foreach($cards as $card)
                        <option value="{{ $card->id }}" {{ $resume->card_id == $card->id ? 'selected' : ''}}>{{
                            $card->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class='col-12 mb-3 file file--upload'>
                    <label for='resume'>
                        <i class="fa fa-cloud-upload"></i>انتخاب رزومه
                    </label>
                    <input name="resume" type="file" id="resume"
                        class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                    <div id="resume_src" class="show"></div>
                    <input type="hidden" id="resume" class="path" name="urlresume" value="">
                    <div class="mr-3">
                        @if($resume->resume)
                        <a href="{{ $resume->resume }}" class="btn btn-primary py-1 px-2">مشاهده رزومه</a>
                        @endif
                    </div>
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
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
@endsection
