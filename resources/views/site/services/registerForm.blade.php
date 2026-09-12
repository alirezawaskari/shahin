<div class="col-12 col-md-5 py-5 my-auto mr-auto">
    @foreach($errors->all() as $error)
    <div class="alert alert-danger my-3" role="alert">
        {{ $error }}
    </div>
    @endforeach
    @if(auth()->check() || isset($factor['id']))

    @if(1)
    @if(isset($required) && !is_null($required))
    @if($required == false)
    <div class="rol2">
        <p>برای خرید این خدمت باید ابتدا خدمات زیر را خریداری نمایید.</p>
        @foreach($requireServices as $ser)
        <a href="{{ $ser->path() }}" class="btn btn-orange text-white mb-2">
            {{ $ser->title }}
        </a>
        @endforeach
    </div>
    @else
    @include('site.layouts.add_to_cart', ['service' => $service, 'representation' => 0, 'callcenter' =>
    $callcenter->id])
    @endif
    @else
    @include('site.layouts.add_to_cart', ['service' => $service, 'representation' => 0, 'callcenter' =>
    $callcenter->id])
    @endif
    @else
    <span>شما قبلا این خدمت را خریده اید و امکان خرید مجدد وجود ندارد.</span>
    @endif
    @else
    @include('auth.layout.check_register')
    @include('auth.layout.login')
    <form action="/fastregister" method="POST" class="my-4" id="registerForm">
        @csrf
        <div class="row" id="get-service-box" style="display: none">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <div class="col-12 col-md-6">
                <input class="form-control my-2" type="text" name="name" required placeholder="نام"
                    value="{{ old('name') }}" pattern="^[\u0600-\u06FF\s]+$" title="لطفا با حروف فارسی وارد نمایید">
                @if ($errors->has('name'))
                <span class="d-block text-red text-left">{{ $errors->first('name') }}</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <input class="form-control my-2" type="text" name="family" required placeholder="نام خانوادگی"
                    value="{{ old('family') }}" pattern="^[\u0600-\u06FF\s]+$" title="لطفا با حروف فارسی وارد نمایید">
                @if ($errors->has('family'))
                <span class="d-block text-red text-left">{{ $errors->first('family') }}</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control my-2" type="int" name="gender" id="gender" required>
                    <option value="" disabled selected>جنسیت</option>
                    <option value="0">آقا</option>
                    <option value="1">خانم</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <input class="form-control my-2 phone" type="text" id="user_service_phone" name="phone" required
                    placeholder="شماره موبایل" value="{{ old('phone') }}">
                @if ($errors->has('phone'))
                <span class="d-block text-red text-left">{{ $errors->first('phone') }}</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control my-2" type="int" name="financial_plans_type_id" id="financial_plans_type_id"
                    required>
                    @foreach($financial_plans_types as $item)
                    <option value="{{ $item->id }}" {{ !$item->status ? 'disabled' : '' }}>
                        {{ $item->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control my-2" type="int" name="province_id" id="province_id" required>
                    <option value="">انتخاب استان</option>
                    @foreach(\App\Models\Province::all() as $province)
                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control my-2" type="int" name="city_id" id="city_id" required>
                    <option value="">انتخاب شهر</option>
                    @foreach(\App\Models\City::all() as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-control my-2" type="int" name="area_id" id="area_id">
                    <option value="">انتخاب منطقه</option>
                    @foreach(\App\Models\Area::all() as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <div class="form-check">
                    {{--<input style="cursor: pointer" class="form-check-input form-control border border-warning"
                        type="checkbox" value="" id="invalidCheck2" required>--}}
                    <label class="form-check-label mr-3 alert alert-dark" for="invalidCheck2">
                        توضیحات مربوط به خدمت درخواستی را مطالعه نمودم
                    </label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary text-white px-3 mt-2 btn-block">ثبت نام
                    جدید دریافت خدمت
                </button>
            </div>
        </div>
        <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
    </form>
    {{-- <a href="{{ route('login') }}" class="btn btn-orange text-white px-3 mt-2 btn-block">قبلا ثبت
        نام کرده اید؟اینجا کلیک کنید</a>--}}
    @endif
</div>
