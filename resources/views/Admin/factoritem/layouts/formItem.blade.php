@switch($form->type)
@case('factor_dentist')
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
        <select class="form-control" name="tariff[]" id="tariff1" onchange="selectByTariff(this.id , this.value)">
            @foreach ($tariffs as $tariff)
            <option value="{{ $tariff->id }}">{{ $tariff->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6 mb-3">
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
    <div class="col-12 col-md-3 mb-3">
        <label>تعداد جلسات</label>
        <input type="number" name="session_number[]" id="session_number1" class="form-control"
            placeholder="تعداد جلسات درمان" value="1" min="1">
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label>طول درمان (روز)</label>
        <input type="number" name="treatment_duration[]" id="treatment_duration1" class="form-control"
            placeholder="طول درمان(روز)" value="1" min="1">
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
@break
@case('factor_building')
<div class="card-header">
    ایجاد فاکتور
</div>
<div class="row treatment_plan d-none" id="treatment_plan">
    <div class="col-12 col-md-3 mb-3">
    </div>
    <div class="col-12 col-md-3 mb-3">
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label>مقدار(براساس متراژ - عدد)</label>
        <input type="number" name="value[]" class="form-control" placeholder="مقدار" min="1">
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label>تعرفه</label>
        <select class="form-control" name="tariff[]" id="tariff1" onchange="selectByTariff(this.id , this.value)">
            @foreach ($tariffs as $tariff)
            <option value="{{ $tariff->id }}">{{ $tariff->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>عنوان فاکتور</label>
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
    <div class="col-12 col-md-3 mb-3">
        <label>تعداد مراجعات جهت انجام کار</label>
        <input type="number" name="session_number[]" id="session_number1" class="form-control"
            placeholder="تعداد مراجعات" value="1" min="1">
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label> طول  دوره اجرا(روز)</label>
        <input type="number" name="treatment_duration[]" id="treatment_duration1" class="form-control"
            placeholder="طول دوره اجرا (روز)" value="1" min="1">
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
@break
@case('multi-select')
<div class="col-12 col-md-4 mb-2">
    {!! $form->help !!}
    <select multiple class="form-control select2 {{ $form->class }}" {{ $form->required == true ?
        'required'
        : '' }} name="item[{{ $form->name }}][]">
        @if($form->name == 'center-agent')
        <option disabled>انتخاب پزشک</option>
        @foreach($center_agents as $doctor)
        @if(isset($factorItem['factor_values'][$form->name]))
        <option value="{{ $doctor->id }}" {{ in_array( $doctor->id,
            $factorItem['factor_values'][$form->name])
            ?
            'selected' : '' }}>
            {{ $doctor->name }} {{ $doctor->family }}
        </option>
        @else
        <option value="{{ $doctor->id }}">
            {{ $doctor->name }} {{ $doctor->family }}
        </option>
        @endif
        @endforeach
        @else
        @foreach($form->beforeformitems as $select_item)
        <option value="{{ $select_item->value }}" class="text-right">{{ $select_item->title }}</option>
        @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-4 mb-2">
    <label>تاریخ مراجعه اولیه کاربر</label>
    <date-picker value="{{ $factorItem['date'] }}" format="YYYY/M/D HH:mm:ss"
        name="date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="new_date">
    </date-picker>
</div>
@break
@case('textarea')
<div class="col-12 mb-2">
    {!! $form->help !!}
    <textarea class="form-control {{ $form->class }}" {{
        $form->required == true ? 'required' : '' }} name="item[{{ $form->name }}]"> {{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : $user[$form->name] }}</textarea>
</div>
@break
@case('confirm')
@role($form->name)
<input type="hidden" name="item[{{ $form->name }}]" value="{{auth()->user()->id}}">
@endrole
@break
@case('factor_service')
<div class="col-12 col-md-12 mb-3">
    {!! $form->help !!}
</div>
<div class="col-12 mb-3">
    <input type="hidden" name="status_factor" value="{{ $factorItem['status_factor'] }}">
    <select class="form-control select2" name="service_tariff_id[]" multiple id="confirm">
        @foreach ($center_service_tariffs as $service_tariff)
        <option value="{{ $service_tariff->id }}">
            {{ $service_tariff->name }} (
            {{ number_format($service_tariff->price) }} ریال -
            تعرفه {{ $service_tariff->tariff->title }})
        </option>
        @endforeach
    </select>
</div>
<div class="row col-md-12">
    <table class="table table-bordered table-striped table-responsive-md">
        @foreach ($factorItem['service_tariffs'] as $service_tariff)
        <tr>
            <td>{{ $service_tariff->name }}</td>
            <td>{{ $service_tariff->pivot->value }}
            </td>
            <td> {{ number_format($service_tariff->pivot->price) }}
                ریال
            </td>
            <td>
                <a class="factor_item_service_tariff_delete" data-id="{{ $service_tariff->pivot->id }}"><i
                        class="fa fa-trash font-size-18" aria-hidden="true"></i></a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@break
@case('other-price')
<div class="col-12 col-md-4 mb-2">
    {!! $form->help !!}
    <input class="form-control {{ $form->class }}" name="item[{{ $form->name }}]"
        value="{{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : 0 }}">
</div>
@break
@case('total-price')
<div class="col-12 mb-2">
    {!! $form->help !!}
    <input class="col-4 form-control" type="text" value="{{ number_format($factorItem['total_price'])}}" name="totalPrice"
        oninput="separatePrices(this)">
</div>
@break
@case('factor-image')
<div class="col-12 row">
    <div class="col-md-4">
        <div id="addnew" class="file file--upload mb-5">
            <label for="{{ $form->name }}">
                <i class="fa fa-upload ml-1" aria-hidden="true"></i>افزودن {!! $form->help !!}
            </label>
            <input name="item[{{ $form->name }}][]" type="file" id="{{ $form->name }}"
                class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                accept=".jpg,.jpeg,.png">
        </div>
    </div>
    <div id="{{ $form->name }}_newlink" class="col-12 row">
        <div>
        </div>
    </div>
    <div id="{{ $form->name }}_newlinktpl">
        <div id="{{ $form->name }}_src" class="show"></div>
        <input type="hidden" id="{{ $form->name }}_path" class="path" name="item[{{ 'url'.$form->name }}][]">
    </div>
</div>
<div class="col-12 row">
    @if(isset($factorItem['factor_image']))
    @foreach($factorItem['factor_image'] as $image)
    <div class="card col-md-3">
        <a href="{{ asset($image) }}" target="_blank">
            <embed src="{{ $image }}" class="card-img-top" height="150px">
        </a>
    </div>
    @endforeach
    @endif
</div>
@break
@case('multi_file')
<div class="col-12 row">
    <div class="col-md-4">
        <div id="addnew" class="file file--upload mb-5">
            <label for="{{ $form->name }}">
                <i class="fa fa-upload ml-1" aria-hidden="true"></i>افزودن تصویر
            </label>
            <input name="item[{{ $form->name }}][]" type="file" id="{{ $form->name }}"
                class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                accept=".jpg,.jpeg,.png">
        </div>
    </div>
    <div id="{{ $form->name }}_newlink" class="col-12 row">
        <div>
        </div>
    </div>
    <div id="{{ $form->name }}_newlinktpl">
        <div id="{{ $form->name }}_src" class="show"></div>
        <input type="hidden" id="{{ $form->name }}_path" class="path" name="item[{{ 'url'.$form->name }}][]">
    </div>
</div>
<div class="col-12">
    {!! $form->help !!}
    <p>تذکر : آپلود تمامی تصاویر درصورت وجود الزامی است.</p>
</div>
<div class="col-12 row">
    @if(isset($factorItem['factor_values'][$form->name]))
    @foreach($factorItem['factor_values'][$form->name] as $image)
    <div class="card col-md-3">
        <a href="{{ asset($image) }}" target="_blank">
            <img src="{{ $image }}" class="card-img-top" height="150px">
        </a>
    </div>
    @endforeach
    @endif
</div>
@break
@case('number')
<div class="col-12 col-md-3 mb-2">
    {!! $form->help !!}
    <input type="number" class="form-control {{ $form->class }}" {{ $form->required == true ? 'required' :
    '' }}
    placeholder="{{ $form->placeholder }}" value="{{ $user[$form->name] }}"
    name="item[{{ $form->name }}]">
</div>
@break
@case('text')
<div class="col-12 col-md-3 mb-2">
    {!! $form->help !!}
    <input type="text" class="form-control {{ $form->class }}" {{ $form->required == true ? 'required' : ''
    }}
    placeholder="{{ $form->placeholder }}" value="{{ $user[$form->name] }}"
    name="item[{{ $form->name }}]">
</div>
@break
@case('textarea')
<div class="col-12 col-md-12 mb-2">
    {!! $form->help !!}
    <textarea class="form-control {{ $form->class }}" {{
        $form->required == true ? 'required' : '' }} name="item[{{ $form->name }}]"> {{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : $user[$form->name] }}</textarea>
</div>
@break
@case('checkbox')
<div class="col-12 col-md-12 mb-3">
    {!! $form->help !!}
    <br>
    @foreach($form->beforeformitems as $beforeformitem)
    {{ $beforeformitem->title }}
    @if(isset($factorItem['factor_values'][$form->name]))
    <input type="{{ $form->type }}" name="item[{{ $form->name }}][]" value="{{ $beforeformitem->value }}" {{
        in_array($beforeformitem->value , $factorItem['factor_values'][$form->name]) ? 'checked' : '' }}>
    @else
    <input type="{{ $form->type }}" name="item[{{ $form->name }}][]" value="{{ $beforeformitem->value }}">
    @endif
    @endforeach
</div>
@break
@case('radio')
<div class="col-12 col-md-12 mb-3">
    {!! $form->help !!}
    <br>
    @foreach($form->beforeformitems as $beforeformitem)
    {{ $beforeformitem->title }}
    @if(isset($factorItem['factor_values'][$form->name]))
    <input type="{{ $form->type }}" name="item[{{ $form->name }}]" value="{{ $beforeformitem->value }}" {{
        $beforeformitem->value == $factorItem['factor_values'][$form->name] ? 'checked' : '' }} required>
    @else
    <input type="{{ $form->type }}" name="item[{{ $form->name }}]" value="{{ $beforeformitem->value }}" required>
    @endif
    @endforeach
</div>
@break
@case('file')
@if($form->save_data_level == 'user')
<div class="card ol-12 col-md-3 mb-2">
    {!! $form->help !!}
    @if(is_null($user[$form->name]))
    <p class="my-4 text-danger text-center">ثبت نشده</p>
    <input name="item[{{ $form->name }}]" type="file" class="filestyle" data-buttonname="btn-white">
    @else
    <a href="{{ asset($user[$form->name]) }}">
        <img src="{{ asset($user[$form->name]) }}" class="card-img-top">
    </a>
    @endif
    <div class="card-body">
        <h5 class="card-title">{!! $form->help !!}</h5>
    </div>
</div>
@else
<div class="card ol-12 col-md-3 mb-2">
    @if(!isset($factorItem['factor_values'][$form->name]))
    <div id="{{ $form->name }}_src" class="show">
        <img class="card-img-top" height="150px">
    </div>
    @else
    <div id="{{ $form->name }}_src" class="show">
        <a href="{{ asset($factorItem['factor_values'][$form->name]) }}" target="_blank">
            <img src="{{ asset($factorItem['factor_values'][$form->name]) }}" class="card-img-top" height="150px">
        </a>
    </div>
    @endif
    <div class="card-body">
        <h5 class="card-title">{!! $form->help !!}
        </h5>
        <label for="{{ $form->name }}" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2"
                aria-hidden="true"></i>انتخاب
            تصویر </label>
        <input name="item[{{ $form->name }}]" id="{{ $form->name }}" type="file"
            class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
        <input type="hidden" id="{{ $form->name }}" class="path" name="item[{{ 'url' . $form->name }}]" value="">
    </div>
</div>
@endif
@break
@endswitch
