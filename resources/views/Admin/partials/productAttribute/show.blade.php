@foreach($attributes as $attribute)
    @foreach($attribute as $attr)
        <div class="col-md-12">
            <input type="hidden" class="attribute_id" value="{{$attr->id}}">
            <h5 class="disInline">{{$attr->title}} : </h5>
            <a href="#" data-toggle="modal" class="floatLeft addRequestAttributeItem" data-target="#attributeItemRequest{{$attr->id}}">
                 درخواست {{$attr->title}} جدید
            </a>
            @include('Admin.modal.attributeItemRequest',['attribute'=>$attr])
        </div>
        <div class="col-md-12 attributeItemBox">
            @foreach($attr->attributeItems->where('status',1) as $attr_item)
                <div class="col-md-2 padB10">
                    @if(\App\Models\AttributeItem::findPaletteByItem($attr_item->id))
                    <div class="disInline paletteByItem"
                         style="background-color:{{\App\Models\AttributeItem::findPaletteByItem($attr_item->id)}}"></div>
                    @endif
                    <div class="disInline">
                        <a id="{{$attr_item->id}}"
                           class="attributeIsPrice curpon colorGray">{{$attr_item->title}}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endforeach