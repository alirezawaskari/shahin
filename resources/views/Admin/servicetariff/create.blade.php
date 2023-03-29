@extends('Admin.master')

@section('content')
<form class="form-horizontal" role="form" method="post" action="{{route('serviceTariff.store')}}">
    @csrf

    @include('Admin.layouts.errors')

    <div class="card" id="newservicetarifftpl">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <label>نام</label>
                    <input type="text" class="form-control" name="name[]" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>قیمت</label>
                    <input type="text" class="form-control" name="price[]" required
                        oninput="separatePrices(this)">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تعداد جلسات</label>
                    <input type="number" value="1" class="form-control" name="session_number[]" id="session_number1"
                        required min="1">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>طول دوره انجام (روز)</label>
                    <input type="number" value="1" class="form-control" name="treatment_duration[]"
                        id="treatment_duration1" required min="1">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب تعرفه</label>
                    <select class="form-control" name="tariff[]" id="tariff1">
                        @foreach ($tariffs as $tariff)
                            <option value="{{ $tariff->id }}">{{ $tariff->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب مرکز</label>
                    <select class="form-control" name="center_id[]" id="center1">
                        @foreach($centers as $center)
                        <option value="{{ $center->id }}">{{ $center->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mb-3 mb-3" id="delete1">
                </div>
            </div>
        </div>
    </div>
    <div id="newservicetariff">
        <div>
        </div>
    </div>
    <div class="col-md-12">
        <div id="addnew" class="btn badge-success mb-5">
            <a href="javascript:newServiceTariffLink()" class="text-white"><i class="fa fa-plus"
                    aria-hidden="true"></i>
                افزودن تعرفه جدید </a>
        </div>
    </div>
    <div class="col-12">
        <input type="submit" class="btn btn-success border-0" value="ثبت">
    </div>
</form>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
<script src="{{ asset('/assets/dashboard/js/serviceTariff.js?v=2.0') }}"></script>
@endsection
