{{-- $formItemUploads: may this context actually SAVE an upload?
     Several includers render this partial outside any <form>, or inside one with no submit
     control (print views, the "پرونده" tab, a status that suppresses the save button). There
     the upload widget is a trap: it uploads to the temp store, shows a preview, and the file
     is silently dropped because nothing ever posts the hidden media id. Those includers pass
     ['formItemUploads' => false] to render read-only — saved images still show.
     Defaults to true, so every include that does have a save button is unaffected. --}}
@php($formItemUploads = $formItemUploads ?? true)
{{-- $factorItem is a FactorItem model in some includers and the plain array built by
     TotalFactorItemController::setFactorItemItems() in others. Every $factorItem['...']
     read below works for both (Eloquent is ArrayAccess), but documentItems() needs the
     model, so resolve it once here. Mappers that carry 'factor_item' cost no query; the
     rest fall back to a lookup by id. Null when neither is available - the document
     loops below guard for it. --}}
@php($factorItemModel = $factorItem instanceof \App\Models\FactorItem
        ? $factorItem
        : ($factorItem['factor_item'] ?? \App\Models\FactorItem::find($factorItem['id'] ?? null)))
@switch($form->type)
@case('multi-select')
<div class="col-12 col-md-4 mb-2">
    {!! $form->help !!}
    <select multiple class="form-control select2 bg-secondary" {{ $form->required == true ?
        'required' : '' }} name="item[{{ $form->name }}][]">
        <option value="" disabled>انتخاب کنید</option>
        @foreach($form->beforeformitems as $select_item)
        <option value="{{ $select_item->value }}" class="text-right" {{ isset($factorItem['factor_values'][$form->name]) && in_array($select_item->value , $factorItem['factor_values'][$form->name]) ? 'selected' : ''  }}>{{
            $select_item->title }}</option>
        @endforeach
    </select>
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
@permission('factor_service_edit')
@if (str_contains(request()->path(), 'edit'))
@include('Admin.factoritem.serviceFormItem.defaultServiceFactor.edit')
@else
@include('Admin.factoritem.serviceFormItem.defaultServiceFactor.list')
@endif
@else
@include('Admin.factoritem.serviceFormItem.defaultServiceFactor.list')
@endpermission
@break
@case('factor_dentist')
@permission('factor_service_edit')
@if (str_contains(request()->path(), 'edit'))
@include('Admin.factoritem.serviceFormItem.dentist.edit')
@else
@include('Admin.factoritem.serviceFormItem.dentist.list')
@endif
@else
@include('Admin.factoritem.serviceFormItem.dentist.list')
@endpermission
@break
@case('factor_building')
@permission('factor_service_edit')
@if (str_contains(request()->path(), 'edit'))
@include('Admin.factoritem.serviceFormItem.building.edit')
@else
@include('Admin.factoritem.serviceFormItem.building.list')
@endif
@else
@include('Admin.factoritem.serviceFormItem.building.list')
@endpermission
@break
@case('factor_legal')
@permission('factor_service_edit')
@if (str_contains(request()->path(), 'edit'))
@include('Admin.factoritem.serviceFormItem.legal.edit')
@else
@include('Admin.factoritem.serviceFormItem.legal.list')
@endif
@else
@include('Admin.factoritem.serviceFormItem.legal.list')
@endpermission
@break
@case('factor_skin_hair')
@if (str_contains(request()->path(), 'edit'))
@permission('factor_service_edit')
@include('Admin.factoritem.serviceFormItem.skinHair.edit')
@else
@include('Admin.factoritem.serviceFormItem.skinHair.list')
@endif
@else
@include('Admin.factoritem.serviceFormItem.skinHair.list')
@endpermission
@break
@case('factor')
<div class="col-12 col-md-12 mb-3">
    {!! $form->help !!}
