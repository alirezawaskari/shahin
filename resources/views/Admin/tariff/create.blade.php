@extends('Admin.master')
@section('title')
    ایجاد تعرفه جدید
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('tariff.store')}}">
                @csrf
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 mb-2">
                        <label>عنوان تعرفه</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label>خدمت</label>
                        <select class="form-control select2" name="service_id" id="service_select1" required>
                            <option value="all">انتخاب کنید</option>
                            @foreach(\App\Models\Service::filter()->get() as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/tickets.js?v=1.1') }}"></script>
@endsection
