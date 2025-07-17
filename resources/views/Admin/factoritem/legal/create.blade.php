<div class="card-header">
    افزودن
</div>
<div class="row treatment_plan d-none" id="treatment_plan">
    <div class="col-12 col-md-8 mb-3">
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
    <div class="col-12 col-md-4 mb-3 ">
        <label>توضیحات</label>
        <input type="text" name="descriptions[]" class="form-control" placeholder="توضیحات">
    </div>
</div>
<div id="new_treatment_plan">
    <div>
    </div>
</div>
<div class="col-12 mb-2">
    <div onclick="treatmentPlan()" class="btn btn-success mb-2">+</div>
</div>
<hr>
