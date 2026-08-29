@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('coupon.update' , [ $coupon->id ])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ $coupon->title }}"
                               class="form-control"
                               name="title" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>کد لاتین</label>
                        <input type="text" value="{{ $coupon->code }}"
                               class="form-control"
                               name="code" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>درصد تخفیف</label>
                        <input type="text" value="{{ $coupon->discount }}"
                               class="form-control"
                               name="discount" required>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>ظرفیت</label>
                        <input type="text" value="{{ $coupon->capacity }}"
                               class="form-control"
                               name="capacity" required>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>تاریخ آغاز</label>
                        <input id="date" type="text" class="form-control start" name="date"
                               data-jdp data-jdp-type="date" data-jdp-format="jYYYY/jMM/jD"
                               value=""/>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>تاریخ پایان</label>
                        <input id="date" type="text" class="form-control end" name="date"
                               data-jdp data-jdp-type="date" data-jdp-format="jYYYY/jMM/jD"
                               value=""/>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>وضعیت</label>
                        <select class="form-control" name="status">
                            <option value="0" {{ $coupon->status == '0' ? 'selected' : '' }}>
                                غیر فعال
                            </option>
                            <option value="1" {{ $coupon->status == '1' ? 'selected' : '' }}>
                                فعال
                            </option>
                        </select>
                    </div>


                    <div class="col-12">
                        <input type="hidden" class="start-val" name="start_at">
                        <input type="hidden" class="end-val" name="end_at">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        // The two fields are built by jalaliDatePicker.js. It publishes the unix
        // timestamp that pwt-datepicker's onSelect used to hand over on the element
        // itself, as data-jdp-unix, so the hidden start_at/end_at keep the same value.
        $(document).on('change', '.start', function () {
            $('.start-val').val(this.dataset.jdpUnix || '');
        });

        $(document).on('change', '.end', function () {
            $('.end-val').val(this.dataset.jdpUnix || '');
        });
    </script>
@endsection
