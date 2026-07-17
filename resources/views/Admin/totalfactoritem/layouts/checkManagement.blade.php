<div class="container">
{{--    @if($totalFactorItem->confirm!=1 && auth()->user()->isAbleTo('store-check'))--}}
    @if(true && auth()->user()->isAbleTo('store-check'))
        <button class="btn btn-success my-2" data-toggle="modal" data-target="#storeCheckModal" id="add-new-check-btn">
            <i class="fa fa-plus m-1"></i> افزودن چک
        </button>
    @endif
    <div class="row">
        <div class="card col-12 mt-5">
            <div class="card-header">
                <h3>چک های پرونده</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered bg-check-light">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">مبلغ (ریال)</th>
                        <th scope="col">تاریخ وصول</th>
                        <th scope="col">متقاضی</th>
                        <th scope="col">صادر کننده</th>
                        <th scope="col">در وجه</th>
                        <th scope="col">شناسه صیادی</th>
                        <th scope="col">موضوع</th>
                        <th scope="col">تصاویر</th>
                        <th scope="col">توضیحات</th>
                        <th scope="col">در اختیار</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">عملیات</th>
                    </tr>
                    </thead>
                    <tbody id="checks-tbody">
                    @php($i=1)
                    @foreach($checks as $check)
                        <tr>
                            <th scope="row">{{$i++}}</th>
                            <td>{{number_format($check->amount)}}</td>
                            <td>{{verta($check->date)->format('Y/m/d')}}</td>
                            <td>
                                {{$check->applicantable ? $check->applicantable->name . ' '.$check->applicantable->family : 'ثبت نشده'}}
                                <br>
                                {{$check->applicantable ? $check->applicantable->phone : 'ثبت نشده'}}
                            </td>
                            <td>{{$check->exportable ? $check->exportable->name . ' '.$check->exportable->family : 'ثبت نشده'}}
                                <br>
                                {{$check->exportable ? $check->exportable->phone : 'ثبت نشده'}}
                            </td>
                            <td>{{$check->paytoable ? $check->paytoable->name . ' '.$check->paytoable->family : 'ثبت نشده'}}
                                <br>
                                {{$check->paytoable ? $check->paytoable->phone : 'ثبت نشده'}}
                            </td>
                            <td>{{$check->serial_code}}</td>
                            <td>
                                @switch($check->title)
                                    @case('installment')
                                    <span class="badge badge-primary"> قسط</span>
                                    @break
                                    @case('guaranty')
                                    <span class="badge badge-info"> ضمانت</span>
                                    @break
                                    @case('prepayment')
                                    <span class="badge badge-purple"> پیش پرداخت</span>
                                    @break
                                    @default
                                    <span>{{$check->title}}</span>
                                @endswitch
                            </td>
                            <td> @foreach( $check->files as $file)
                                    <a href="{{$file->path??''}}" target="_blank"> <img src="{{$file->path??''}}"
                                                                                        width="70px" alt=""></a>
                                @endforeach

                            </td>
                            <td>{{$check->histories->count()>0?$check->histories->last()->description :''}}</td>
                            <td>
                                {{$check->assetTransactions()->exists()?$check->assetTransactions->last()->receiverable->name.' '.$check->assetTransactions->last()->receiverable->family :'لاله'}}
                                <br>
                                {{$check->assetTransactions()->exists()?$check->assetTransactions->last()->receiverable->phone:''}}
                            </td>
                            <td>
                                @switch ($check->status)
                                    @case(0)
                                    <span class="badge badge-warning">در انتظار وصول</span>
                                    @break
                                    @case(1)
                                    <span class="badge badge-success">وصول شده</span>
                                    @break
                                    @case(2)
                                    <span class="badge badge-danger">برگشت خورده</span>
                                    @break
                                    @case(3)
                                    <span class="badge badge-info">عودت داده شده</span>
                                    @break
                                    @case(4)
                                    <span class="badge badge-info">رسوب در خزانه</span>
                                    @break
                                    @case(5)
                                    <span class="badge badge-info">جاری</span>
                                    @break
                                    @default
                                    <span class="badge badge-info">{{$check->status}}</span>
                                    @break
                                @endswitch
                            </td>
                            <td>
{{--                                @if($totalFactorItem->confirm!=1)--}}
                                @if(true)
                                    @permission('update-check')
                                    <span
                                        data-applicant="{{$check->applicantable ? $check->applicantable->name.' '.$check->applicantable->family : 'ثبت نشده'}}"
                                        data-exporter="{{$check->exportable ? $check->exportable->name.' '.$check->exportable->family : 'ثبت نشده'}}"
                                        data-check_id="{{$check->id}}" data-serial_code="{{$check->serial_code}}"
                                        data-description="{{$check->histories->count()>0?$check->histories->last()->description :''}}"
                                        data-pay_to="{{$check->paytoable ? $check->paytoable->name.' '.$check->paytoable->family : 'ثبت نشده'}}"
                                        data-title="{{$check->title}}" data-amount="{{number_format($check->amount)}}"
                                        data-jdate="{{verta($check->date)->format('Y/m/d')}}"
                                        class="btn btn-outline-primary mb-1 d-flex" data-toggle="modal"
                                        data-target="#updateCheckModal" id="edit-check-btn"> ویرایش <i
                                            class="fa fa-edit"></i></span>
                                    @endpermission

                                    @permission('update-status-check')
                                    <button type="button" class=" mb-1 list-check-status btn btn-outline-primary"
                                    data-check_id="{{$check->id}}"
                                    data-check_date="{{verta($check->date)->format('Y/m/d')}}"
                                            data-front_image="{{$check->front_image}}"
                                            data-toggle="modal" data-target="#statusCheckModal">وضعیت
                                        </button>
                                    @endpermission

                                    @permission('delete-check')
                                    <span class="btn btn-outline-danger delete-check"
                                          data-id="{{$check->id}}">  حذف </span>
                                    @endpermission
                            </td>
                                @endif

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{--store check modal --}}
<div class="modal fade " id="storeCheckModal" tabindex="-1" role="dialog" aria-labelledby="storeCheckLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">افزودن چک </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border  bg-check">
                <div class="container">
                    <div class="row" id="check-store-error-box">

                    </div>
                </div>
                <div class=" row">
                    <div class="col-md-12">
                        <form id="new-check-form">
                            <div class="installment-check">
                                <div class="form-row  border rounded m-1">
                                    <div class="container card">
                                        <div class="row card-body">
                                            <div class="form-group col-md-4">
                                                <label>مبلغ (ریال) :</label>
                                                <input name="amount" id="check-amount" class="form-control"
                                                       placeholder="مبلغ چک" required oninput="separatePrices(this)">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>متقاضی :</label>
                                                <input id="check-exporter" name="applicant" class="form-control"
                                                       placeholder="متقاضی" readonly
                                                       value="{{$factor->user->name.' '.$factor->user->family }}"
                                                       required>
                                                <input type="hidden" value="{{$factor->user->id}}" name="applicant_id"
                                                       id="applicant-id">
                                            </div>
                                        </div>
                                    </div>
                                    {{--                                     <div class="form-group col-md-4">--}}
                                    {{--                                        <label>صادر کننده :</label>--}}
                                    {{--                                        <input id="check-exporter" name="exporter" class="form-control" placeholder="در وجه ..."--}}
                                    {{--                                               value="{{$factor->user->name.' '.$factor->user->family }}" required>--}}
                                    {{--                                    </div>--}}
                                    <div class="container card border-secondary rounded p-2">
                                        <div class="row p-2">
                                            <div class="col-md-12 text-center card-title">
                                                اطلاعات صادر کننده چک
                                                <hr>
                                            </div>
                                            <div class="row card-body">
                                                <div class="form-group col-md-4">
                                                    <label>شماره موبایل صادر کننده </label>
                                                    <input id="check-exporter-phone" name="exporter_phone"
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
                                                        <input type="radio" name="exporter_gender" id="exporter-female-gender" value="1" class="form-check-input exporter_gender" style="cursor: pointer;">
                                                        <label for="exporter-female-gender" class="form-check-label ">
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
                                                            name="exporter_province" id="exporter_province_id"
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
                                                            name="exporter_city" id="exporter_city_id" required>
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
                                                    <input id="check-payto-phone" name="pay_to_phone"
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
                                                           id="pay_to_family" name="pay_to_family" required
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
                                                    <select class="form-control my-2  pay_to_province_id" type="int"
                                                            name="pay_to_province" id="province_id"
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
                                                            name="pay_to_city" id="city_id" required>
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
                                            {{--                                                <input id="check-pay-to" name="pay_to" class="form-control" placeholder="در وجه ..."--}}
                                            {{--                                                       value="سید مصطفی میرحسینی" required>--}}
                                            {{--                                            </div>--}}
                                            <div class="form-group col-md-4">
                                                <label>شناسه صیادی :</label>
                                                <input id="serial-code" name="serial_code" class="form-control"
                                                       placeholder="شناسه صیادی چک (16 رقمی)"
                                                       value="" required>
                                            </div>
                                            <div class="form-group col-md-4  ">
                                                <label>تاریخ وصول :</label>
                                                <input name="date" id="check-date" class="form-control  check-date"
                                                       required>
                                            </div>
                                            <div class="form-group col-md-4 ">
                                                <label>بابت :</label>
                                                <select name="title" class="form-control" id="check-title">
                                                    <option value="installment">قسط</option>
                                                    <option value="guaranty">ضمانت</option>
                                                    <option value="prepayment">پیش پرداخت</option>
                                                </select>
                                            </div>

                                            <input type="hidden" id="total-factor-item-id" name="total_factor_item_id"
                                                   value="{{isset($totalFactorItem)?$totalFactorItem->id:''}}" required>

                                            <div class="form-group col-md-8">
                                                <label>توضیحات :</label>
                                                <textarea class="form-control" name="description" id="check-description"
                                                          cols="30"
                                                          rows="3"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label>تصویر روی چک :</label>
                                                <input class="form-control" type="file" id="check-front-image"
                                                       name="first_image" required>

                                                <label>تصویر پشت چک :</label>
                                                <input class="form-control" type="file" id="check-back-image"
                                                       name="second_image">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <span class="btn btn-success m-2" id="submit-check-btn">ثبت</span>
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
{{--update check modal --}}
<div class="modal fade " id="updateCheckModal" tabindex="-1" role="dialog" aria-labelledby="updateCheckLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ویرایش چک </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border border-secondary bg-check">
                <div class="container">
                    <div class="row" id="check-update-error-box">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form id="new-check-form">
                            <div class="installment-check">
                                <div class="form-row  border rounded">
                                    <div class="form-group col-md-4">
                                        <label>مبلغ (ریال) :</label>
                                        <input name="amount" id="edit-check-amount" class="form-control"
                                               placeholder="مبلغ چک" required oninput="separatePrices(this)">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>متقاضی :</label>
                                        <input id="edit-check-applicant" name="applicant" class="form-control" readonly
                                               placeholder=""
                                               value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>صادر کننده :</label>
                                        <input id="edit-check-exporter" name="exporter" class="form-control" readonly
                                               placeholder=""
                                               value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>در وجه :</label>
                                        <input id="edit-check-pay-to" name="pay_to" class="form-control"
                                               placeholder="در وجه ..."
                                               value=" " required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>شناسه صیادی :</label>
                                        <input id="edit-serial-code" name="serial_code" class="form-control"
                                               placeholder="شناسه صیادی چک (16 رقمی)"
                                               value=" " required>
                                    </div>

                                    <div class="form-group col-md-4  ">
                                        <label>تاریخ وصول :</label>
                                        <input name="date" id="edit-check-date" class="form-control  check-date"
                                               required>
                                    </div>
                                    <div class="form-group col-md-4  ">

                                        <label>بابت :</label>
                                        <select name="title" class="form-control" id="edit-check-title">
                                            <option value="installment">قسط</option>
                                            <option value="guaranty">ضمانت</option>
                                            <option value="prepayment">پیش پرداخت</option>
                                        </select>
                                    </div>

                                    <input type="hidden" id="edit-check-id" name="check_id" required>

                                    <div class="form-group col-md-8">
                                        <label>توضیحات :</label>
                                        <textarea class="form-control" name="description" id="edit-check-description"
                                                  cols="30"
                                                  rows="3"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label>تصویر روی چک :</label>
                                        <input class="form-control" type="file" id="edit-check-front-image"
                                               name="first_image" required>

                                        <label>تصویر پشت چک :</label>
                                        <input class="form-control" type="file" id="edit-check-back-image"
                                               name="second_image">
                                    </div>
                                    <div class="col-md-12">
                                        <span class="btn btn-success m-2" id="update-check-btn">اعمال تغییرات</span>
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
@include('Admin.check.statusModal')
