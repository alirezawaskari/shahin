@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('groupsms.store')}}">
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
                        <label>متن پیام</label>
                        <textarea required name="message" id="" cols="30" rows="10" class="form-control"></textarea>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>وضعیت پرداخت</label>
                        <select class="form-control" required
                                name="flag" id="flag">
                            <option value="">
                                انتخاب کنید..
                            </option>
                            <option value="0">
                                پرداخت ناموفق
                            </option>
                            <option value="1">
                                پرداخت موفق
                            </option>
                        </select>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>خدمات</label>
                        <select multiple class="form-control select2"
                                name="service_id[]" id="service_id">
                            <option value="all">
                                تمام کاربران
                            </option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-6 mb-3">
                        <label>وضعیت</label>
                        <select class="form-control" name="status" id="status">
                            <option value="0">
                                پیش نویس
                            </option>
                            <option value="1">
                                آماده ارسال
                            </option>
                            <option value="2">
                                ارسال شده
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>تاریخ ارسال</label>
                        <input type="text"
                               class="form-control date"
                               data-jdp-type="date" data-jdp-format="jYYYY/jMM/jD"
                               name="date">
                    </div>

                    <div class="col-12">
                        <input type="hidden" class="send_at">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function () {

            // The field is built by jalaliDatePicker.js. It publishes the unix timestamp
            // that pwt-datepicker's onSelect used to hand over on the element itself, as
            // data-jdp-unix, so the hidden .send_at keeps the same value.
            $(document).on('change', 'input.date[name="date"]', function () {
                $('.send_at').val(this.dataset.jdpUnix || '');
            });
        });

    </script>

@endsection

