@if(count($categories) > 0)
@foreach($categories as $category)
    @if(isset($category['parents']))
        @foreach($category['parents'] as $ind=>$cat)
            @if($loop->last)
                <div class="checkbox">
                    <label>
                        <input name="choose_category_id" class="choose_category_id" type="radio" value="{{$category->id}}">{{$cat->title}}
                    </label>
                </div>
            @endif
        @endforeach
    @endif
    @if(isset($category['parents']))
    <p class="disInline">
        @foreach($category['parents'] as $ind=>$cat)
            @if($ind == count($category['parents']) - 1 ) <span>{{$cat->title}}</span>
            @else <span>{{$cat->title}}</span> <span> > </span> @endif
        @endforeach
    </p><br/>
    @endif
@endforeach
@else
    <p>هیچ نتیجه ای یافت نشد.</p>
@endif