</div>
<div class="row col-md-12">
    <table class="table table-bordered table-striped table-responsive-md">
        <tr>
            <th>تصویر</th>
            <th>نام کالا</th>
            <th>ویژگی</th>
            <th>مبلغ</th>
            <th>اقدام</th>
        </tr>
        @foreach ($factorItem['products'] as $product)
        <tr>
            <td><img src="{{ env('APP_URL').'/'.$product->product->image }}" height="100px" width="100px"></td>
            <td>{{ $product->product->name }}</td>
            <td>{{ $product->attribute_item->attribute->title.' :
                '.$product->attribute_item->title }}</td>
            <td> {{ number_format($product->price) }}
                ریال
            </td>
            <td>
                <a href="{{ url('service/factor-delete/' . $product->product_id) }}"><i class="fa fa-trash"
                        aria-hidden="true"></i></a>
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
        name="totalPrice" oninput="separatePrices(this)">
    <p class="text-warning">همکار گرامی لطفا فقط جمع کل فاکتور را بصورت عددی وارد کنید.</p>
</div>
@break
@case('factor-image')
{{-- sanitized DOM id (see the multi_file branch) --}}
@php($domId = preg_replace('/[^A-Za-z0-9_]/', '_', $form->name))
@if($formItemUploads)
<div class="col-12 row">
    <div class="col-md-4">
        <div id="addnew" class="mb-5">
            <label for="{{ $domId }}" class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-upload ml-1" aria-hidden="true"></i>افزودن {!! $form->help !!}
            </label>
            <input name="item[{{ $form->name }}][]" type="file" id="{{ $domId }}"
                class="d-none filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                accept=".jpg,.jpeg,.png">
        </div>
    </div>
    <div id="{{ $domId }}_newlink" class="col-12 row">
        <div>
        </div>
    </div>
    <div id="{{ $domId }}_newlinktpl">
        <div id="{{ $domId }}_src" class="show"></div>
        <input type="hidden" id="{{ $domId }}_path" class="path" name="item[{{ 'url'.$form->name }}][]">
    </div>
</div>
@endif
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
</div>
{{-- DOM ids are sanitized: form names contain spaces/apostrophes ("Medical prescription",
     "Doctor's order") and a raw name produces an id the widget's jQuery selectors cannot
     match (#Medical prescription_src), so no preview renders. The submitted name[] keeps
     the real form name. --}}
@php($domId = preg_replace('/[^A-Za-z0-9_]/', '_', $form->name))
@if($formItemUploads)
<div class="col-12 row">
    <div class="col-md-4">
        <div id="addnew" class="mb-5">
            <label for="{{ $domId }}" class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-upload ml-1" aria-hidden="true"></i>افزودن تصویر
            </label>
            <input name="item[{{ $form->name }}][]" type="file" id="{{ $domId }}"
                class="d-none filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                accept=".jpg,.jpeg,.png">
        </div>
    </div>
    <div id="{{ $domId }}_newlink" class="col-12 row">
        <div>
        </div>
    </div>
    <div id="{{ $domId }}_newlinktpl">
        <div id="{{ $domId }}_src" class="show"></div>
        <input type="hidden" id="{{ $domId }}_path" class="path" name="item[{{ 'url'.$form->name }}][]">
    </div>
</div>
@endif
<div class="col-12 row">
    @foreach($factorItemModel ? $factorItemModel->documentItems($form->name) : [] as $doc)
    <div class="card col-md-3">
        <a href="{{ $doc['url'] }}" target="_blank">
            {{-- object-fit:contain — height alone stretches non-4:3 uploads --}}
            <img src="{{ $doc['url'] }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
        </a>
        @if($doc['legacy'])
        {{-- same hint as Admin/partials/currentMedia: still a legacy path, adopted on save --}}
        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
        @endif
    </div>
    @endforeach
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
    placeholder="{{ $form->placeholder }}" value="{{ isset($factorItem['factor_values'][$form->name]) ?
    $factorItem['factor_values'][$form->name] : $user[$form->name] }}"
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
@case('calendar')
<div class="col-12 col-md-4 mb-3">
    {!! $form->help !!}
    <date-picker
        value="{{ isset($factorItem['factor_values'][$form->name]) ? $factorItem['factor_values'][$form->name] : $user[$form->name] }}"
        format="YYYY/M/D" name="item[{{ $form->name }}]" type="date" display-format="YYYY/MM/DD">
    </date-picker>
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
{{-- Sanitized DOM id (see the multi_file branch). The hidden path input previously reused
     the file input's id verbatim — two elements with the same id — so the widget could
     never write the uploaded media id into it and the upload was dropped on save. --}}
@php($domId = preg_replace('/[^A-Za-z0-9_]/', '_', $form->name))
<div class="card ol-12 col-md-3 mb-2">
    @php($savedDoc = $factorItemModel ? ($factorItemModel->documentItems($form->name)[0] ?? null) : null)
    @if(!$savedDoc)
    <div id="{{ $domId }}_src" class="show">
        <img class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
    </div>
    @else
    <div id="{{ $domId }}_src" class="show">
        <a href="{{ $savedDoc['url'] }}" target="_blank">
            <img src="{{ $savedDoc['url'] }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
        </a>
        @if($savedDoc['legacy'])
        {{-- same hint as Admin/partials/currentMedia: still a legacy path, adopted on save --}}
        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
        @endif
    </div>
    @endif
    <div class="card-body">
        <h5 class="card-title">{!! $form->help !!}
        </h5>
        @if($formItemUploads)
        <label for="{{ $domId }}" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2"
                aria-hidden="true"></i>انتخاب
            تصویر </label>
        <input name="item[{{ $form->name }}]" id="{{ $domId }}" type="file"
            class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
        <input type="hidden" id="{{ $domId }}_path" class="path" name="item[{{ 'url' . $form->name }}]" value="">
        @endif
    </div>
</div>
@endif
@break
@case('rules')
<div class="col-12 bg-white">
    {!! $form->help !!}
    @if ($factorItem->contractView)
        {!! $factorItem->contractView->contract !!}
    @else
        {!! $form->help !!}
    @endif
</div>
@break
@endswitch
