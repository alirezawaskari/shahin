<form class="form-horizontal" role="form" method="post"
      action="{{ url('centerRequest/store') }}" enctype="multipart/form-data">
    @csrf
    <div class="col-12">
        @include('Admin.layouts.errors')
        <div class="form-row">
            <div class="col-12 mb-3">
                <h5>1 - انتخاب نوع دسته بندی</h5>
                <br>
                <br>
                فروش کالا <input type="radio" name="cat_type" value="category"
                                 onclick="catType(this.value)" checked>
                ارائه خدمات <input type="radio" name="cat_type" value="service"
                                   onclick="catType(this.value)">
            </div>
            <div class="col-12 mb-5">
                <select class="form-control" name="service" id="service">
                    <option selected disabled>انتخاب خدمت مربوطه</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->title }}
                        </option>
                    @endforeach
                </select>
                <select class="form-control" name="category" id="category">
                    <option selected disabled>انتخاب دسته بندی مربوطه</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="col-12 mb-5">
                <h5>2 - انتخاب جایگاه شغلی</h5>
                <br>
                <br>
                فروشنده <input type="radio" value="seller" name="type" checked>
                تولید کننده <input type="radio" value="producer" name="type">
                تامین کننده <input type="radio" value="supplier" name="type">
                خدمات دهنده <input type="radio" value="service_provider" name="type">
            </div>
        </div>

        <div class="form-group">
            <h5> 3 - سایر اطلاعات</h5>
            <br>
            <br>
            <div class="form-row">
                <div class="col-12 col-md-6 mb-3">
                    <label>نام </label>
                    <input type="text" name="name" required placeholder="نام "
                           class="form-control">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label> نام خانوادگی</label>
                    <input type="text" name="family" required placeholder="نام خانوادگی"
                           class="form-control">
                </div>
                <div class="col-12 col-md-6">
                    <label> جنسیت</label>
                    <select class="form-control my-2" type="int" name="gender" id="gender"
                            required>
                        <option value="" disabled selected>جنسیت</option>
                        <option value="0">آقا</option>
                        <option value="1">خانم</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>شماره موبایل</label>
                    <input type="number" name="phone" placeholder="شماره موبایل"
                           class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="col-12 col-md-6">
                    <select class="form-control my-2" type="int" name="province"
                            id="province_id" required>
                        <option value="">انتخاب استان</option>
                        @foreach(\App\Models\Province::all() as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <select class="form-control my-2" type="int" name="city" id="city_id"
                            required>
                        <option value="">انتخاب شهر</option>
                        @foreach(\App\Models\City::all() as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label>موضوع</label>
                    <input name="subject" required class="form-control"
                           placeholder="موضوع شغل خود را وارد نمایید">
                </div>

                <div class="col-12 col-md-6">
                    <label>نحوه آشنایی (طریقی که با این مجموعه آشنا شدید)</label>
                    <input name="connections" class="form-control"
                           placeholder="نحوه آشنایی خود با شرکت را وارد نمائید">
                </div>

                <div class="col-12 mt-3">
                    <label>توضیحات</label>
                    <textarea name="description" class="form-control no-resize" rows="6"
                              placeholder="توضیحات کاملی راجع به امتیازات و توانایی های شغل و محل ارائه خدمات خود را بیان نمائید"></textarea>
                </div>

                <div class="col-12">
                    <div id="addnew">
                        <label for='file' class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصاویر
                        </label>
                        <input name="file[]" type="file" id="file"
                               class="d-none filestyle file-upload file_upload_ajax file-up"
                               data-buttonname="btn-white">
                    </div>
                    <div id="file_newlink" class="col-12 row">
                        <div>
                        </div>
                    </div>
                    <div id="file_newlinktpl">
                        <div id="file_src" class="show"></div>
                        <input type="hidden" id="file" class="path" name="urlfile[]">
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group">
            <input type="submit" value="ثبت درخواست"
                   class="btn btn-orange btn-block text-white font-14">
        </div>
    </div>
</form>
