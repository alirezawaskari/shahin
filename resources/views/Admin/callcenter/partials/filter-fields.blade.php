{{--
    Requests filter fields. ONE definition shared by:
      - the requests page (Admin.callcenter.all) filter form
      - the "filter by requests" pop-up of the group-message page (Admin.groupsms.create)
    so the two can never drift apart. It must sit inside a <div class="row"> in a form whose
    serialisation is the filter criteria (see Callcenter::scopeApplyCriteria()).

    $withBusinessPicker (default true): the staff-only business picker. The group-message
    pop-up turns it off: the audience is always pinned to the business the campaign is sent from.
--}}
                <div class="col-12 col-md-3 mb-2"><input type="text" name="name" placeholder="نام" class="form-control">
</div>
<div class="col-12 col-md-3 mb-2"><input type="text" name="family" placeholder="نام خانوادگی"
        class="form-control"></div>
<div class="col-12 col-md-3 mb-2"><input type="text" name="user_phone" placeholder="موبایل"
        class="form-control"></div>
<div class="col-12 col-md-3 mb-2"><input type="text" name="description" placeholder="موضوع درخواست"
        class="form-control"></div>
<div class="col-12 col-md-3 mb-2">
    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
    </date-picker>
</div>
<div class="col-12 col-md-3 mb-2">
    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="sex" class="form-control" >
        <option value="all">جنسیت</option>
        <option value="1">خانم</option>
        <option value="0">آقا</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select id="has-factor" name="has_factor" class="form-control">
        <option value="">فاکتور</option>
        <option value="1">دارد</option>
        <option value="0">ندارد</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="type[]" id="task_type" class="custom-select" multiple="multiple">
        <option value="1">پیامک</option>
        <option value="0">سایت</option>
        <option value="3">دیجیتال</option>
        <option value="2">تماس ورودی</option>
        <option value="4">مراجعه حضوری</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="request_type" id="request_type" class="custom-select">
        <option value="all" selected>انتخاب نوع درخواست کاربر</option>
        <option value="link">ارسال کارت ویزیت</option>
        <option value="other">سایر</option>
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="status[]" class="custom-select" id="select_status" multiple="multiple">
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
        <option value="11">نصراف از دریافت پیامک</option>

    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="province" class="custom-select" id="province_id">
        <option value="all">انتخاب استان</option>
        @foreach($provinces as $province)
        <option value="{{$province->id}}">{{$province->name}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <select name="city" class="custom-select select2" id="city_id">
        <option value="all">انتخاب شهر</option>
    </select>
</div>

@include('Admin.filter.productServiceFilterInputs')
<div class="col-12 col-md-3 mb-2">
    <select name="users[]" multiple="multiple" class="custom-select select2" id="user_callcenter">
        @foreach($callcenters as $item)
        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-2"><select name="agents[]" multiple="multiple" id="user_agents"
        class="custom-select select2">
        @foreach($callcenters as $item)
        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-2"><select name="representation" class="custom-select select2">
        <option value="all">انتخاب نمایندگی</option>
        @foreach($representations as $representation)
        <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
        @endforeach
    </select>
</div>
@if($withBusinessPicker ?? true)
    @include('Admin.filter.businessSelect')
@endif
