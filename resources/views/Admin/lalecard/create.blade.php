@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('lalecard.store')}}">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ old('name') }}"
                               class="form-control"
                               name="name" required>
                    </div>


                    <div class="col-12 col-md-3 mb-3">
                        <label>نام خانوادگی</label>
                        <input type="text" value="{{ old('family') }}"
                               class="form-control"
                               name="family" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>شهر</label>
                        <input type="text" value="{{ old('city') }}"
                               class="form-control"
                               name="city">
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>تاریخ ارسال پست</label>
                        <input type="text" name="post_date" class="form-control date">
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
@endsection
