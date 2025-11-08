<div class="card border col-12 mt-5 pt-4 col-l main ">
    <div class="card-body">
        @php($message = Session::get('message'))
        <form action="{{ route('factorItem.updateServiceTariff' , [$factorItem['resnumber']])}}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @if(!is_null($factorItem['forms']))
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_factor' , $form->data_display_level))
            @include('Admin.factoritem.layouts.formItem')
            @endif
            @endforeach
            @endif
        </form>
    </div>
    <div class="col-md-12 border border-secondary box mb-5">
        <form action="{{ route('factorItem.createFactor' , [ $factorItem['resnumber']])}}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @foreach($factorItem['forms'] as $form)
            @if (in_array('panel_factor_create' , $form->data_display_level))
            @switch($form->type)
            @case('total-price')
            <div class="col-12 mb-2">
                {!! $form->help !!}
                <input class="col-md-4 form-control" type="text" value="{{ number_format($factorItem['total_price'])}}"
                    name="totalPrice" oninput="separatePrices(this)">
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
                    <input type="hidden" id="{{ $form->name }}_path" class="path"
                        name="item[{{ 'url'.$form->name }}][]">
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
            @case('textarea')
            <div class="col-12 col-md-12 mb-2">
                {!! $form->help !!}
                <textarea class="form-control {{ $form->class }}" {{
                    $form->required == true ? 'required' : '' }} name="item[{{ $form->name }}]"> {{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : $user[$form->name] }}</textarea>
            </div>
            @break
            @case('factor_service')
            @include('Admin.factoritem.serviceFormItem.defaultServiceFactor.create')
            @break
            @case('factor_dentist')
            @include('Admin.factoritem.serviceFormItem.dentist.create')
            @break
            @case('factor_skin_hair')
            @include('Admin.factoritem.serviceFormItem.skinHair.create')
            @break
            @case('factor_building')
            @include('Admin.factoritem.serviceFormItem.building.create')
            @break
            @case('factor_legal')
            @include('Admin.factoritem.serviceFormItem.legal.create')
            @break
            @endswitch
            @endif
            @endforeach
            <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}" id="factorItem">
            <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
            <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
            <div class="row">
                <input type="hidden" value="{{ $factorItem['id'] }}" name="id">
                <div class="col-12 col-md-12 mb-12">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck" name="checked">
                        </div>
                    </div>
                </div>
                <p></p>
                <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" id="btnSave">
            </div>
        </form>
    </div>
    @if(!$factorItem['complete'])
    <form action="{{ route('factorItem.sendNotificationRecordingFactor' , [ $factorItem['resnumber']])}}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="alert alert-danger">
            <p>
                ملاحظات :
            </p>
            <p>
                همکار محترم بعد از فشردن کلید ثبت نهایی پیامک حاوی فاکتور برای بیمار ارسال شده و در پرونده نامبرده نمایش
                داده میشود.
            </p>
        </div>
        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت نهایی و ارسال فاکتور برای کاربر">
    </form>
    @else
    <div class="alert alert-success">
        <p>
            این فاکتور توسط کاربر تایید شده است.
        </p>
    </div>
    @endif
</div>
