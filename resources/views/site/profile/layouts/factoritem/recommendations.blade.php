{{--
    Doctor "توصیه ها و دستورات" (recommendations) section of the public
    /factorItem/{resnumber} page.

    Only the recommendation forms (data_display_level contains
    'show_recommendations_site') are rendered here. When the file is still active
    (recommendationsEditable() -> status_factor 6/10) the patient gets ajax upload
    widgets + a save button; otherwise the section is READ-ONLY (saved images/text
    only). Uploads persist through FactorItemController@saveRecommendations, which
    moves the temp media into the factor item's Spatie collection.

    Expects $factorItem (model) and $item (array with 'forms') from the parent scope.
--}}
@php
    $recEditable = \App\Http\Controllers\FactorItemController::recommendationsEditable($factorItem);
    $recForms = collect($item['forms'] ?? [])->filter(function ($f) {
        return is_array($f->data_display_level) && in_array('show_recommendations_site', $f->data_display_level);
    });
@endphp

@if($recForms->count())
    @if($recEditable)
    <form action="{{ route('site.factorItem.saveRecommendations') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
    @endif

        @foreach($recForms as $form)
        @switch($form->type)

        @case('multi_file')
        {{-- DOM ids must be free of spaces/quotes (some form names contain them and break
             jQuery selectors). Sanitize for ids only; the submit field names keep the real
             $form->name so the controller keys factor_values correctly. --}}
        @php($domId = preg_replace('/[^A-Za-z0-9_]/', '_', $form->name))
        <div class="col-12 mb-2">
            {!! $form->help !!}
        </div>
        @if($recEditable)
        <div class="col-12 row">
            <div class="col-md-4">
                <div id="addnew" class="file file--upload mb-3">
                    <label for="{{ $domId }}" class="btn btn-primary custom-file-upload border border-1">
                        <i class="fa fa-upload ml-1" aria-hidden="true"></i>افزودن تصویر
                    </label>
                    <input name="item[{{ $form->name }}][]" type="file" id="{{ $domId }}"
                        class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                        accept=".jpg,.jpeg,.png">
                </div>
            </div>
            <div id="{{ $domId }}_newlink" class="col-12 row">
                <div></div>
            </div>
            <div id="{{ $domId }}_newlinktpl" class="d-none">
                <div id="{{ $domId }}_src" class="show"></div>
                <input type="hidden" id="{{ $domId }}_path" class="path" name="item[{{ 'url'.$form->name }}][]">
            </div>
        </div>
        @endif
        <div class="col-12 row mb-4">
            @php($savedImages = $factorItem['factor_values'][$form->name] ?? [])
            @if(is_array($savedImages) && count($savedImages))
                @foreach($savedImages as $image)
                <div class="card col-md-3 mb-2">
                    <a href="{{ $image }}" target="_blank">
                        <img src="{{ $image }}" class="card-img-top"
                            style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                    </a>
                </div>
                @endforeach
            @elseif(!$recEditable)
                <p class="my-3 text-muted text-center w-100">بدون تصویر</p>
            @endif
        </div>
        @break

        @case('textarea')
        <div class="col-12 mb-3">
            {!! $form->help !!}
            @php($savedText = $factorItem['factor_values'][$form->name] ?? '')
            @if($recEditable)
            <textarea class="form-control {{ $form->class }}" name="item[{{ $form->name }}]" rows="4">{{ $savedText }}</textarea>
            @else
            <p class="form-control" style="min-height:60px;white-space:pre-wrap;">{{ $savedText }}</p>
            @endif
        </div>
        @break

        @default
        @include('Admin.factoritem.layouts.formItem')
        @endswitch
        @endforeach

    @if($recEditable)
        <div class="col-12">
            <button type="submit" class="btn btn-primary px-4 my-3">ثبت توصیه‌ها و تصاویر</button>
        </div>
    </form>
    @endif
@endif
