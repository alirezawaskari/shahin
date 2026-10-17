{{--
    Requests filter fields. ONE definition shared by:
      - the requests page (Admin.callcenter.all) filter form
      - the "filter by requests" pop-up of the group-message page (Admin.groupsms.create)
    so the two can never drift apart. It must sit inside a <div class="row"> in a form whose
    serialisation is the filter criteria (see Callcenter::scopeApplyCriteria()).

    $withBusinessPicker (default true): the staff-only business picker. The group-message
    pop-up turns it off: the audience is always pinned to the business the campaign is sent from.
--}}
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">نام</label>
    <input type="text" name="name" placeholder="نام" class="form-control form-control-sm">
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">نام خانوادگی</label>
    <input type="text" name="family" placeholder="نام خانوادگی" class="form-control form-control-sm">
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">شماره همراه</label>
    <input type="text" name="user_phone" placeholder="09xxxxxxxxx" class="form-control form-control-sm">
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">موضوع درخواست</label>
    <input type="text" name="description" placeholder="موضوع درخواست" class="form-control form-control-sm">
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">از تاریخ ثبت</label>
    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate" class="form-control-sm w-100">
    </date-picker>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">تا تاریخ ثبت</label>
    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate" class="form-control-sm w-100"></date-picker>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">جنسیت</label>
    <select name="sex" class="custom-select form-select form-select-sm">
        <option value="all">همه جنسیت‌ها</option>
        <option value="1">خانم</option>
        <option value="0">آقا</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">وضعیت فاکتور</label>
    <select id="has-factor" name="has_factor" class="custom-select form-select form-select-sm">
        <option value="">همه</option>
        <option value="1">دارد</option>
        <option value="0">ندارد</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">نوع ورود درخواست</label>
    <select name="type[]" id="task_type" class="custom-select select2 form-select form-select-sm" multiple="multiple" data-placeholder="انتخاب روش ثبت (پیامک، سایت، ...)">
        <option value="1">پیامک</option>
        <option value="0">سایت</option>
        <option value="3">دیجیتال</option>
        <option value="2">تماس ورودی</option>
        <option value="4">مراجعه حضوری</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">نوع درخواست کاربر</label>
    <select name="request_type" id="request_type" class="custom-select form-select form-select-sm">
        <option value="all" selected>همه درخواست‌ها</option>
        <option value="link">ارسال کارت ویزیت</option>
        <option value="other">سایر</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">وضعیت پرونده / درخواست</label>
    <select name="status[]" class="custom-select select2 form-select form-select-sm" id="select_status" multiple="multiple" data-placeholder="انتخاب وضعیت‌ها">
        <option value="0">جدید</option>
        <option value="1">در انتظار پرداخت</option>
        <option value="2">معلق</option>
        <option value="3">پرداخت شده</option>
        <option value="4">نیاز به تماس</option>
        <option value="5">بسته شده</option>
        <option value="6">در حال انقضا</option>
        <option value="7">عودت پرداخت</option>
        <option value="8">بایگانی</option>
        <option value="9">منقضی شده</option>
        <option value="10">اقدام شده</option>
        <option value="11">انصراف از دریافت پیامک</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">استان</label>
    <select name="province" class="custom-select select2 form-select form-select-sm" id="province_id" data-placeholder="انتخاب استان">
        <option value="all">همه استان‌ها</option>
        @if(isset($provinces))
            @foreach($provinces as $province)
            <option value="{{$province->id}}">{{$province->name}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">شهر</label>
    <select name="city" class="custom-select select2 form-select form-select-sm" id="city_id" data-placeholder="انتخاب شهر">
        <option value="all">همه شهرها</option>
    </select>
</div>

@include('Admin.filter.productServiceFilterInputs')

<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">کارشناس / کالسنتر</label>
    <select name="users[]" multiple="multiple" class="custom-select select2 form-select form-select-sm" id="user_callcenter" data-placeholder="انتخاب کارشناس">
        @if(isset($callcenters))
            @foreach($callcenters as $item)
            <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">بازاریاب</label>
    <select name="agents[]" multiple="multiple" id="user_agents" class="custom-select select2 form-select form-select-sm" data-placeholder="انتخاب بازاریاب">
        @if(isset($callcenters))
            @foreach($callcenters as $item)
            <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">نمایندگی</label>
    <select name="representation" class="custom-select select2 form-select form-select-sm" id="representation_id" data-placeholder="انتخاب نمایندگی">
        <option value="all">همه نمایندگی‌ها</option>
        @if(isset($representations))
            @foreach($representations as $representation)
            <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
            @endforeach
        @endif
    </select>
</div>
@if($withBusinessPicker ?? true)
    @include('Admin.filter.businessSelect')
@endif
