<div class="container">
{{--    @if($totalFactorItem->confirm!=1 && auth()->user()->isAbleTo('store-promissory'))--}}
{{--    @if(true && auth()->user()->isAbleTo('store-promissory'))--}}
        <button class="btn btn-success m-2" data-toggle="modal" data-target="#storePromissoryModal" id="add-new-promissory-btn">
            <i class="fa fa-plus m-5"></i> افزودن سفته
        </button>
{{--    @endif--}}
    <div class="row">
        <div class="card col-md-12 m-5">
            <div class="card-header">
                <h3>سفته های پرونده</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered bg-promissory-light">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">مبلغ (ریال)</th>
                        <th scope="col">تاریخ صدور</th>
                        <th scope="col">متقاضی</th>
                        <th scope="col">صادر کننده</th>
                        <th scope="col">ضمانت کننده</th>
                        <th scope="col">در وجه</th>
                        <th scope="col">شماره سریال</th>
                        <th scope="col">موضوع</th>
                        <th scope="col">تصاویر</th>
                        <th scope="col">توضیحات</th>
                        <th scope="col">در اختیار</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">عملیات</th>
                    </tr>
                    </thead>
                    <tbody id="promissories-tbody">
                    @php($i=1)
                    @foreach($promissories as $promissory)
                        <tr>
                            <th scope="row">{{$i++}}</th>
                            <td>{{number_format($promissory->amount)}}</td>
                            <td>{{verta($promissory->date)->format('Y/m/d')}}</td>
                            <td>
                                {{$promissory->applicantable->name . ' '.$promissory->applicantable->family}}
                                <br>
                                {{$promissory->applicantable->phone}}
                            </td>
                            <td>{{$promissory->exportable->name . ' '.$promissory->exportable->family}}
                                <br>
                                {{$promissory->exportable->phone}}
                            </td>
                            <td>{{$promissory->guarantorable->name . ' '.$promissory->guarantorable->family}}
                                <br>
                                {{$promissory->guarantorable->phone}}
                            </td>
                            <td>{{$promissory->paytoable->name . ' '.$promissory->paytoable->family}}
                                <br>
                                {{$promissory->paytoable->phone}}
                            </td>
                            <td>{{$promissory->serial_code}}</td>
                            <td>
                                @switch($promissory->title)
                                    @case('factor_item_guaranty')
                                    <span class="badge badge-info">ضمانت پرونده</span>
                                    @break
                                    @default
                                    <span>{{$promissory->title}}</span>
                                @endswitch
                            </td>
                            <td> @foreach( $promissory->files as $file)
                                    <a href="{{$file->path??''}}" target="_blank"> <img src="{{$file->path??''}}" width="70px" alt=""></a>
                                @endforeach
                                @if($promissory->fileMediaIsLegacy('front') || $promissory->fileMediaIsLegacy('back'))
                                <small class="d-block text-warning">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                @endif
                            </td>
                            <td>{{$promissory->histories->count()>0?$promissory->histories->last()->description :''}}</td>
                            <td>
                                {{$promissory->assetTransactions()->exists()?$promissory->assetTransactions->last()->receiverable->name.' '.$promissory->assetTransactions->last()->receiverable->family :'لاله'}}
                                <br>
                                {{$promissory->assetTransactions()->exists()?$promissory->assetTransactions->last()->receiverable->phone:''}}
                            </td>
                            <td>
                                @switch ($promissory->status)
                                    @case(0)
                                    <span class="badge badge-warning">رسوب در خزانه</span>
                                    @break
                                    @case(1)
                                    <span class="badge badge-success">عودت داده شده</span>
                                    @break
                                    @default
                                    <span class="badge badge-info">{{$promissory->status}}</span>
                                    @break
                                @endswitch
                            </td>
                            <td>
{{--                                @if($totalFactorItem->confirm!=1)--}}
                                @if(true)
                                    @permission('update-promissory')
                                    <span
                                        data-applicant="{{$promissory->applicantable->name.' '.$promissory->applicantable->family}}"
                                        data-exporter="{{$promissory->exportable->name.' '.$promissory->exportable->family}}"
                                        data-guaranty="{{$promissory->guarantorable->name.' '.$promissory->guarantorable->family}}"
                                        data-promissory_id="{{$promissory->id}}" data-serial_code="{{$promissory->serial_code}}"
                                        data-description="{{$promissory->histories->count()>0?$promissory->histories->last()->description :''}}"
                                        data-pay_to="{{$promissory->paytoable->name.' '.$promissory->paytoable->family}}"
                                        data-title="{{$promissory->title}}" data-amount="{{number_format($promissory->amount)}}"
                                        data-jdate="{{verta($promissory->date)->format('Y/m/d')}}"
                                        data-front_image="{{$promissory->front_image}}" data-back_image="{{$promissory->back_image}}"
                                        data-front_legacy="{{$promissory->fileMediaIsLegacy('front') ? 1 : 0}}" data-back_legacy="{{$promissory->fileMediaIsLegacy('back') ? 1 : 0}}"
                                        class="btn btn-outline-primary" data-toggle="modal"
                                        data-target="#updatePromissoryModal" id="edit-promissory-btn"> ویرایش <i
                                            class="fa fa-edit"></i></span>
                                    @endpermission

                                    @permission('update-status-promissory')
                                    <button type="button" class="  list-promissory-status btn btn-outline-primary"
                                            data-promissory_id="{{$promissory->id}}"
                                            data-front_image="{{$promissory->files->where('title','front')->first()->path??''}}"
                                            data-toggle="modal" data-target="#statusPromissoryModal">وضعیت
                                    </button>
                                    @endpermission

                                    @permission('delete-promissory')
                                    <span class="btn btn-outline-danger delete-promissory"
                                          data-id="{{$promissory->id}}">  حذف </span>
                                    @endpermission
                                @endif
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{--store promissory modal --}}
<div class="modal fade " id="storePromissoryModal" tabindex="-1" role="dialog" aria-labelledby="storePromissoryLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">افزودن سفته </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border  bg-promissory">
                <div class="container">
                    <div class="row" id="promissory-store-error-box">

                    </div>
                </div>
                <div class=" row">
                    <div class="col-md-12">
                        <form id="new-promissory-form">
                            <div class="installment-promissory">
                                <div class="form-row  border rounded m-5">
                                    <div class="container card">
                                        <div class="row card-body">
                                            <div class="form-group col-md-4">
                                                <label>مبلغ (ریال) :</label>
                                                <input name="amount" id="promissory-amount" class="form-control"
                                                       placeholder="مبلغ سفته" required oninput="separatePrices(this)">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>متقاضی :</label>
                                                <input id="promissory-exporter" name="applicant" class="form-control"
                                                       placeholder="متقاضی" readonly
                                                       value="{{$factor->user->name.' '.$factor->user->family }}"
                                                       required>
                                                <input type="hidden" value="{{$factor->user->id}}" name="applicant_id"
                                                       id="applicant-id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container card border-secondary rounded p-2">
                                        <div class="row p-2">
                                            <div class="col-md-12 text-center card-title">
                                                اطلاعات صادر کننده سفته
                                                <hr>
                                            </div>
                                            <div class="row card-body">
                                                <div class="form-group col-md-4">
                                                    <label>شماره موبایل صادر کننده </label>
                                                    <input id="promissory-exporter-phone" name="exporter_phone"
                                                           oninput="searchUser(this.value,'exporter')"
                                                           class="form-control" placeholder=""
                                                           value="{{$factor->user->phone}}" required>
                                                    <p class="text-danger text-center exporter_error_user"></p>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام</label>
                                                    <input class="form-control my-2 exporter_name" type="text"
                                                           id="exporter_name" name="name" required
                                                           placeholder="نام" value="" pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('name'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('name')}}</span>
                                                    @endif
                                                    <input type="hidden" class="exporter_id" id="exporter-id"
                                                           name="exporter_id">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام خانوادگی</label>
                                                    <input class="form-control my-2 exporter_family" type="text"
                                                           id="exporter-family" name="exporter_family" required
                                                           placeholder="نام خانوادگی" value=""
                                                           pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('family'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('family')}}</span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>جنسیت</label>
                                                    <div class="form-check">
                                                        <input type="radio" name="exporter_gender" id="exporter-female-gender" value="1" class="form-promissory-input exporter_gender" style="cursor: pointer;">
                                                        <label for="exporter-female-gender" class="form-promissory-label ">
                                                            خانم
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" name="exporter_gender" id="exporter-male-gender" checked="checked" value="0" class="form-check-input exporter_gender" style="cursor: pointer;">
                                                        <label
                                                            for="exporter-male-gender" class="form-check-label">
                                                            آقا
                                                        </label></div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>انتخاب استان</label>
                                                    <select class="form-control my-2 exporter_province_id" type="int"
                                                            name="exporter_province_id" id="exporter_province_id"
                                                            required>
                                                        <option value="">انتخاب استان</option>
                                                        @foreach(\App\Models\Province::all() as $province)
                                                            <option value="{{ $province->id }}">{{ $province->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>انتخاب شهر</label>
                                                    <select class="form-control my-2 exporter_city_id" type="int"
                                                            name="exporter_city_id" id="exporter_city_id" required>
                                                        <option value="">انتخاب شهر</option>
                                                        @foreach(\App\Models\City::all() as $city)
                                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container card border-secondary rounded p-2">
                                        <div class="row p-2">
                                            <div class="col-md-12 text-center card-title">
                                                اطلاعات ضمانت کننده سفته
                                                <hr>
                                            </div>
                                            <div class="row card-body">
                                                <div class="form-group col-md-4">
                                                    <label>شماره موبایل ضمانت کننده </label>
                                                    <input id="guaranty-phone" name="guaranty_phone"
                                                           oninput="searchUser(this.value,'guaranty')"
                                                           class="form-control" placeholder=""
                                                           value="{{$factor->user->phone}}" required>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام</label>
                                                    <input class="form-control my-2 guaranty_name" type="text"
                                                           id="guaranty-name" name="guaranty_name" required
                                                           placeholder="نام" value="" pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('name'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('name')}}</span>
                                                    @endif
                                                    <input type="hidden" class="guaranty_id" id="guaranty-id"
                                                           name="guaranty_id">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام خانوادگی</label>
                                                    <input class="form-control my-2 guaranty_family" type="text"
                                                           id="guaranty-family" name="guaranty_family" required
                                                           placeholder="نام خانوادگی" value=""
                                                           pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('family'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('family')}}</span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>جنسیت</label>
                                                    <div class="form-check">
                                                        <input type="radio" name="guaranty_gender" id="guaranty-female-gender" value="1" class="form-promissory-input guaranty_gender" style="cursor: pointer;">
                                                        <label for="guaranty-female-gender" class="form-promissory-label ">
                                                            خانم
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" name="guaranty_gender" id="guaranty-male-gender" checked="checked" value="0" class="form-check-input guaranty_gender" style="cursor: pointer;">
                                                        <label
                                                            for="guaranty-male-gender" class="form-check-label">
                                                            آقا
                                                        </label></div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>انتخاب استان</label>
                                                    <select class="form-control my-2 guaranty_province_id" type="int"
                                                            name="guaranty_province_id" id="guaranty_province_id"
                                                            required>
                                                        <option value="">انتخاب استان</option>
                                                        @foreach(\App\Models\Province::all() as $province)
                                                            <option value="{{ $province->id }}">{{ $province->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>انتخاب شهر</label>
                                                    <select class="form-control my-2 guaranty_city_id" type="int"
                                                            name="guaranty_city_id" id="guaranty_city_id" required>
                                                        <option value="">انتخاب شهر</option>
                                                        @foreach(\App\Models\City::all() as $city)
                                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container card  rounded p-2 ">
                                        <div class="row p-2">
                                            <div class="col-md-12 alert text-center card-title">
                                                در وجه
                                                <hr>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4 card-body">
                                                    <label>شماره موبایل دریافت کننده </label>
                                                    <input id="promissory-payto-phone" name="pay_to_phone"
                                                           oninput="searchUser(this.value,'pay_to')"
                                                           class="form-control" placeholder=""
                                                           value="{{\App\Models\User::find(15563)->phone}}" required>
                                                    <p class="text-danger text-center pay_to_error_user"></p>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام</label>
                                                    <input class="form-control my-2 pay_to_name" type="text"
                                                           name="pay_to_name" id="pay_to_name" required
                                                           placeholder="نام" value="" pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('name'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('name')}}</span>
                                                    @endif
                                                    <input type="hidden" class="pay_to_id" id="pay-to-id"
                                                           name="pay_to_id">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>نام خانوادگی</label>
                                                    <input class="form-control my-2 pay_to_family" type="text"
                                                           id="pay_to_family" name="pay_to_family" required placeholder="نام خانوادگی" value="" pattern="^[\u0600-\u06FF\s]+$"
                                                           title="لطفا با حروف فارسی وارد نمایید">
                                                    @if ($errors->has('family'))
                                                        <span
                                                            class="d-block text-red text-left">{{ $errors->first('family')}}</span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>جنسیت</label>
                                                    <div class="form-check">
                                                        <input type="radio" name="pay_to_gender" id="pay-to-female-gender" value="1"
                                                               class="form-check-input pay_to_gender" style="cursor: pointer;">
                                                        <label for="pay-to-female-gender" class="form-check-label">
                                                            خانم
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" name="pay_to_gender" id="pay-to-male-gender" checked="checked" value="0" class="form-check-input pay_to_gender" style="cursor: pointer;">
                                                        <label
                                                            for="pay-to-male-gender" class="form-check-label">
                                                            آقا
                                                        </label></div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label>انتخاب استان</label>
                                                    <select class="form-control my-2 pay_to_province_id" type="int"
                                                            name="pay_to_province_id" id="pay_to_province_id"
                                                            required>
                                                        <option value="">انتخاب استان</option>
                                                        @foreach(\App\Models\Province::all() as $province)
                                                            <option value="{{ $province->id }}">{{ $province->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label>انتخاب شهر</label>
                                                    <select class="form-control my-2 pay_to_city_id" type="int"
                                                            name="pay_to_city_id" id="pay_to_city_id" required>
                                                        <option value="">انتخاب شهر</option>
                                                        @foreach(\App\Models\City::all() as $city)
                                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container card p-2">
                                        <div class="row  ">
                                            <div class="form-group text-center card-title col-md-12">
                                                سایر اطلاعات
                                                <hr>
                                            </div>
                                            {{--                                            <div class="form-group col-md-4">--}}
                                            {{--                                                <label>در وجه :</label>--}}
                                            {{--                                                <input id="promissory-pay-to" name="pay_to" class="form-control" placeholder="در وجه ..."--}}
                                            {{--                                                       value="سید مصطفی میرحسینی" required>--}}
                                            {{--                                            </div>--}}
                                            <div class="form-group col-md-6">
                                                <label>شماره سریال :</label>
                                                <input id="serial-code" name="serial_code" class="form-control"
                                                       placeholder="شماره سریال سفته"
                                                       value="" required>
                                            </div>
                                            <div class="form-group col-md-6  ">
                                                <label>تاریخ صدور :</label>
                                                <input name="date" id="promissory-date" class="form-control  promissory-date"
                                                       required>
                                            </div>
                                            <input type="hidden" name="title" value="factor_item_guaranty" id="promissory-title">
                                            <input type="hidden" id="total-factor-item-id" name="total_factor_item_id"
                                                   value="{{isset($totalFactorItem)?$totalFactorItem->id:''}}" required>

                                            <div class="form-group col-md-8">
                                                <label>توضیحات :</label>
                                                <textarea class="form-control" name="description" id="promissory-description"
                                                          cols="30"
                                                          rows="3"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                {{-- ajax -> temp upload -> hidden url_* (temp media id); PromissoryController attachTo()s to Spatie --}}
                                                <label>تصویر روی سفته :</label>
                                                <label for="promissory-front-image" class="btn btn-primary btn-sm d-block custom-file-upload"><i class="fa fa-upload"></i> انتخاب تصویر</label>
                                                <input type="file" id="promissory-front-image" name="promissory_front_image"
                                                       class="d-none filestyle file-upload file_upload_ajax file-up"
                                                       data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                <div id="promissory-front-image_src" class="show"></div>
                                                <input type="hidden" id="promissory-front-image_path" class="path" name="url_promissory_front_image">

                                                <label>تصویر پشت سفته :</label>
                                                <label for="promissory-back-image" class="btn btn-primary btn-sm d-block custom-file-upload"><i class="fa fa-upload"></i> انتخاب تصویر</label>
                                                <input type="file" id="promissory-back-image" name="promissory_back_image"
                                                       class="d-none filestyle file-upload file_upload_ajax file-up"
                                                       data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                <div id="promissory-back-image_src" class="show"></div>
                                                <input type="hidden" id="promissory-back-image_path" class="path" name="url_promissory_back_image">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <span class="btn btn-success m-2" id="submit-promissory-btn">ثبت</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
            </div>
        </div>
    </div>
</div>
{{--update promissory modal --}}
<div class="modal fade " id="updatePromissoryModal" tabindex="-1" role="dialog" aria-labelledby="updatePromissoryLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ویرایش سفته </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border border-secondary bg-promissory">
                <div class="container">
                    <div class="row" id="promissory-update-error-box">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form id="new-promissory-form">
                            <div class="installment-promissory">
                                <div class="form-row  border rounded m-5">
                                    <div class="form-group col-md-4">
                                        <label>مبلغ (ریال) :</label>
                                        <input name="amount" id="edit-promissory-amount" class="form-control"
                                               placeholder="مبلغ سفته" required oninput="separatePrices(this)">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>متقاضی :</label>
                                        <input id="edit-promissory-applicant" name="applicant" class="form-control" readonly
                                               placeholder=""
                                               value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>صادر کننده :</label>
                                        <input id="edit-promissory-exporter" name="exporter" class="form-control" readonly
                                               placeholder=""
                                               value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>ضمانت کننده :</label>
                                        <input id="edit-promissory-guaranty" name="guaranty" class="form-control" readonly  placeholder="" value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>در وجه :</label>
                                        <input id="edit-promissory-pay-to" name="pay_to" class="form-control" readonly
                                               placeholder="در وجه ..."
                                               value=" " required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>شماره سریال :</label>
                                        <input id="edit-serial-code" name="serial_code" class="form-control"
                                               placeholder="شماره سریال"
                                               value=" " required>
                                    </div>

                                    <div class="form-group col-md-6  ">
                                        <label>تاریخ صدور :</label>
                                        <input name="date" id="edit-promissory-date" class="form-control  promissory-date"
                                               required>
                                    </div>

                                    <input type="hidden" name="title" value="factor_item_guaranty" id="edit-promissory-title">
                                    <input type="hidden" id="edit-promissory-id" name="promissory_id" required>

                                    <div class="form-group col-md-8">
                                        <label>توضیحات :</label>
                                        <textarea class="form-control" name="description" id="edit-promissory-description"
                                                  cols="30"
                                                  rows="3"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        {{-- ajax -> temp upload -> hidden url_* (temp media id); PromissoryController attachTo()s to Spatie --}}
                                        <label>تصویر روی سفته :</label>
                                        <label for="edit-promissory-front-image" class="btn btn-primary btn-sm d-block custom-file-upload"><i class="fa fa-upload"></i> انتخاب تصویر</label>
                                        <input type="file" id="edit-promissory-front-image" name="promissory_front_image"
                                               class="d-none filestyle file-upload file_upload_ajax file-up"
                                               data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <div id="edit-promissory-front-image_src" class="show"></div>
                                        <input type="hidden" id="edit-promissory-front-image_path" class="path" name="url_promissory_front_image">

                                        <label>تصویر پشت سفته :</label>
                                        <label for="edit-promissory-back-image" class="btn btn-primary btn-sm d-block custom-file-upload"><i class="fa fa-upload"></i> انتخاب تصویر</label>
                                        <input type="file" id="edit-promissory-back-image" name="promissory_back_image"
                                               class="d-none filestyle file-upload file_upload_ajax file-up"
                                               data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <div id="edit-promissory-back-image_src" class="show"></div>
                                        <input type="hidden" id="edit-promissory-back-image_path" class="path" name="url_promissory_back_image">
                                    </div>
                                    <div class="col-md-12">
                                        <span class="btn btn-success m-2" id="update-promissory-btn">اعمال تغییرات</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
            </div>
        </div>
    </div>
</div>
@include('Admin.promissory.statusModal')
