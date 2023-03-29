<div class="col-12 col-md-12 mb-3">
    <h4 class="text-center">اطلاعات پک </h4>
</div>

<div class="col-12 col-md-12 mb-5">
    <label>انتخاب محصول</label>

    <select class="js-example-basic-single form-control" name="product_id[]" multiple="multiple">
        <option value="">لطفا انتخاب کنید</option>
        @foreach($products as $product)
            <option value="{{$product->id}}"
                    {{ in_array(trim($product->id) , $pack->products()->pluck('products.id')->toArray()) ? 'selected' : '' }}>
                {{$product->name}}
            </option>
        @endforeach
    </select>
    @if($errors->has('product_id'))
        <span class="help-block sub-error"> {{$errors->first('product_id')}} </span>
    @endif
</div>



<div class="col-12 col-md-6 mb-5">

    <label>عنوان پک*</label>

    <input id="title_lbl" type="text" class="form-control" name="title"
           placeholder="لطفا عنوان را وارد کنید" value="{{$pack->title}}">
    @if($errors->has('title'))
        <span class="help-block sub-error"> {{$errors->first('title')}} </span>
    @endif

</div>

<div class="col-12 col-md-6 mb-5">
    <label>تگ</label>
    {{--<select class="selectMultiTag form-control" name="tag[]" multiple="multiple">--}}
        {{--@foreach($tags as $name)--}}
            {{--<option value="{{ $name }}"--}}
                    {{--{{ in_array(trim($name) , $pack->tagNames()) ? 'selected' : '' }}>--}}
                {{--{{ $name }}--}}
            {{--</option>--}}
        {{--@endforeach--}}
    {{--</select>--}}
</div>

<div class="col-12 col-md-12 mb-5">
    <div class="checkbox">
        <label>
            <input type="checkbox" name="is_show" @if($pack->is_show == 1) checked
                   @endif value="1">نمایش در سایت
        </label>
        @if($errors->has('is_show'))<span
                class="help-block sub-error">{{$errors->first('is_show')}}</span>@endif
    </div>
</div>

<div class="col-12 col-md-3 mb-5">
    <label> عکس اصلی ذخیره شده</label>

    <img src="{{$pack->image}}" class="border-radius5" alt="{{$pack->title}}" width="80px">
</div>
<div class="col-12 col-md-3 mb-5">
    <label>عکس اصلی *</label>
    <label for="image" class="custom-file-upload">
        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
    </label>
    <input id="image" class="file-upload file_upload_ajax" type="file" name="image"/>
    <div id="image_src" class="show"></div>
    <input type="hidden" id="image" class="path" name="image_path" value="">
    @if($errors->has('image'))
        <span class="help-block sub-error"> {{$errors->first('image')}} </span>
    @endif

</div>

<div class="col-12 col-md-12 mb-5">
    <label> عکس های ذخیره شده</label>

    @if($pack->galleries()->get())
        @foreach($pack->galleries()->get() as $index=>$gallery)
            <div class="div_remove_btn mrgL20" id="gallery_{{$gallery->id}}">
                <img src="{{$gallery->image}}" alt="{{$gallery->title}}" class="border-radius5" width="80px">
                <a id="{{$gallery->id}}" title="حذف عکس" class="btn btn-default remove_btn">
                    <i class="fa fa-close" aria-hidden="true"></i></a>
            </div>
        @endforeach
    @endif

</div>

@include('Admin.partials.multipleImages')

<div class="col-12 col-md-12 mb-5">
    <label>توضیحات </label>

    <textarea class="form-control" title="توضیحات" rows="5" cols="5"
              name="description">{{$pack->description}}</textarea>
    @if($errors->has('description'))
        <span class="help-block sub-error"> {{$errors->first('description')}} </span>
    @endif

</div>

