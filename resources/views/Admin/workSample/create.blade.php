@extends('Admin.master')
@section('title')ایجاد نمونه‌کارها@endsection
@section('content')
    <form class="form-horizontal" method="post" action="{{ route('workSample.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="col-12">
            @include('Admin.layouts.errors')

            <div class="card">
                <div class="card-body">
                    <h5>1 - اطلاعات نمونه‌کار</h5>
                    <br><br>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>عنوان نمونه‌کار</label>
                            <input type="text" name="title" class="form-control" placeholder="عنوان را وارد کنید" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>وضعیت</label>
                            <select class="form-control" name="status">
                                <option value="1">فعال</option>
                                <option value="0">غیرفعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>تصویر نمونه‌کار</label>
                            <br>
                            <label for="image" class="btn btn-primary custom-file-upload border border-1">
                                <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                            </label>
                            <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                                   name="image" accept=".jpg,.jpeg,.png,.webp"/>
                            {{-- the widget writes the preview into #image_src.show and the temp
                                 media id into #image_path; the .show class is required --}}
                            <div id="image_src" class="show">
                                <div class="rounded border d-flex align-items-center justify-content-center text-muted mt-2"
                                     style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                            </div>
                            <input type="hidden" id="image_path" class="path" name="image_path" value="">
                            @if($errors->has('image_path'))
                                <span class="help-block sub-error"> {{ $errors->first('image_path') }} </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h5>2 - ارتباط با مدل مربوطه</h5>
                    <br><br>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>انتخاب شعبه</label>
                            <select name="center_id" class="custom-select selectSearch" id="select-center" required>
                                <option value="" disabled selected>انتخاب کنید</option>
                                @foreach(\App\Models\Center::all() as $center)
                                    <option value="{{ $center->id }}">{{ $center->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>مدل مربوطه</label>
                            <select name="worksampleable_type" id="worksampleable_type" class="form-control" required>
                                <option value="" disabled selected>انتخاب کنید</option>
                                @foreach($worksampleableTypes as $key => $type)
                                    <option value="{{ $type['class'] }}">{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>شناسه مدل مربوطه</label>
                            <select name="worksampleable_id" id="worksampleable_id" class="custom-select selectSearch" required>
                                <option value="">لطفاً ابتدا مدل مربوطه را انتخاب کنید</option>
                            </select>
                        </div>
                    </div>


                    <hr>

                    <div class="form-group mt-4">
                        <input type="submit" class="btn btn-success border-0" value="ثبت نمونه‌کار">
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('js')
    <script>
        // #worksampleable_id is a .selectSearch, i.e. select2-initialized (adminJs.js).
        // Replacing its <option>s needs a 'change.select2' trigger so the rendered widget
        // redraws — the namespace keeps it from re-entering the handlers bound below.
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
                    // The endpoint scopes to the selected center and returns [] when that
                    // center's centerable isn't a خدمت — say so instead of leaving an
                    // empty, unselectable dropdown with no explanation.
                    if (!res || res.length === 0) {
                        setWorksampleableOptions('<option value="">برای این شعبه خدمتی ثبت نشده است</option>');
                        return;
                    }
                    let options = '<option value="">انتخاب کنید</option>';
                    res.forEach(item => {
                        options += `<option value="${item.id}">${item.title}</option>`;
                    });
                    setWorksampleableOptions(options);
                },
                error: function () {
                    setWorksampleableOptions('<option value="">خطا در دریافت اطلاعات</option>');
                }
            });
        }

        // BOTH selects: the list is scoped by center, so changing the شعبه after picking
        // the model left a stale (or wrongly-scoped) list behind.
        $('#worksampleable_type, #select-center').on('change', loadWorksampleableItems);
    </script>
@endsection
