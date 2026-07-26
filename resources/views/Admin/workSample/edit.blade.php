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
                        <div class="col-md-3 mb-3">
                            <label>تصویر ذخیره شده</label>
                            @include('Admin.partials.currentMedia', ['owner' => $workSample, 'field' => 'image', 'boxH' => 150])
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>تغییر تصویر</label>
                            <br>
                            <label for="image" class="btn btn-primary custom-file-upload border border-1">
                                <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                            </label>
                            <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                                   name="image" accept=".jpg,.jpeg,.png,.webp"/>
                            <div id="image_src" class="show"></div>
                            <input type="hidden" id="image_path" class="path" name="image_path" value="">
                            @if($errors->has('image_path'))
                                <span class="help-block sub-error"> {{ $errors->first('image_path') }} </span>
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
        {{-- see the note in create.blade.php: select2 redraw + center-scoped, so both
             selects must refresh the list and an empty result must explain itself --}}
        function setWorksampleableOptions(html) {
            $('#worksampleable_id').html(html).trigger('change.select2');
        }

        function loadWorksampleableItems() {
            const type = $('#worksampleable_type').val();
            const centerId = $('#select-center').val();

            if (!type) {
                setWorksampleableOptions('<option value="">لطفاً ابتدا مدل مربوطه را انتخاب کنید</option>');
                return;
            }

            setWorksampleableOptions('<option value="">در حال دریافت اطلاعات...</option>');

            $.ajax({
                url: '{{ route("workSample.getItemsByType") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    center_id: centerId
                },
                success: function (res) {
                    if (!res || res.length === 0) {
                        setWorksampleableOptions('<option value="">برای این شعبه خدمتی ثبت نشده است</option>');
                        return;
                    }
                    const current = '{{ $workSample->worksampleable_id }}';
                    let options = '<option value="">انتخاب کنید</option>';
                    res.forEach(item => {
                        // keep the saved selection selected when it's still in the list,
                        // so merely touching the center doesn't silently clear it
                        const sel = String(item.id) === current ? ' selected' : '';
                        options += `<option value="${item.id}"${sel}>${item.title}</option>`;
                    });
                    setWorksampleableOptions(options);
                },
                error: function () {
                    setWorksampleableOptions('<option value="">خطا در دریافت اطلاعات</option>');
                }
            });
        }

        $('#worksampleable_type, #select-center').on('change', loadWorksampleableItems);
    </script>
@endsection
