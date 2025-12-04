@if (!is_null($factorItem['centers']))
<div class="col-12 card border shadow-lg">
    <div class="card-body">
        <form action="{{ route('factorItem.setCenter') }}" method="POST">
            @csrf
            <div class="col-12 mb-2">
                <label for="factorItem[center]">ارجاع به مرکز:</label>
                <select name="center_id" class="form-control" required>
                    <option value="null">بدون مرکز</option>
                    @foreach ($factorItem['centers'] as $center)
                    <option value="{{ $center->id }}" class="font-12" {{ $factorItem['center'] !=null &&
                        $factorItem['center']->id ==
                        $center->id ? 'selected' : '' }}>
                        {{ $center->title }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
            <div class="col-12 mb-4">
                <label for="description">توضیحات</label>
                <textarea id="description" name="description" class="form-control">
                            </textarea>
                @if(auth()->user()->center)
                <p class="text-warning col-12"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> همکار
                    لطفا توضیحات مرتبط با نوبت داده شده را وارد کنید.</p>
                @else
                <p class="text-warning col-12"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> همکار
                    محترم توجه داشته باشید که توضیحات ثبت شده توسط شما به مرکز انتخابی نمایش داده میشود.</p>

                @endif
            </div>

            <div class="col-12 col-md-12 mb-2 mt-auto">
                <button type="submit" class="btn btn-success">ثبت</button>
            </div>
        </form>
    </div>
</div>
@endif
