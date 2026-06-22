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
    @endphp

    @for($i = 0; $i < 4; $i++)
        @php
            $image = $deskImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

            <input type="text"
                   name="deskImage[{{$i}}][file_title]"
                   value="{{ $image->title ?? '' }}"
                   class="form-control mb-2"
                   placeholder="عنوان">

            <img id="preview_desk_{{$i}}"
                 src="{{ $image ? asset($image->image) : 'https://via.placeholder.com/150x100' }}"
                 class="img-fluid mb-2"
                 style="height:100px;object-fit:cover">

            <input type="file"
                   name="deskImage[{{$i}}][file]"
                   class="form-control"
                   onchange="previewImage(event,'preview_desk_{{$i}}')">

        </div>
    @endfor

    {{-- ================= BIG DESKTOP IMAGE ================= --}}
    @php
        $bigDesk = $deskImages[4] ?? null;
    @endphp

    <div class="col-md-12 card p-3 shadow-sm text-center">

        <input type="text"
               name="deskImage[4][file_title]"
               value="{{ $bigDesk->title ?? '' }}"
               class="form-control mb-2"
               placeholder="عنوان عکس بزرگ">

        <img id="preview_desk_4"
             src="{{ $bigDesk ? asset($bigDesk->image) : 'https://via.placeholder.com/300x200' }}"
             class="img-fluid mb-2 rounded"
             style="height:180px;object-fit:cover">

        <input type="file"
               name="deskImage[4][file]"
               class="form-control"
               onchange="previewImage(event,'preview_desk_4')">

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

    @for($i = 0; $i < 4; $i++)
        @php
            $image = $mobileImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

            <input type="text"
                   name="mobileImage[{{$i}}][file_title]"
                   value="{{ $image->title ?? '' }}"
                   class="form-control mb-2"
                   placeholder="عنوان">

            <img id="preview_mobile_{{$i}}"
                 src="{{ $image ? asset($image->image) : 'https://via.placeholder.com/100x150' }}"
                 class="img-fluid mb-2"
                 style="height:120px;object-fit:cover">

            <input type="file"
                   name="mobileImage[{{$i}}][file]"
                   class="form-control"
                   onchange="previewImage(event,'preview_mobile_{{$i}}')">

        </div>
    @endfor

    {{-- ================= BIG MOBILE IMAGE ================= --}}
    @php
        $bigMobile = $mobileImages[4] ?? null;
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

        <input type="file"
               name="mobileImage[4][file]"
               class="form-control"
               onchange="previewImage(event,'preview_mobile_4')">

    </div>

</div>