<div class="col-md-12 mb-3">
    <h4>عکس های گالری *</h4>
    <span>تمامی عکس ها الزامی هستند.</span><br/>
    <span> نمایش عکس های گالری در صفحه موبایل و دسکتاپ</span><br/>
    <span>4 عکس کوچک و یک عکس  بزرگ </span>
</div>

<div class="row">
    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">عکس های دسک تاپ</div>
    @for($i=0;$i<4;$i++)
        @php
            $image = $galleries->where('type',1)->values()->get($i);
        @endphp
        <div class="card col-md-6 p-2 shadow-sm text-center">
            <input type="text"
                   name="deskImage[{{$i}}][file_title]"
                   value="{{ $image?->title }}"
                   class="form-control mb-2"
                   placeholder="عنوان عکس"/>

            <img id="preview_h_{{$i}}"
                 src="{{ $image?->image ?? 'https://via.placeholder.com/150x100?text=Preview' }}"
                 class="img-fluid mb-2 rounded"
                 style="height:100px;object-fit:cover">

            <input type="file"
                   name="deskImage[{{$i}}][file]"
                   class="d-none"
                   id="file_h_{{$i}}"
                   onchange="previewImage(event,'preview_h_{{$i}}')">

            <input type="hidden"
                   name="deskImage[{{$i}}][file]"
                   id="hidden_h_{{$i}}">

            <button type="button"
                    class="btn btn-outline-primary btn-sm mb-2"
                    onclick="document.getElementById('file_h_{{$i}}').click()">
                انتخاب تصویر
                <strong> 176*300</strong>
            </button>

            @if($image?->id)
                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        onclick="removeImage('preview_h_{{$i}}')">
                    حذف
                </button>
            @endif

        </div>
    @endfor
    <div class="col-md-12 card p-2 shadow-sm text-center">

        <input type="text"
               name="deskImage[{{$i}}][file_title]"
               value="{{ $image?->title }}"
               class="form-control mb-2"
               placeholder="عنوان عکس"/>

        <img id="preview_h_{{$i}}"
             src="{{ $image?->image ?? 'https://via.placeholder.com/150x100?text=Preview' }}"
             class="img-fluid mb-2 rounded"
             style="height:100px;object-fit:cover">

        <input type="file"
               name="deskImage[{{$i}}][file]"
               class="d-none"
               id="file_h_{{$i}}"
               onchange="previewImage(event,'preview_h_{{$i}}')">

        <input type="hidden"
               name="deskImage[{{$i}}][file]"
               id="hidden_h_{{$i}}">

        <button type="button"
                class="btn btn-outline-primary btn-sm mb-2"
                onclick="document.getElementById('file_h_{{$i}}').click()">
            انتخاب تصویر

            <strong>375 * 900 </strong>

        </button>

        @if($image?->id)
            <button type="button"
                    class="btn btn-outline-danger btn-sm"
                    onclick="removeImage('preview_h_{{$i}}')">
                حذف
            </button>
        @endif

    </div>
</div>

<div class="row">
    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 "> عکس های موبایل</div>

    @for($i=0;$i<4;$i++)
        @php
            $image = $galleries->where('type',0)->values()->get($i);
        @endphp
        <div class="card col-md-6 p-2 shadow-sm text-center">

            <input type="text"
                   name="mobileImage[{{$i}}][file_title]"
                   value="{{ $image?->title }}"
                   class="form-control mb-2"
                   placeholder="عنوان عکس"/>

            <img id="preview_v_{{$i}}"
                 src="{{ $image?->image ?? 'https://via.placeholder.com/100x150?text=Preview' }}"
                 class="img-fluid mb-2 rounded"
                 style="height:120px;object-fit:cover">

            <input type="file"
                   name="mobileImage[{{$i}}][file_title]"
                   class="d-none"
                   id="file_v_{{$i}}"
                   onchange="previewImage(event,'preview_v_{{$i}}')">

            <input type="hidden"
                   name="mobileImage[{{$i}}][file]"
                   id="hidden_v_{{$i}}">

            <button type="button"
                    class="btn btn-outline-primary btn-sm mb-2"
                    onclick="document.getElementById('file_v_{{$i}}').click()">
                انتخاب تصویر
                <strong>190 * 115 </strong>
            </button>

            @if($image?->id)
                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        onclick="removeImage('preview_v_{{$i}}')">
                    حذف
                </button>
            @endif

        </div>
    @endfor

    <div class=" col-md-12 card p-2 m-1 shadow-sm text-center">

        <input type="text"
               name="mobileImage[{{$i}}][file]"
               value="{{ $image?->title }}"
               class="form-control mb-2"
               placeholder="عنوان عکس"/>

        <img id="preview_v_{{$i}}"
             src="{{ $image?->image ?? 'https://via.placeholder.com/100x150?text=Preview' }}"
             class="img-fluid mb-2 rounded"
             style="height:120px;object-fit:cover">

        <input type="file"
               name="mobileImage[{{$i}}][file]"
               class="d-none"
               id="file_v_{{$i}}"
               onchange="previewImage(event,'preview_v_{{$i}}')">

        <input type="hidden"
               name="mobileImage[{{$i}}][file]"
               id="hidden_v_{{$i}}">

        <button type="button"
                class="btn btn-outline-primary btn-sm mb-2"
                onclick="document.getElementById('file_v_{{$i}}').click()">
            انتخاب تصویر
            <strong>175 * 420</strong>
        </button>

        @if($image?->id)
            <button type="button"
                    class="btn btn-outline-danger btn-sm"
                    onclick="removeImage('preview_v_{{$i}}')">
                حذف
            </button>
        @endif

    </div>

</div>
