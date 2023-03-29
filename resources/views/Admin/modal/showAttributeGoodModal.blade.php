<div class="form-group">

    @if(isset($attributes))
        @foreach($attributes as $index=>$attribute)
            @if(count($attribute) > 0)
                <hr/>
                <h5>{{\App\Models\AttributeGroup::title($index)}}</h5>

                @foreach($attribute as $attr)
                    <input type="hidden" class="attribute_id" value="{{$attr->id}}">
                    @if($attr->attributeItems()->where('status',1)->count() > 0)
                        @if($attr->type == 'select' || $attr->type == 'color')
                            <div class="row">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">{{$attr->title}}
                                    @include('Admin.modal.validation',['field'=>'attribute'.$attr->id,'validationable_id'=>$attr->id,'validationable_type'=>'App\Models\Attribute'])
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <select title="" id="{{$attr->id}}" name="attribute_multi[{{$attr->id}}][]"
                                            class="selectMulti form-control attribute_multi"
                                            multiple="multiple">
                                        @foreach($attr->attributeItems()->where('status',1)->get() as $select)
                                            <option value="{{$select->id}}"
                                                    @if(\App\Models\ProductAttribute::hasProductAttribute($select->id,$product_id) == true) selected @endif>{{$select->title}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br/>
                        @endif
                    @endif

                    @if($attr->attributeItems()->where('status',1)->count() == 0)
                        @if($attr->type == 'text')
                            <div class="row">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">{{$attr->title}}
                                    @include('Admin.modal.validation',['field'=>'text','validationable_id'=>$attr->id,'validationable_type'=>'App\Models\Attribute'])
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <input id="name_lbl" type="{{$attr->type}}" class="form-control"
                                           name="attr_create[{{$attr->id}}]"
                                           placeholder="لطفا {{$attr->title}} را وارد کنید"
                                           value="{{\App\Models\ProductAttribute::showProductAttribute($attr->id,$product_id)}}">
                                    @if($errors->has('name'))
                                        <span class="help-block sub-error"> {{$errors->first('name')}} </span>
                                    @endif
                                </div>
                            </div>
                            <br/>
                        @endif
                    @endif

                    @if($attr->type == 'switch')
                        <div class="row">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">{{$attr->title}}
                                @include('Admin.modal.validation',['field'=>'switch','validationable_id'=>$attr->id,'validationable_type'=>'App\Models\Attribute'])
                            </label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <div class="form-group">{{\App\Models\ProductAttribute::hasProductAttribute($attr->id,$product_id)}}
                                    <label><input type="checkbox" class="js-switch" name="attr_create[{{$attr->id}}]" value="1"
                                                  @if(\App\Models\ProductAttribute::showProductAttribute($attr->id,$product_id) == 1) checked @endif>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <br/>
                    @endif

                @endforeach

                <br/>
                <br/>
            @endif
        @endforeach
    @endif


</div>
