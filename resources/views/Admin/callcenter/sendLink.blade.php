<div id="sendLink" class="sendLink">
    <form id="sendLinkForm" action="{{ route('callcenter.registerForm') }}" method="POST" class="my-4">
        @csrf
        <div class="row text-dark">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body searchUser row">
                        <div class="col-12 row">
                            <div class="col-12 col-md-9 mb-3 mt-3">
                                <input class="phone form-control my-2" type="text" name="phone" required
                                    placeholder="شماره تماس کاربر را جستجو کنید" value="{{ old('phone') }}"
                                    oninput="searchUser(this.value)" id="phone">
                                @if ($errors->has('phone'))
                                <span class="d-block text-red text-left">{{
                                    $errors->first('phone') }}</span>
                                @endif
                            </div>

                            <div class="col-12 col-md-3 mt-3 mb-3">
                                <a id="historyBtn" data-toggle="modal" href="#historyModal"
                                    class="btn btn-primary py-1 px-2 text-white my-2">
                                    تاریخچه کاربر
                                </a>
                            </div>
                        </div>
                        <p class="text-danger text-center error_user"></p>

                        <div class="col-6 mb-3 mt-3">
                            <input class="form-control my-2 name" type="text" id="name" name="name" required
                                placeholder="نام" value="{{ old('name') }}" pattern="^[\u0600-\u06FF\s]+$"
                                title="لطفا با حروف فارسی وارد نمایید">
                            @if ($errors->has('name'))
                            <span class="d-block text-red text-left">{{ $errors->first('name')
                                }}</span>
                            @endif
                        </div>

                        <div class="col-6 mb-3 mt-3">
                            <input class="form-control my-2 family" type="text" id="family" name="family" required
                                placeholder="نام خانوادگی" value="{{ old('family') }}" pattern="^[\u0600-\u06FF\s]+$"
                                title="لطفا با حروف فارسی وارد نمایید">
                            @if ($errors->has('family'))
                            <span class="d-block text-red text-left">{{ $errors->first('family')
                                }}</span>
                            @endif
                        </div>

                        <div class="col-6 mb-3 mt-3">
                            <select class="form-control my-2 province_id" type="int" name="province" id="province"
                                required>
                                <option value="">انتخاب استان</option>
                                @foreach(\App\Models\Province::all() as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 mb-3 mt-3">
                            <select class="form-control my-2 city_id" type="int" name="city" id="cities" required>
                                <option value="">انتخاب شهر</option>
                                @foreach(\App\Models\City::all() as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 mb-3 mt-3">
                            <div class="form-check">
                                <input style="cursor: pointer" class="form-check-input" type="radio" name="gender"
                                    id="female-gender" value="1">
                                <label class="form-check-label" for="female-gender">
                                    خانم
                                </label>
                            </div>
                            <div class="form-check">
                                <input style="cursor: pointer" class="form-check-input" type="radio" name="gender"
                                    id="male-gender" checked value="0">
                                <label class="form-check-label" for="male-gender">
                                    آقا
                                </label>
                            </div>
                        </div>

                        <div class="col-6 mb-3 mt-3">
                            <select class="form-control my-2" type="int" name="request_type" id="requestType" required>
                                <option value="link">نوع درخواست کاربر - ارسال کارت ویزیت</option>
                                <option value="other">نوع درخواست کاربر - سایر</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input vipCheck" id="vipCheck" name="vip">
                                <label class="custom-control-label" for="vipCheck">ثروتمند/توانمند</label>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input sosCheck" id="sosCheck" name="sos">
                                <label class="custom-control-label" for="sosCheck">فقیر/کم توان</label>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input challenging-check" id="challenging"
                                    name="challenging">
                                <label class="custom-control-label" for="challenging">چالش انگیز</label>
                            </div>
                        </div>

                        <div class="col-md-3 mt-2 mb-3" id="checkbox-send-factor">
                            <hr>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send-factor" name="send_factor"
                                    value="1">
                                <label class="form-check-label" for="send-factor">ارسال فاکتور</label>
                            </div>
                        </div>
                    </div>
                    <div class="loading_send_link position-absolute ajax-loading align-items-center flex-column pt-5 d-none text-center"
                        id="loading_send_link">
                        <div role="status" class="spinner-border text-primary">
                        </div>
                        <br>
                        <span>در حال جستجو کاربر...</span>
                    </div>
                </div>
                <div class="col-12 p-4 alert-warning">
                    <p>راهنمایی : </p>
                    <p>1 - شماره تماس کاربر را جستجو کنید.</p>
                    <p>2 - درصورت ثبت بودن کاربر اطلاعات کاربر کامل می شود در غیر این صورت
                        اطلاعات را کامل کنید.</p>
                    <p>3 - از فرم سمت چپ خدمات و دسته بندی های مورد نیاز را برای کاربر ارسال
                        کنید.</p>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body containers" id="linkRequest">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link show" id="category" data-toggle="tab" href="#categoryContent"
                                    role="tab" aria-controls="categoryContent" aria-selected="false">فروش : کالا</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link show active" id="services" data-toggle="tab" href="#servicesContent"
                                    role="tab" aria-controls="servicesContent" aria-selected="false">ارائه :
                                    خدمات</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="mt-3" id="validation_error">
                            </div>
                            <div class="tab-pane fade p-t-20 active show" id="servicesContent" role="tabpanel"
                                aria-labelledby="services">
                                <div class="row d-none" id="newservicetpl">
                                    <div
                                        class="col-12 mb-3 mt-2 service_category {{ auth()->user()->center ? 'd-none' : ''}}">
                                        <select class="form-control" name="service_category[]" id="service_category_id1"
                                            onchange="selectByCategory(this.id , this.value)">
                                            <option selected disabled> انتخاب دسته بندی</option>
                                            @foreach ($service_categories as $category)
                                            <option value="{{ $category['id'] }}">{{
                                                $category['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 mt-2 service">
                                        <select class="form-control" name="service[]" id="service_id1"
                                            onchange="getRequiredDataByItem(this.id , this.value , 'service')">
                                            <option selected disabled>انتخاب خدمات</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service['id'] }}">{{
                                                $service['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 mt-2 service_item">
                                        <select class="form-control" name="service_item[]"
                                            id="service_item_id1">
                                            <option selected disabled>انتخاب نوع خدمت</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 mt-2 financial_plans_type">
                                        <select class="form-control" name="service_financial_plans_type[]"
                                            id="financial_plans_type_id1">
                                            <option selected disabled>انتخاب نوع طرح</option>
                                        </select>
                                    </div>
                                    {{-- <div class="col-12 mb-3 mt-2 service_pay_type">
                                        <select class="form-control" name="service_pay_type[]" id="service_pay_type1">
                                            <option selected disabled>انتخاب نوع پرداخت</option>
                                            <option value="0">نوع پرداخت - نقدی</option>
                                            <option value="1">نوع پرداخت - اقساطی</option>
                                        </select>
                                    </div> --}}
                                    <div class="col-12 mb-3 mt-2 service_purchase_type">
                                        <select class="form-control" name="service_purchase_type[]"
                                            id="service_purchase_type1">
                                            <option selected disabled>انتخاب نحوه دریافت خدمت</option>
                                            <option value="0"> نحوه دریافت خدمت - غیر حضوری</option>
                                            <option value="1"> نحوه دریافت خدمت - حضوری</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3 mt-2 {{ auth()->user()->center ? 'd-none' : '' }}"
                                        id="delete1">
                                    </div>
                                </div>
                                <div class="newservice" id="newservice">
                                    <div>
                                    </div>
                                </div>
                                @if(!auth()->user()->center)
                                <div class="col-md-12">
                                    <div id="addnew" class="btn badge-success mb-5">
                                        <a href="javascript:newServiceLink()" class="text-white"><i class="fa fa-plus"
                                                aria-hidden="true"></i>
                                            افزودن خدمت </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="tab-pane fade p-t-20" id="categoryContent" role="tabpanel"
                                aria-labelledby="category-tab">
                                <div class="row d-none" id="newcategorytpl">
                                    <div class="col-12 mb-3 mt-2 product_category">
                                        <select class="form-control" name="product_category[]" id="product_category_id1"
                                            onchange="getFinancialPlansTypeByItem(this.id , this.value , 'category')">
                                            <option selected disabled> انتخاب دسته بندی</option>
                                            @foreach ($product_categories as $category)
                                            <option value="{{ $category['id'] }}">{{
                                                $category['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3 mt-2 financial_plans_type">
                                        <select class="form-control" name="category_financial_plans_type[]"
                                            id="financial_plans_type_id1">
                                            <option selected disabled>انتخاب نوع طرح</option>
                                        </select>
                                    </div>

                                    {{-- <div class="col-12 mb-3 mt-2 category_pay_type">
                                        <select class="form-control" name="category_pay_type[]" id="category_pay_type1">
                                            <option selected disabled>انتخاب نوع پرداخت</option>
                                            <option value="0">نوع پرداخت - نقدی</option>
                                            <option value="1">نوع پرداخت - اقساطی</option>
                                        </select>
                                    </div> --}}

                                    <div class="col-12 mb-3 mt-2 category_purchase_type">
                                        <select class="form-control" name="category_purchase_type[]"
                                            id="category_purchase_type1">
                                            <option selected disabled>انتخاب نحوه دریافت کالا</option>
                                            <option value="1"> نحوه دریافت کالا - حضوری</option>
                                            <option value="0"> نحوه دریافت کالا - غیرحضوری</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3 mt-2 {{ auth()->user()->center ? 'd-none' : '' }}"
                                        id="delete1">
                                    </div>
                                </div>
                                <div class="newcategory" id="newcategory">
                                    <div>
                                    </div>
                                </div>

                                @if(!auth()->user()->center)
                                <div class="col-md-12">
                                    <div id="addnew" class="btn badge-success mb-5">
                                        <a href="javascript:newCategoryLink()" class="text-white"><i class="fa fa-plus"
                                                aria-hidden="true"></i>
                                            افزودن دسته بندی</a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-12 mb-2 factor-payment-type">
                                    <label>نحوه پرداخت فاکتور</label>
                                    <select class="form-control" name="payment_type" id="payment_type2"
                                        onchange="paymentType(2)">
                                        <option selected value="online">آنلاین</option>
                                        <option value="send_card_number">ارسال شماره کارت</option>
                                        @permission('record_payment')
                                        <option value="card_reader">کارتخوان</option>
                                        <option value="card">کارت به کارت</option>
                                        <option value="cash">پول نقد</option>
                                        @endpermission
                                        <option value="supervisor">دارای سرپرست</option>
                                        <option value="wallet">کسر از کیف پول</option>
                                        <option value="credit">کسر از حواله</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mb-2 d-none" id="number_card2">
                                    <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                                    <input class="form-control" name="number_card" type="number" id="number_card_input2"
                                        minlength="4" maxlength="4">
                                </div>

                                <div class="col-12 col-md-6 mb-2 d-none" id="payment_code2">
                                    <label>شماره پیگیری (4 رقم آخر)</label>
                                    <input class="form-control" name="payment_code" type="number"
                                        id="payment_code_input2" minlength="4" maxlength="4">
                                </div>

                                <div class="col-12 col-md-6 mb-2 d-none" id="bill_image2">
                                    <label>رسید دریافت وجه</label>
                                    <br>
                                    <label for="bill_image_input2" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image"
                                        type="file" id="bill_image_input2">
                                    <div id="bill_image_input2_src" class="show"></div>
                                    <input type="hidden" id="bill_image_input2_path" class="path" name="url_bill_image"
                                        value="">
                                </div>

                                <div class="col-12 mb-2 d-none" id="password2">
                                    <label>رمز عبور کاربر</label>
                                    <input class="form-control" name="password" type="number" id="password_input2">
                                    <p>لطفا رمز عبور 6 رقمی که برای کاربر ارسال شده است را وارد
                                        کنید.</p>
                                </div>

                                <div class="col-12 col-md-4 mb-2 d-none" id="send_password2">
                                    <span class="send_user_password btn btn-success-default text-white px-3"> ارسال رمز
                                        <i class="fa fa-paper-plane mr-1" aria-hidden="true"></i></span>
                                </div>
                                <div class="col-12 mb-2 d-none" id="mobile_supervisor2">
                                    <label>شماره سرپرست</label>
                                    <input class="form-control" name="mobile_supervisor" id="mobile_supervisor_input2"
                                        type="text" minlength="11" maxlength="11">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body containers">
                        <div class="card-header">
                            توضیحات :
                        </div>
                        <textarea name="description" class="border border-secondary form-control mb-3"
                            placeholder="توضیحات مربوط به درخواست کاربر را وارد کنید" required></textarea>
                            <div class="mb-3"><label>انتقال به همکار (درصورت نیاز به انتقال درخواست همکار مربوطه را انتخاب کنید.)</label>
                                <select name="callcenter" class="form-control">
                                    <option value="">انتخاب همکار</option>
                                    @foreach($callcenters as $item)
                                    <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                            <div class="card">
                                <div class="card-header">
                                    <h5>توضیحات مربوط به کدام درخواست می باشد ؟</h5>
                                </div>
                                <div class="card-body border border-info">
                                    <div class="alert alert-info text-center">درخواست های کاربر </div>
                                    <table class="table table-bordered  table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">انتخاب</th>
                                                <th scope="col">موضوع درخواست</th>
                                                <th scope="col">وضعیت</th>
                                                <th scope="col">کالسنتر</th>
                                                <th scope="col">تاریخ ثبت</th>
                                            </tr>
                                        </thead>
                                        <tbody id="in-progress-tasks-tbody">
                                            <tr>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <input type="button" class="btn btn-success send_link_submit" id="send_link_submit" value="ثبت">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
