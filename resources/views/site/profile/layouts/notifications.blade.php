@extends('site.profile.master')

@section('title')
    پیام های جدید
@endsection

@section('content2')

    <div class="col-12 col-md-9">
        <div class="bg-white shadow rounded">
            <div class="bg-light py-3 mb-3">
                <p class="text-dark font-14 font-14 text-center m-0">پیام های جدید</p>
            </div>
            <div class="p-2 p-md-4">

                <div class="alert alert-warning">
                    در اینجا شما پیام هایی جدیدی که توسط سیستم برایتان ارسال شده را میتوانید مشاهده کنید.
                </div>
                @if(count($notifications) > 0)
                    @foreach($notifications as $item)
                        <div class="bg-light rounded p-3 mb-2">
                            <div class="d-flex justify-content-center flex-column flex-md-row justify-content-md-between align-items-center ">
                                <h6 class="text-dark">{{ $item->data['title'] }}</h6>
                                <span class="font-12 text-black-50">{{ verta($item->created_at)->format('%d %B ، %Y') }}</span>
                            </div>
                            <p class="text-dark font-14 my-3">{!! $item->data['message'] !!}</p>
                        </div>
                    @endforeach
                @else
                    <p class="font-16 text-dark">هیچ پیامی یافت نشد!</p>
                @endif

                <div class="text-center my-2">
                    <a href="{{ route('profile.notifications.read') }}" class="btn btn-secondary px-5">پیام های خوانده شده</a>
                </div>
            </div>
        </div>
    </div>

@stop
