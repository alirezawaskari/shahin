<div class="col-md-12 col-sm-12 col-xs-12 mb-3 ">
    <h4>عکس های صفحه اصلی *</h4>
    <span>تمامی عکس های صفحه اصلی الزامی هستند.</span><br/>
    <span>دو عکس افقی و دو عکس عمودی در سایت نمایش داده می شوند.</span><br/>
</div>

<div class="col-12 col-md-12 mb-5">
    <div class="col-md-6">
        <p>عکس های افقی : </p>
        @if($model->galleries()->where('type',1)->count() > 0 )
            @foreach($model->galleries()->where('type',1)->get() as $i=>$img_banner)
                <div class="col-md-12">
                    <input type="hidden" name="horizontalImages[{{$i}}][gallery_id]" value="{{$img_banner->id}}">

                    <input type="text" placeholder="عنوان عکس" name="horizontalImages[{{$i}}][file_title]"
                           value="@if($img_banner){{$img_banner->title}}@endif"
                           class="form-control disInline width30"/>

                    <label for="banner{{$i}}" class="custom-file-upload"><i
                                class="fa fa-cloud-upload"></i></label>

                    <div class="disInline">
                        <input id="banner{{$i}}" class="file-upload file_upload_ajax" type="file"
                               name="banner{{$i}}"/>
                        <div id="banner{{$i}}_src" class="show">

                            <div class="div_remove_btn" id="gallery_{{$img_banner->id}}">
                                <img class="border-radius5" src="{{$img_banner->image}}"
                                     alt="{{$img_banner->title}}"
                                     width="80px">
                                <a id="{{$img_banner->id}}" title="حذف عکس"
                                   class="btn btn-default remove_btn">
                                    <i class="fa fa-close" aria-hidden="true"></i></a>
                            </div>

                        </div>
                        <input type="hidden" id="banner{{$i}}" class="path" name="horizontalImages[{{$i}}][file]"
                               value="">
                    </div>
                </div>

            @endforeach


        @else
            @for($i=0;$i < 2;$i++)
                <div class="col-md-12">
                    <input type="text" placeholder="عنوان عکس" name="horizontalImages[{{$i}}][file_title]"
                           class="form-control disInline width30"/>
                    <label for="banner{{$i}}" class="custom-file-upload mrgZ"><i
                                class="fa fa-cloud-upload"></i>
                    </label>
                    <div class="disInline">
                        <input id="banner{{$i}}" class="file-upload file_upload_ajax" type="file"
                               name="banner{{$i}}"/>
                        <div id="banner{{$i}}_src" class="show"></div>
                        <input type="hidden" id="banner{{$i}}" class="path" name="horizontalImages[{{$i}}][file]"
                               value="">
                        @if($errors->has("horizontalImages.$i.file"))
                            <span class="help-block sub-error"> {{$errors->first("horizontalImages.$i.file")}} </span>
                        @endif
                    </div>
                </div>

            @endfor
        @endif

    </div>

    <div class="col-md-5">
        <p>عکس های عمودی : </p>

        @if($model->galleries()->where('type',0)->count() > 0)
            @foreach($model->galleries()->where('type',0)->get() as $i=>$img_banner)
                <div class="col-md-12">
                    <input type="hidden" name="horizontalImages[{{$img_banner->id}}][gallery_id]"
                           value="{{$img_banner->id}}">
                    <input type="text" placeholder="عنوان عکس"
                           name="horizontalImages[{{$img_banner->id}}][file_title]"
                           value="@if($img_banner){{$img_banner->title}}@endif"
                           class="form-control disInline width30"/>

                    <label for="banner{{$img_banner->id}}" class="custom-file-upload"><i
                                class="fa fa-cloud-upload"></i></label>

                    <div class="disInline">
                        <input id="banner{{$img_banner->id}}" class="file-upload file_upload_ajax" type="file"
                               name="banner{{$img_banner->id}}"/>
                        <div id="banner{{$img_banner->id}}_src" class="show">

                            <div class="div_remove_btn" id="gallery_{{$img_banner->id}}">
                                <img class="border-radius5" src="{{$img_banner->image}}"
                                     alt="{{$img_banner->title}}"
                                     width="80px">
                                <a id="{{$img_banner->id}}" title="حذف عکس"
                                   class="btn btn-default remove_btn">
                                    <i class="fa fa-close" aria-hidden="true"></i></a>
                            </div>

                        </div>
                        <input type="hidden" id="banner{{$img_banner->id}}" class="path"
                               name="horizontalImages[{{$img_banner->id}}][file]" value="">
                    </div>
                </div>
            @endforeach

        @else
            @for($i=0;$i < 2;$i++)
                <div class="col-md-12">
                    <input type="text" placeholder="عنوان عکس" name="verticalImages[{{$i}}][file_title]"
                           class="form-control disInline width30"/>
                    <label for="banner{{$i}}" class="custom-file-upload mrgZ"><i
                                class="fa fa-cloud-upload"></i>
                    </label>
                    <div class="disInline">
                        <input id="banner{{$i}}" class="file-upload file_upload_ajax" type="file"
                               name="banner{{$i}}"/>
                        <div id="banner{{$i}}_src" class="show"></div>
                        <input type="hidden" id="banner{{$i}}" class="path" name="verticalImages[{{$i}}][file]"
                               value="">
                        @if($errors->has("verticalImages.$i.file"))
                            <span class="help-block sub-error"> {{$errors->first("verticalImages.$i.file")}} </span>
                        @endif
                    </div>
                </div>

            @endfor
        @endif
    </div>

</div>



