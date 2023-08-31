@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('serviceTariff.update' ,  $serviceTariff->slug )}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ $serviceTariff->name }}"
                               class="form-control"
                               name="name" required>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>انتخاب تعرفه</label>
                        <select class="form-control" name="tariff_id">
                            @foreach ($tariffs as $tariff)
                                <option value="{{ $tariff->id }}" {{ $serviceTariff->tariff_id == $tariff->id ? 'selected' : ''}}>{{ $tariff->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>مبلغ</label>
                        <input type="text" value="{{ number_format($serviceTariff->price) }}"
                               class="form-control"
                               name="price" required oninput="separatePrices(this)">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>تعداد جلسات</label>
                        <input type="number" value="{{ $serviceTariff->session_number }}"
                               class="form-control"
                               name="session_number" required min="1">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>طول دوره انجام (روز)</label>
                        <input type="number" value="{{ $serviceTariff->treatment_duration }}"
                               class="form-control"
                               name="treatment_duration" required min="1">
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
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@endsection
