<div class="col-md-12 col-sm-12 col-xs-12 mb-3 ">
    <h4>عکس های صفحه اصلی *</h4>
    <span>تمامی عکس های صفحه اصلی الزامی هستند.</span><br/>
    <span>دو عکس افقی و دو عکس عمودی در کامپیوتر نمایش داده می شوند.</span><br/>
    <span>چهار عکس افقی در موبایل نمایش داده می شوند.</span><br/>
</div>
<div class="col-12 col-md-12 mb-5">
<div class="col-md-6">
    <p>عکس های افقی : </p>
    @for($i=4;$i < 8;$i++)
        <div class="col-md-12">
            <input type="text" name="horizontalImages[{{$i}}][file_title]" class="form-control disInline width30"
                   placeholder="عنوان عکس"/>
            <label for="file-upload{{$i}}" class="custom-file-upload mrgZ">
                {{--<i class="fa fa-cloud-upload"></i>--}}
            </label>
            <div class="disInline">
                <input id="file-upload{{$i}}" class="file-upload file_upload_ajax" type="file" name="banner{{$i}}"/>
                <div id="banner{{$i}}_src" class="show"></div>
                <input type="hidden" id="banner{{$i}}" class="path" name="horizontalImages[{{$i}}][file]" value="">
                @if($errors->has("horizontalImages.$i.file"))
                    <span class="help-block sub-error"> {{$errors->first("horizontalImages.$i.file")}} </span>
                @endif
            </div>
        </div>
    @endfor
</div>
<div class="col-md-6">
    <p>عکس های عمودی : </p>
    @for($i=0;$i < 2;$i++)
        <div class="col-md-12">
            <input type="text" name="verticalImages[{{$i}}][file_title]" class="form-control disInline width30"
                   placeholder="عنوان عکس"/>
            <label for="file-upload{{$i}}" class="custom-file-upload mrgZ">
                {{--<i class="fa fa-cloud-upload"></i>--}}
            </label>
            <div class="disInline">
                <input id="file-upload{{$i}}" class="file-upload file_upload_ajax" type="file" name="banner{{$i}}"/>
                <div id="banner{{$i}}_src" class="show"></div>
                <input type="hidden" id="banner{{$i}}" class="path" name="verticalImages[{{$i}}][file]" value="">
                @if($errors->has("verticalImages.$i.file"))
                    <span class="help-block sub-error"> {{$errors->first("verticalImages.$i.file")}} </span>
                @endif
            </div>
        </div>
    @endfor
</div>
</div>




