<div class="card border col-12 mt-5 pt-4 col-l main">
    <h4 class="mb-3 card-header"><i class="fas fa-hourglass-end"></i> اتمام کار</h4>
    <form action="{{ route('factorItem.completion')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
        <input type="hidden" name="status" value="5">
        <input type="hidden" name="status_factor" value="7">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
        @if(!is_null($factorItem['forms']))
        @foreach($factorItem['forms'] as $form)
        @if (in_array('panel_complation_file_work' , $form->data_display_level))
        <div class="col-12 col-md-4 mb-2">
            <div class="card">
                @if(isset($factorItem['factor_values'][$form->name]))
                @foreach($factorItem['factor_values'][$form->name] as $image)
                <a href="{{ asset($image) }}" target="_blank">
                    <img src="{{ $image }}" class="card-img-top" height="150px">
                </a>
                @endforeach
                @else
                <div id="{{ $form->name }}_src" class="show">
                    <img class="card-img-top" height="150px">
                </div>
                <div class="card-body">
                    <label>{{ $form->help }}</label>
                    <br>
                    <label for="{{ $form->name }}" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input name="item[{{ $form->name }}][]" type="file" id="{{ $form->name }}"
                        class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                        accept=".jpg,.jpeg,.png" required>
                    <input type="hidden" id="{{ $form->name }}" class="path"
                        name="item[{{ 'url'.$form->name }}][]">
                </div>
                @endif
            </div>
        </div>
        <div class="col-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="alert alert-dark">تذکر : درصورت اتمام کار دکمه پرینت فرم را انتخاب و پس از  پرینت و امضای فرم توسط کاربر تصویر را آپلود نمایید .</p>
                        </div>
                        <div class="col-md-12">
                            <p class="alert alert-dark" >تذکر : آپلود تصویر برای اتمام پرونده الزامی است.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
        @endif
        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $factorItem->status_factor == 7 ?
        'disabled': ''}}>
    </form>
</div>
