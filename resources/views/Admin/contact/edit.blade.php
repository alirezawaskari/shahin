@extends('Admin.master')

@section('content')
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('contact.update' , [$contact->id ])}}">
            @csrf
            @method('PATCH')

            @include('Admin.layouts.errors')
            <div class="row">

                <div class="col-12 col-md-6 mb-3">
                    <label>نام</label>
                    <input type="text" value="{{ $contact->name }}" class="form-control" name="name" required>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>موبایل</label>
                    <input type="text" value="{{ $contact->phone }}" class="form-control" name="phone" required>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>متن پیام</label>
                    <textarea type="text" class="form-control" name="text" required>{{ $contact->text }}</textarea>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>متن پاسخ</label>
                    <textarea type="text" class="form-control" name="answer">{{ $contact->answer }}</textarea>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت</label>
                    <select class="form-control selectpicker" name="status" id="status">
                        <option value="0" {{ $contact->status == 0 ? 'selected' : '' }}>
                            جدید
                        </option>
                        <option value="1" {{ $contact->status == 1 ? 'selected' : '' }}>
                            خوانده شده
                        </option>
						<option value="2" {{ $contact->status == 2 ? 'selected' : '' }}>
                             تعلیق شده
                        </option>
                    </select>
                </div>

				 <div class="col-12 col-md-4 mb-3">
                    <label>ارجاع به واحد مربوطه</label>
                    <select class="form-control" name="card_id">
                        <option value="all">انتخاب کنید</option>
                        @foreach($cards as $card)
                        <option value="{{ $card->id }}" {{ $contact->card_id == $card->id ? 'selected' : ''}}>{{ $card->title }}</option>
                        @endforeach
                    </select>
                </div>

                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-4 mb-3">
                    <label>نمایندگی</label>
                    <select name="representation_id" class="custom-select">
                        @foreach($representations as $representation)
                            <option value="">نمایندگی کل</option>
                            <option value="{{$representation->id}}" {{ $representation->id == $contact->representation_id ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-12 mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="customCheck" checked="" name="check">
                        <label class="custom-control-label" for="customCheck">این پیام برای کاربر پیامک شود.</label>
                    </div>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <p>توجه : همکار گرامی توجه داشته باشید متنی که داخل متن پاسخ درج می شود برای کاربر پیامک می شود.</p>
					@if($contact->responder_id != '')
                        <div class="col-12 col-md-6 mb-3">
                            <label>همکار پاسخ دهنده</label>
                            <h5>{{ $contact->responder->family }}</h5>
                        </div>
                    @endif
                </div>

                @if(count($contact->file_urls))
                    <div class="col-12 col-md-6 mb-3">
                        <h3>تصاویر :</h3>
                        <hr>
                        @foreach($contact->file_urls as $image_url)
                            <a href="{{asset($image_url)}}" target="_blank" rel="noopener"><img src="{{asset($image_url)}}" width="200px" ></a>
                        @endforeach
                        <hr>
                    </div>
                @endif

                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                </div>

            </div>
        </form>
    </div>
</div>
@stop
