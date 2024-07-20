<div class="card">
    <div class="card-header">ارجاع مرکز : </div>
    <div class="card-body">
        <form action="{{ route('factorItem.setCenter') }}" method="POST">
            <div class="row">
                @csrf
                <div class="col-12 mb-2">
                    <label for="factorItem[center]">ارجاع به مرکز:</label>
                    <select name="center_id" class="form-control" required>
                        <option value="">ارجاع مرکز</option>
                        <option value="null">بدون مرکز</option>
                        @foreach ($factorItem['centers'] as $center)
                        <option value="{{ $center->id }}" class="font-12" {{ $factorItem['center'] !=null &&
                            $factorItem['center']->id ==
                            $center->id ? 'selected' : '' }}>
                            {{ $center->title }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                </div>
                <div class="col-12 mb-3">
                    <label for="description">توضیحات</label>
                    <textarea id="description" name="description" class="form-control">
                            </textarea>
                    <p class="text-warning col-12"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> همکار
                        محترم توجه داشته باشید که توضیحات ثبت شده توسط شما به مرکز انتخابی نمایش داده میشود.</p>
                </div>
                <div class="col-12 col-md-12  mt-auto">
                    <button type="submit" class="btn btn-success">ثبت</button>
                </div>
            </div>
        </form>
    </div>
</div>
