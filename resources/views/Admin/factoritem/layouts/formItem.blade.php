@switch($form->type)
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
                        {{ number_format($service_tariff->price) }} ریال - {{ $service_tariff->tariff->title }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="row col-md-12">
            <table class="table table-bordered table-striped table-responsive-md">
                <tr>
                    <th>#</th>
                    <th>وضعیت اجرا</th>
                    <th>عنوان</th>
                    <th>مبلغ(ریال)</th>
                    <th>حذف</th>
                </tr>
                @foreach ($factorItem['service_tariffs'] as $service_tariff)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <ul class="circle work-step"
                                onclick="stepWorkDiagram({{ $service_tariff->pivot->session_number }} , {{ $service_tariff->pivot->id }} , {{ App\Models\Session::where('factor_item_service_tariff_id' , $service_tariff->pivot->id)->where('status' , 1)->count() }})">
                                @for($i = 1 ; $i <= $service_tariff->pivot->session_number ; $i++)
                                    <li id="part_{{ $service_tariff->pivot->id.'_'.$i }}">
                                        <div class="text"></div>
                                    </li>
                                @endfor
                            </ul>
                            <div class="circle smaller-circle">
                                <p id="percent_{{ $service_tariff->pivot->id }}">
                                <p>
                            </div>
                        </td>
                        <td>{{ $service_tariff->name }}</td>
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
            <input class="col-md-4 form-control" type="text" value="{{ number_format($factorItem['total_price'])}}"
                   name="totalPrice"
                   oninput="separatePrices(this)">
            <p class="text-warning">همکار گرامی لطفا فقط جمع کل فاکتور را بصورت عددی وارد کنید.</p>
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
        <div class="col-12">
            {!! $form->help !!}
            <p>تذکر : آپلود تمامی تصاویر درصورت وجود الزامی است.</p>
        </div>
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
                    <input type="{{ $form->type }}" name="item[{{ $form->name }}][]"
                           value="{{ $beforeformitem->value }}" {{
        in_array($beforeformitem->value , $factorItem['factor_values'][$form->name]) ? 'checked' : '' }}>
                @else
                    <input type="{{ $form->type }}" name="item[{{ $form->name }}][]"
                           value="{{ $beforeformitem->value }}">
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
                    <input type="{{ $form->type }}" name="item[{{ $form->name }}]" value="{{ $beforeformitem->value }}"
                           required>
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
                            <img src="{{ asset($factorItem['factor_values'][$form->name]) }}" class="card-img-top"
                                 height="150px">
                        </a>
                    </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{!! $form->help !!}
                    </h5>
                    <label for="{{ $form->name }}" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2"
                            aria-hidden="true"></i>انتخاب
                        تصویر </label>
                    <input name="item[{{ $form->name }}]" id="{{ $form->name }}" type="file"
                           class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                           accept=".jpg,.jpeg,.png">
                    <input type="hidden" id="{{ $form->name }}" class="path" name="item[{{ 'url' . $form->name }}]"
                           value="">
                </div>
            </div>
        @endif
        @break
@endswitch
