<!-- packModal -->
<div id="packModal" class="modal fade bd-example-modal-lg" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">ایجاد پک</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="col-md-12 col-xs-12">
                    <h4 class="text-center">اطلاعات پک </h4>
                    <div class="col-12 col-md-6 mb-3 disInline">
                        <label>عنوان *</label>
                        <input id="title_lbl" type="text" class="form-control" name="title"
                               placeholder="لطفا عنوان را وارد کنید" value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3 disInline">
                        <label></label>
                        <div class="checkbox">
                            <label>
                                <input class="is_show" type="checkbox" name="is_show" @if(old('is_show') == 1) checked @endif value="1">نمایش در سایت
                            </label>
                            @if($errors->has('is_show'))<span
                                    class="help-block sub-error">{{$errors->first('is_show')}}</span>@endif
                        </div>
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <label>عکس اصلی *</label>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file" name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                            <span class="help-block sub-error"> {{$errors->first('image')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-3 tagBox">
                        <label>تگ</label>
                        <select class="selectMultiTag form-control" name="tag[]" multiple="multiple">
                            @foreach($tags as $tag)
                                <option value="{{$tag->name}}">{{$tag->name}}</option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <label>توضیحات </label>
                        <textarea class="form-control" title="توضیحات" rows="5" cols="5"
                                  id="description" name="description">{{old('description')}}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block sub-error"> {{$errors->first('description')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                        <label>نقد و بررسی </label>
                        <textarea id="editor1" class="form-control" name="review"
                                  title="نقد و بررسی ">{{old('review')}}</textarea>
                        @if($errors->has('review'))
                            <span class="help-block sub-error"> {{$errors->first('review')}} </span>
                        @endif
                    </div>

                    <div>
                        @include('Admin.partials.multipleImages',['has_link'=>0])
                    </div>
                </div>


                <br/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-md btn-success" data-dismiss="modal">ثبت</button>
            </div>
        </div>

    </div>
</div>
