@if(isset($has_link))<input type="hidden" class="multipleImagesHasLink" value="{{$has_link}}">@endif
@php $__fu = isset($listKey) ? $listKey.'_fileinput' : 'file-upload'; @endphp
<div class="col-12 col-md-8 mb-3">
    <label for="{{ $__fu }}" class="btn btn-primary custom-file-upload border border-1">
        <i class="fa fa-cloud-upload"></i> بارگذاری تصاویر
    </label>
    <input id="{{ $__fu }}" class="d-none file-upload" type="file" multiple accept="image/*" onchange="multipleImages(this);"/>
    <table class="table table-striped">
        <tbody class="imagesList" @isset($listKey) id="{{ $listKey }}_temp" @endisset>
        </tbody>
    </table>
</div>

