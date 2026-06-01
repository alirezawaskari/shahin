<div class="col-md-12 mb-3">
    <h4>عکس های گالری *</h4>
    <span>تمامی عکس ها الزامی هستند.</span><br/>
    <span> نمایش عکس های گالری در صفحه موبایل و دسکتاپ</span><br/>
    <span>4 عکس کوچک و یک عکس  بزرگ </span>
</div>

<div class="row mb-5">
    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">عکس های دسک تاپ</div>

        @for($i=0;$i<4;$i++)
            @php
                $image = optional($ghorfeOnlineList->galleries()->where('type',1)->skip($i)->first());
            @endphp

            <div class="card col-md-6 mb-3 p-2 shadow-sm text-center">

                <input type="text"
                       name="horizontalImages[{{$i}}][file_title]"
                       value="{{ $image->title }}"
                       class="form-control mb-2"
                       placeholder="عنوان عکس"/>

                <img id="preview_h_{{$i}}"
                     src="{{ $image->image ?? 'https://via.placeholder.com/150x100?text=Preview' }}"
                     class="img-fluid mb-2 rounded"
                     style="height:100px;object-fit:cover">

                <input type="file"
                       name="horizontal_file_{{$i}}"
                       class="d-none"
                       id="file_h_{{$i}}"
                       onchange="previewImage(event,'preview_h_{{$i}}')">

                <input type="hidden"
                       name="horizontalImages[{{$i}}][file]"
                       id="hidden_h_{{$i}}">

                <button type="button"
                        class="btn btn-outline-primary btn-sm mb-2"
                        onclick="document.getElementById('file_h_{{$i}}').click()">
                    انتخاب تصویر
                </button>

                @if($image->id)
                    <button type="button"
                            class="btn btn-outline-danger btn-sm"
                            onclick="removeImage('preview_h_{{$i}}')">
                        حذف
                    </button>
                @endif

            </div>
        @endfor

    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 "> عکس های موبایل</div>

        @for($i=0;$i<4;$i++)
            @php
                $image = optional($ghorfeOnlineList->galleries()->where('type',0)->skip($i)->first());
            @endphp

            <div class="card col-md-6 mb-3 p-2 shadow-sm text-center">

                <input type="text"
                       name="verticalImages[{{$i}}][file_title]"
                       value="{{ $image->title }}"
                       class="form-control mb-2"
                       placeholder="عنوان عکس"/>

                <img id="preview_v_{{$i}}"
                     src="{{ $image->image ?? 'https://via.placeholder.com/100x150?text=Preview' }}"
                     class="img-fluid mb-2 rounded"
                     style="height:120px;object-fit:cover">

                <input type="file"
                       name="vertical_file_{{$i}}"
                       class="d-none"
                       id="file_v_{{$i}}"
                       onchange="previewImage(event,'preview_v_{{$i}}')">

                <input type="hidden"
                       name="verticalImages[{{$i}}][file]"
                       id="hidden_v_{{$i}}">

                <button type="button"
                        class="btn btn-outline-primary btn-sm mb-2"
                        onclick="document.getElementById('file_v_{{$i}}').click()">
                    انتخاب تصویر
                </button>

                @if($image->id)
                    <button type="button"
                            class="btn btn-outline-danger btn-sm"
                            onclick="removeImage('preview_v_{{$i}}')">
                        حذف
                    </button>
                @endif

            </div>
        @endfor
</div>
