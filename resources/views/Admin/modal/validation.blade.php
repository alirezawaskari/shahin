<a class="curpon  colorOrange insertValidationBtn font11" data-toggle="modal"
   id="{{$field}}" data-target="#insertValidation{{$field}}">درج خطا</a>

<input class="hidden product_id" title="product_id" name="product_id" id="{{$field}}" value="{{$product->id}}">
@if(isset($validationable_id))
    <input class="hidden validationable_id" title="validationable_id" name="validationable_id" id="{{$field}}" value="{{$validationable_id}}">
@endif
@if(isset($validationable_type))
    <input class="hidden validationable_type" title="validationable_type" name="validationable_type" id="{{$field}}" value="{{$validationable_type}}">
@endif

@if(isset($validationable_id) && isset($validationable_type))
    @include('Admin.modal.insertValidation',['product_id'=>$product->id,'field'=>$field,
    'validationable_id'=>$validationable_id,'validationable_type'=>$validationable_type])
@else
    @include('Admin.modal.insertValidation',['product_id'=>$product->id,'field'=>$field])
@endif
