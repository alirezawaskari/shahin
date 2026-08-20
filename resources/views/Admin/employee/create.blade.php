@extends('Admin.master')
@section('title')ایجاد همکار@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد همکار</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">فهرست همکاران</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @include('Admin.layouts.errors')

        <form method="post" action="{{ route('employee.store') }}">
            @csrf
            <div class="row">
                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                    جستجو و اطلاعات کاربر
                </div>

                <div class="col-12 mb-3 mt-3">
                    {{-- Search first: the phone number is the identity. An existing customer is
                         promoted, not duplicated — store() adopts the users row that has it. --}}
                    <div class="alert alert-info mb-0">
                        شماره موبایل کاربر را وارد کنید تا در میان <b>همه کاربران سامانه</b> جستجو شود.
                        اگر کاربر از قبل ثبت شده باشد، همان کاربر به همکار تبدیل می‌شود و کاربر تکراری ساخته نمی‌شود.
                        @if(! $is_super_admin && $business)
                            <br>
                            این همکار برای کسب و کار <b>{{ $business->title }}</b> ثبت می‌شود.
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>موبایل *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="phone" id="employee_phone"
                               value="{{ old('phone') }}" placeholder="09121234567" required>
                        <div class="input-group-append">
                            {{-- A button, not a keystroke listener: the lookup runs when the
                                 operator says the number is complete, so a half-typed number
                                 never fires a request or contradicts itself mid-entry. --}}
                            <button type="button" class="btn btn-primary" id="employee_search">جستجو</button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8 mb-3">
                    <label class="d-block">&nbsp;</label>
                    <div id="employee_user_state" class="alert alert-secondary mb-0 py-2">
                        شماره موبایل را وارد کنید و «جستجو» را بزنید.
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>نام *</label>
                    <input type="text" class="form-control" name="name" id="employee_name" value="{{ old('name') }}" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام خانوادگی *</label>
                    <input type="text" class="form-control" name="family" id="employee_family" value="{{ old('family') }}" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>کد ملی</label>
                    <input type="text" class="form-control" name="melicode" value="{{ old('melicode') }}">
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>جنسیت *</label>
                    <select class="form-control" name="gender" id="employee_gender" required>
                        <option value="0" @selected(old('gender', '0') == '0')>مرد</option>
                        <option value="1" @selected(old('gender') === '1')>زن</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>استان *</label>
                    <select class="form-control" name="province" id="employee_province" required>
                        <option value="">انتخاب استان</option>
                        @foreach(\App\Models\Province::orderBy('name')->get() as $province)
                            <option value="{{ $province->id }}" @selected(old('province') == $province->id)>{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شهر *</label>
                    <select class="form-control" name="city" id="employee_city" required>
                        <option value="">انتخاب شهر</option>
                        @foreach(\App\Models\City::orderBy('name')->get() as $city)
                            <option value="{{ $city->id }}" @selected(old('city') == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>شماره تماس اضطراری</label>
                    <input type="text" class="form-control" name="call" value="{{ old('call') }}">
                </div>
                <div class="col-12 col-md-8 mb-3">
                    <label>آدرس</label>
                    <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                </div>

                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                    اطلاعات همکاری
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان شغلی</label>
                    <input type="text" class="form-control" name="work_title" value="{{ old('work_title') }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شماره تماس کاری</label>
                    <input type="text" class="form-control" name="business_phone" value="{{ old('business_phone') }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب سرپرست</label>
                    <select class="form-control selectMulti" name="parent_id">
                        <option value="">ندارد</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت همکار *</label>
                    <select class="form-control" name="status" required>
                        <option value="1" @selected(old('status', '1') === '1')>فعال</option>
                        <option value="0" @selected(old('status') === '0')>غیرفعال</option>
                    </select>
                    <small class="text-muted">
                        همکار فعال، دسترسی پنل می‌گیرد
                        ({{ $is_super_admin ? 'سطح همکار لاله' : 'سطح همکار کسب و کار' }}).
                    </small>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت نمایش *</label>
                    <select class="form-control" name="show_status" required>
                        <option value="1" @selected(old('show_status', '1') === '1')>نمایان</option>
                        <option value="0" @selected(old('show_status') === '0')>پنهان</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>نقش کاربر</label>
                    <select class="form-control selectMulti" name="role_id">
                        <option value="">بدون نقش</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">تا زمانی که نقشی انتخاب نشود، همکار دسترسی به بخش‌های پنل ندارد.</small>
                </div>

                {{-- Representation and business decide WHICH organisation the new employee
                     lands in, so only لاله's superAdmin sets them; a business admin's employee
                     always joins their own business. --}}
                @if($is_super_admin)
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی</label>
                        <select class="form-control selectMulti" name="admin_representation_id">
                            <option value="">بدون نمایندگی (دفتر تهران)</option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}" @selected(old('admin_representation_id') == $representation->id)>{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>کسب و کار</label>
                        <select class="form-control selectMulti" name="ghorfe_online_list_id">
                            <option value="">بدون کسب و کار (همکار لاله)</option>
                            @foreach($businesses as $item)
                                <option value="{{ $item->id }}" @selected(old('ghorfe_online_list_id') == $item->id)>{{ $item->title }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">هر کاربر تنها می‌تواند همکار یک کسب و کار باشد.</small>
                    </div>
                @endif

                <div class="col-12 col-md-4 mb-3">
                    <label>شعبه</label>
                    <select class="form-control selectMulti" name="center_id">
                        <option value="">بدون شعبه</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" @selected(old('center_id') == $center->id)>{{ $center->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>رمز عبور</label>
                    <input type="password" class="form-control" name="password">
                    <small class="text-muted">خالی بگذارید تا رمز تصادفی ساخته شود (ورود با کد پیامکی).</small>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تکرار رمز عبور</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

                <div class="col-12 my-3 d-flex justify-content-end">
                    <input type="submit" id="employee_submit" class="btn btn-success border-0" value="ایجاد همکار">
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    // The quick-action user search, reused as-is: it looks the number up across ALL users
    // with no business scope, which is what lets a business associate take on someone who
    // has never dealt with their shop.
    function searchEmployeeUser() {
        var phone = $('#employee_phone').val();
        var state = $('#employee_user_state');

        if (String(phone).trim().length !== 11) {
            state.attr('class', 'alert alert-warning mb-0 py-2')
                 .text('شماره موبایل باید ۱۱ رقم باشد.');
            $('#employee_submit').prop('disabled', false);
            return;
        }

        state.attr('class', 'alert alert-secondary mb-0 py-2').text('در حال جستجو...');

        $.ajax({
            type: 'GET',
            url: '{{ route('employee.searchUser') }}',
            data: {phone: phone}
        }).done(function (response) {
            var user = response.data.user || {};
            var associate = response.data.associate || {};

            if (!associate.exists) {
                state.attr('class', 'alert alert-warning mb-0 py-2')
                     .text('کاربری با این شماره ثبت نشده است؛ با تکمیل فرم، کاربر جدید ساخته می‌شود.');
                $('#employee_submit').prop('disabled', false);
                return;
            }

            $('#employee_name').val(user.name || '');
            $('#employee_family').val(user.family || '');
            $('#employee_gender').val(user.sex === null || user.sex === undefined ? '0' : String(user.sex));
            $('#employee_province').val(user.province_id || '');
            fillEmployeeCities(user.province_id, user.city_id);

            if (associate.blocked_by) {
                // Refused by the one-business rule; store() refuses it again server-side.
                state.attr('class', 'alert alert-danger mb-0 py-2').text(associate.blocked_by);
                $('#employee_submit').prop('disabled', true);
                return;
            }

            $('#employee_submit').prop('disabled', false);

            // Not blocked but already staff somewhere: only مدیر سیستم gets here, and saving
            // moves the person to the business chosen below — so say that, don't just refuse.
            if (associate.is_employee) {
                state.attr('class', 'alert alert-warning mb-0 py-2')
                     .text('این کاربر هم اکنون همکار «' + (associate.business || 'یک کسب و کار')
                           + '» است؛ با ثبت این فرم، کسب و کار او تغییر می‌کند.');
                return;
            }

            state.attr('class', 'alert alert-success mb-0 py-2')
                 .text('کاربر یافت شد: ' + (user.name || '') + ' ' + (user.family || '')
                       + (associate.business ? ' — همکار کسب و کار: ' + associate.business : ''));
        });
    }

    // Same endpoint the send-link form uses for its province -> city cascade.
    function fillEmployeeCities(provinceId, selectedCityId) {
        if (!provinceId) {
            return;
        }

        $.ajax({
            url: '/city/select',
            type: 'post',
            dataType: 'json',
            data: {_token: $('meta[name="csrf-token"]').attr('content'), id: provinceId}
        }).done(function (data) {
            var select = $('#employee_city').empty().append('<option value="">انتخاب شهر</option>');
            $.each(data.cities, function (i, city) {
                select.append($('<option>').val(city.id).text(city.name));
            });
            if (selectedCityId) {
                select.val(selectedCityId);
            }
        });
    }

    $('body').on('change', '#employee_province', function () {
        fillEmployeeCities($(this).val(), null);
    });

    $('body').on('click', '#employee_search', function () {
        searchEmployeeUser();
    });

    // Enter in the phone field searches instead of submitting a form the operator has not
    // filled in yet.
    $('body').on('keydown', '#employee_phone', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchEmployeeUser();
        }
    });

    // A number typed after a search invalidates it — the previous verdict was about a
    // different person.
    $('body').on('input', '#employee_phone', function () {
        $('#employee_user_state').attr('class', 'alert alert-secondary mb-0 py-2')
            .text('شماره موبایل را وارد کنید و «جستجو» را بزنید.');
        $('#employee_submit').prop('disabled', false);
    });
</script>
@stop
