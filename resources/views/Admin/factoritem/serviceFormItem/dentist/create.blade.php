<div class="card-header">
    افزودن
</div>
<div class="row treatment_plan d-none" id="treatment_plan">
    <div class="col-12 col-md-3 mb-3">
        <label>شماره دندان</label>
        <input type="text" name="dentist_number[]" class="form-control" placeholder="شماره دندان">
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label>موقعیت دندان</label>
        <select class="form-control" name="dentist_position[]">
            <option value="بالا چپ">بالا چپ</option>
            <option value="پایین چپ">پایین چپ</option>
            <option value="بالا راست">بالا راست</option>
            <option value="پایین راست">پایین راست</option>
            <option value="کل درمان">کل درمان</option>
        </select>
    </div>
    <div class="col-12 col-md-3 mb-2">
        <label>پزشک</label>
        <select class="form-control" name="doctor_id[]">
            <option value="">بدون پزشک</option>
            @foreach ($center_agents as $doctor)
                <option value="{{ $doctor->id }}">
                    {{ $doctor->name }} {{ $doctor->family }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label>بازه زمانی تعرفه</label>
        <select class="form-control" name="tariff[]" id="tariff1"
                onchange="selectByTariff(this.id , this.value)">
            @foreach ($tariffs as $tariff)
                <option value="{{ $tariff->id }}">{{ $tariff->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-5 mb-3">
        <label>تعرفه</label>
        <select class="form-control" name="service_tariff_id[]" id="service_tariff1" ondragend="prices()"
                onchange="getSessionNumber(this.id , this.value)">
            <option value="" selected disabled>انتخاب کنید</option>
            @foreach ($center_service_tariffs as $service_tariff)
                <option value="{{ $service_tariff->id }}">
                    {{ $service_tariff->name }} (
                    {{ number_format($service_tariff->price) }} ریال -
                    تعرفه {{ $service_tariff->tariff->title }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-3 mb-3 ">
        <label>توضیحات</label>
        <input type="text" name="description[]" class="form-control" placeholder="توضیحات" maxlength="100">
    </div>
    <div class="col-12 col-md-2 mb-3">
        <label>تعداد جلسات</label>
        <input type="number" name="session_number[]" id="session_number1" class="form-control"
               placeholder="تعداد جلسات درمان" value="1" min="1">
    </div>
    <div class="col-12 col-md-2 mb-3">
        <label>طول درمان (روز)</label>
        <input type="number" name="treatment_duration[]" id="treatment_duration1" class="form-control"
               placeholder="طول درمان(روز)" value="1" min="1">
    </div>
    @permission('change-price-tariff')
    <div class="col-12 col-md-3 mb-3">
        <label>قیمت</label>
        <input type="number" name="price[]" id="price1" class="form-control"
               placeholder="قیمت"  min="1">
    </div>
    @endpermission
</div>
<div id="new_treatment_plan">
    <div>
    </div>
</div>
<div class="col-12 mb-2">
    <div onclick="treatmentPlan()" class="btn btn-success mb-2">+</div>
</div>
<hr>
