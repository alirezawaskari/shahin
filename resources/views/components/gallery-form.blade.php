<div class="col-md-12 mb-3">
    <h4>عکس های گالری *</h4>
    <span>تمامی عکس ها الزامی هستند.</span><br/>
    <span>نمایش در موبایل و دسکتاپ</span><br/>
    <span>4 عکس کوچک و 1 عکس بزرگ</span>
</div>

{{-- ================= DESKTOP ================= --}}
<div class="row">

    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
        عکس های دسکتاپ
    </div>

    @php
        $deskImages = $galleries->where('type', 1)->values();

        $numberImage = 4;

        if ($ghorfeTemplet->type == 'service') {
            $numberImage = 6;
        }

        $bigDeskIndex = $numberImage;
    @endphp

    @for($i = 0; $i < $numberImage; $i++)
        @php
            $image = $deskImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

            <input type="text"
                   name="deskImage[{{$i}}][file_title]"
                   value="{{ $image->title ?? '' }}"
                   class="form-control mb-2"
                   placeholder="عنوان">


            <div class="input-group mb-2">
                <input id="desk_{{$i}}" name="desk_file_{{$i}}"
                       class="d-none form-control file-upload file_upload_ajax" type="file"
                       accept="image/*"/>
                <label for="desk_{{$i}}"
                       class="btn btn-primary custom-file-upload border border-1">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
            </div>

            <input type="hidden" id="desk_{{$i}}_path" class="path" name="deskImage[{{$i}}][file]" value="">
            <div id="desk_{{$i}}_src" class="show disInline"></div>

        </div>
    @endfor

    {{-- ================= BIG DESKTOP IMAGE ================= --}}
    @php
        $bigDesk = $deskImages[$numberImage] ?? null;
    @endphp

    <div class="col-md-12 card p-3 shadow-sm text-center">

        <input type="text"
               name="deskImage[4][file_title]"
               value="{{ $bigDesk->title ?? '' }}"
               class="form-control mb-2"
               placeholder="عنوان عکس بزرگ">

        <div class="input-group mb-2">
            <input id="desk_4" name="desk_file_4"
                   class="d-none form-control file-upload file_upload_ajax" type="file"
                   accept="image/*"/>
            <label for="desk_4"
                   class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
            </label>
        </div>

        <input type="hidden" id="desk_4_path" class="path" name="deskImage[4][file]" value="">
        <div id="desk_4_src" class="show disInline"></div>

    </div>

</div>

{{-- ================= MOBILE ================= --}}
<div class="row mt-4">

    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
        عکس های موبایل
    </div>

    @php
        $mobileImages = $galleries->where('type', 0)->values();
    @endphp

    @for($i = 0; $i < $numberImage; $i++)
        @php
            $image = $mobileImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

            <input type="text"
                   name="mobileImage[{{$i}}][file_title]"
                   value="{{ $image->title ?? '' }}"
                   class="form-control mb-2"
                   placeholder="عنوان">

            <div class="input-group mb-2">
                <input id="mobile_{{$i}}" name="mobile_file_{{$i}}"
                       class="d-none form-control file-upload file_upload_ajax" type="file"
                       accept="image/*"/>
                <label for="mobile_{{$i}}"
                       class="btn btn-primary custom-file-upload border border-1">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
            </div>

            <input type="hidden" id="mobile_{{$i}}_path" class="path" name="mobileImage[{{$i}}][file]" value="">
            <div id="mobile_{{$i}}_src" class="show disInline"></div>

        </div>
    @endfor

    @php
        $bigMobile = $mobileImages[$numberImage] ?? null;
    @endphp

    <div class="col-md-12 card p-3 shadow-sm text-center">

        <input type="text"
               name="mobileImage[4][file_title]"
               value="{{ $bigMobile->title ?? '' }}"
               class="form-control mb-2"
               placeholder="عنوان عکس بزرگ">

        <img id="preview_mobile_4"
             src="{{ $bigMobile ? asset($bigMobile->image) : 'https://via.placeholder.com/150x250' }}"
             class="img-fluid mb-2 rounded"
             style="height:180px;object-fit:cover">

        <div class="input-group mb-2">
            <input id="mobile_4" name="mobile_file_4"
                   class="d-none form-control file-upload file_upload_ajax" type="file"
                   accept="image/*"/>
            <label for="mobile_4"
                   class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
            </label>
        </div>

        <input type="hidden" id="mobile_4_path" class="path" name="mobileImage[4][file]" value="">
        <div id="mobile_4_src" class="show disInline"></div>

    </div>

</div>
