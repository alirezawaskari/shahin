<a href="" class="floatLeft" data-toggle="modal" data-target="#brandRequestModal">  درخواست برند جدید</a>
<label for="brand_id_lbl">برند *</label>
<select title="brand_id" class="form-control brands selectSearch" name="good[brand_id]">
    <option value="" data-title=""></option>
    @foreach($brands as $brand)
        <option value="{{$brand->id}}" data-title="">@if($brand->title_en != null)  {{$brand->title_en}} | @endif {{$brand->title}} </option>
    @endforeach

</select>

@include('Admin.site.brand.brandRequest',['category_id'=>$category_id,'request_by'=>1])