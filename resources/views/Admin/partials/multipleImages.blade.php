@if(isset($has_link))<input type="hidden" class="multipleImagesHasLink" value="{{$has_link}}">@endif
<div class="col-12 col-md-8 mb-3">
    <label for="file-upload" class="custom-file-upload">
        <i class="fa fa-cloud-upload"></i> بارگذاری تصاویر
    </label>
    <input id="file-upload" class="file-upload" type="file" onchange="multipleImages(this);"/>
    <table class="table table-striped">
        <tbody class="imagesList">
        </tbody>
    </table>
</div>

