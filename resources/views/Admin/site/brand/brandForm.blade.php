

@if(isset($request_by)) <input name="request_by" value="{{$request_by}}" class="hidden request_by" title=""> @endif

<div class="row">
    <div class="col-12 col-md-12 mb-3 disInline">
        @if(isset($category))
            <span> ایجاد برند برای دسته بندی : </span> <h5 class="disInline">{{$category->title}}</h5>
            <input name="category_id" value="{{$category->id}}" class="hidden category_id" title="">
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3 disInline">
        <label for="title_lbl">نام فارسی برند <span class="required">*</span> :</label>
        <input id="title" type="text" class="form-control title" name="title"
               placeholder="لطفا نام فارسی برند را وارد کنید" value="{{old('title')}}">
        @if($errors->has('title'))
            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3 disInline">
        <label for="name_lbl"> نام انگلیسی برند<span class="required">*</span> :</label>
        <input id="title_en" type="text" class="form-control title_en" name="title_en"
               placeholder="لطفا نام انگلیسی برند را وارد کنید" value="{{old('title_en')}}">
        @if($errors->has('title_en'))
            <span class="help-block sub-error  "> {{$errors->first('title_en')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-12 mb-5">
        <label for="description_lbl"> شرح برند : <span class="required">*</span> :</label>
        <textarea id="description_lbl" class="form-control description" title="شرح کالا" rows="3" cols="5"
                  maxlength="100"
                  placeholder="توضیحات برند باید بین 70 تا 100 کلمه درباره ی تاریخچه و کالا ها برند باشد"
                  name="description">{{old('description')}}</textarea>
        @if($errors->has('description'))
            <span class="help-block sub-error  "> {{$errors->first('description')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-12 mb-5">
        <label for="type_lbl">نوع برند <span class="required">*</span> :</label>
        <input type="radio" name="type" title="ایرانی" class="type_zero type" value="0">
        <span class="lblRadioBtn font-weight-bold font16">ایرانی </span>

        <input type="radio" name="type" title="خارجی" class="type_one type" value="1">
        <span class="lblRadioBtn font-weight-bold font16">خارجی </span>
    </div>

</div>

<div class="row type_zero_box" style="display: none;">
    <div class="col-12 col-md-6 mb-5 disInline">
        <label>برگه ثبت برند<span class="required">*</span> :</label>
        <p class="grayColor">برگه ثبت سند را با حجم کمتر از ۶ مگابایت و فرمت JPG یا PNG وارد کنید</p>
    </div>

    <div class="col-12 col-md-5 mb-5 disInline">
        <label for="registration_form" class="btn btn-primary custom-file-upload border border-1">
            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
        </label>
        <input id="registration_form" class="d-none file-upload file_upload_ajax" type="file" name="registration_form"/>
        <div id="registration_form_src" class="show disInline"></div>
        <input type="hidden" id="registration_form_path" class="path registration_form_path" name="registration_form_path"
               value="">
        @if($errors->has('registration_form'))
            <span class="help-block sub-error  "> {{$errors->first('registration_form')}} </span>
        @endif
    </div>
</div>
<div class="row type_zero_box" style="display: none;">

    <div class="col-12 col-md-6 mb-5">
        <label for="link_zero_lbl"> و یا لینک قوه قضاییه را پر کنید <span class="required">*</span> : </label>
        <input id="link_zero" type="text" class="form-control link" name="link_zero"
               placeholder="" value="{{old('link_zero')}}">
        @if($errors->has('link_zero'))
            <span class="help-block sub-error  "> {{$errors->first('link_zero')}} </span>
        @endif
    </div>

</div>

<div class="row type_one_box mb-4" style="display: none;">

    <div class="col-12 col-md-6 disInline mb-5">
        <label for="link_lbl">لینک سایت معتبر خارجی<span class="required">*</span> :</label>
        <input id="link_one" type="text" class="form-control link" name="link_one"
               placeholder="" value="{{old('link_one')}}">
        @if($errors->has('link_one'))
            <span class="help-block sub-error  "> {{$errors->first('link_one')}} </span>
        @endif
    </div>

</div>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 disInline">
        <label>لوگوی برند <span class="required">*</span> :</label><br/>
        <p class="grayColor">لوگو برند را با ابعاد 600 * 600 پیکسل و با فرمت PNG ویا JPEG و با پس زمینه سفید بارگذاری کنید</p>
    </div>
    <div class="col-12 col-md-5 mb-3 disInline">
        <label for="logo" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-cloud-upload"></i>
            بارگذاری تصویر</label>
        <input id="logo" class="d-none file-upload file_upload_ajax" type="file" name="logo"/>
        <div id="logo_src" class="show disInline"></div>
        <input type="hidden" id="logo_path" class="path logo_path" name="logo_path" value="">
        @if($errors->has('logo'))
            <span class="help-block sub-error  "> {{$errors->first('logo')}} </span>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-12 mb-3">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
        <button type="button" class="btn btn-success brandStore">ارسال</button>
    </div>
</div>

