@if(isset($category_center_id) && $category_center_id != 0)
    <input type="hidden" class="category_center_id" value="{{$category_center_id}}">
@endif

@php $preselected_category = $wizard_category ?? null; @endphp

@if($preselected_category)
    {{-- آمده از ویزارد: دسته‌بندی مرحله قبل از پیش انتخاب شده تا کاربر مجبور به
         انتخاب دوباره نشود. جستجو همچنان در دسترس است تا بتواند تغییرش دهد. --}}
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 mrgB15">
            <label>دسته بندی انتخاب شده <span class="required">*</span> :</label>
            <div class="alert alert-success d-flex align-items-center justify-content-between flex-wrap">
                <label class="mb-0">
                    <input name="choose_category_id" class="choose_category_id" type="radio"
                           value="{{$preselected_category->id}}" checked>
                    <span class="fw-bold">{{$preselected_category->title}}</span>
                    <span class="text-muted">{{\App\Models\Category::findPath($preselected_category->id)}}</span>
                </label>
                <button type="button" class="btn btn-sm btn-outline-secondary changeWizardCategory">
                    تغییر دسته بندی
                </button>
            </div>
        </div>
    </div>
@endif

<div class="row {{ $preselected_category ? 'wizardCategorySearchBox' : '' }}"
     @if($preselected_category) style="display: none;" @endif>
    <div class="col-md-6 col-sm-12 col-xs-12 mrgB15">
        <label for="parent_id_lbl">جستجو گروه کالا  <span class="required">*</span> :</label><br/>
        <input class="form-control searchCategory" name="category" placeholder="لطفا بیش از سه حرف تایپ کنید"  type="text">
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 ">
        <p>نتایج جستجو گروه ها</p><hr/>
        <div id="category-suggestion-box" style="display: none;"></div>
    </div>
</div>
@if($errors->has('choose_category_id'))
    <span class="help-block sub-error">{{$errors->first('choose_category_id')}}</span>@endif
