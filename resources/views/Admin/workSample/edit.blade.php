@extends('Admin.master')
@section('title')ویرایش نمونه‌کار@endsection

@section('content')
    <form class="form-horizontal" method="post" action="{{ route('workSample.update', $workSample->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-12">
            @include('Admin.layouts.errors')

            <div class="card">
                <div class="card-body">
                    <h5>ویرایش نمونه‌کار</h5><br><br>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>عنوان نمونه‌کار</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $workSample->title) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>وضعیت</label>
                            <select class="form-control" name="status">
                                <option value="1" @selected($workSample->status == 1)>فعال</option>
                                <option value="0" @selected($workSample->status == 0)>غیرفعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>تصویر نمونه‌کار</label>
                            <input type="file" name="image" class="form-control-file">
                            @if($workSample->image)
                                <img src="{{ asset($workSample->image) }}" alt="نمونه‌کار" class="img-thumbnail mt-2" width="100">
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h5>ارتباط با مدل مربوطه</h5><br><br>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>انتخاب شعبه</label>
                            <select name="center_id" class="custom-select selectSearch" id="select-center" required>
                                @foreach(\App\Models\Center::all() as $center)
                                    <option value="{{ $center->id }}" @selected($workSample->center_id == $center->id)>{{ $center->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>مدل مربوطه</label>
                            <select name="worksampleable_type" id="worksampleable_type" class="form-control" required>
                                @foreach($worksampleableTypes as $key => $type)
                                    <option value="{{ $type['class'] }}" @selected($workSample->worksampleable_type == $type['class'])>{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>شناسه مدل مربوطه</label>
                            <select name="worksampleable_id" id="worksampleable_id" class="custom-select selectSearch" required>
                                <option value="{{ $workSample->worksampleable_id }}" selected>
                                    {{ optional($workSample->worksampleable)->title ?? '---' }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group mt-4">
                        <input type="submit" class="btn btn-primary border-0" value="بروزرسانی نمونه‌کار">
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script>
        $('#worksampleable_type').on('change', function () {
            const type = $(this).val();
            const centerId = $('#select-center').val();
            $('#worksampleable_id').html('<option>در حال دریافت اطلاعات...</option>');

            $.ajax({
                url: '{{ route("workSample.getItemsByType") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    center_id: centerId
                },
                success: function (res) {
                    let options = '<option value="">انتخاب کنید</option>';
                    res.forEach(item => {
                        options += `<option value="${item.id}">${item.title}</option>`;
                    });
                    $('#worksampleable_id').html(options);
                },
                error: function () {
                    $('#worksampleable_id').html('<option>خطا در دریافت اطلاعات</option>');
                }
            });
        });
    </script>
@endsection
