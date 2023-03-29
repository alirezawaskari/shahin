@if(isset($category_center_id) && $category_center_id != 0)
    <input type="hidden" class="category_center_id" value="{{$category_center_id}}">
@endif
<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12 mrgB15">
        <label for="parent_id_lbl">جستجو گروه کالا  <span class="required">*</span> :</label><br/>
        <input class="form-control searchCategory" name="category" placeholder="لطفا بیش از سه حرف تایپ کنید"  type="text">
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 ">
        <p>نتایج جستجو گروه ها</p><hr/>
        <div id="category-suggestion-box" style="display: none;"></div>
    </div>
</div>
@if($errors->has('choose_category_id'))
    <span class="help-block sub-error  ">{{$errors->first('choose_category_id')}}</span>@endif




