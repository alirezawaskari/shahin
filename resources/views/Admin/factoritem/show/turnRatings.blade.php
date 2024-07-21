@if (!is_null($factorItem['centers']))
<div class="card border shadow-lg">
    <div class="card-header">تعیین نوبت مشاوره : </div>
    <div class="card-body">
        <form action="{{ route('factorItem.setCenter') }}" method="POST">
            @csrf
            <div class="col-4 mb-3">
                <label>انتخاب نوع دریافت مشاوره :</label>
                <select name="item[typeConsultation]" id="select_type_consultation" class="form-control" required>
                    <option id="type_consultation_online" value="online"
                        {{isset($factorItem['factor_values']['typeConsultation']) &&
                        $factorItem['factor_values']['typeConsultation']=='online' ?'selected' : '' }}>آنلاین
                    </option>
                    <option value="inPerson" {{isset($factorItem['factor_values']['typeConsultation']) &&
                        $factorItem['factor_values']['typeConsultation']=='inPerson' ?'selected' : '' }}>حضوری
                    </option>
                </select>
            </div>
            <hr>
            <div class="divDate">
                <div class="col-4 mb-2">
                    <input type="hidden" name="type" value="supporter">
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                    <label for="item[date]">زمان پیشنهادی جهت حضور در مرکز:</label>
                    <input type="text" name="date" class="form-control date" value="{{ $factorItem['date'] }}">
                </div>
                @if(!auth()->user()->center_id)
                <div class="col-4 mb-2">
                    <label for="factorItem[center]">ارجاع به مرکز:</label>
                    <select name="center_id" class="form-control" required>
                        <option value="">ارجاع مرکز</option>
                        @foreach ($factorItem['centers'] as $center)
                        <option value="{{ $center->id }}" class="font-12" {{ $factorItem['center'] !=null &&
                            $factorItem['center']->id ==
                            $center->id ? 'selected' : '' }}>
                            {{ $center->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                <div class="col-12 mb-4">
                    <label for="description">توضیحات</label>
                    <textarea id="description" name="description" class="form-control">
                            </textarea>
                </div>
                <div class="col-12 mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="turnRatingsCheck" checked=""
                            name="check">
                        <label class="custom-control-label" for="turnRatingsCheck">زمان مراجعه برای کاربر
                            پیامک شود.</label>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-12 mb-2 mt-auto">
                <button type="submit" class="btn btn-success">ثبت</button>
            </div>
            @if(!auth()->user()->center_id)
            <span class="text-warning"><i class="fa fa-warning"></i> همکار محترم توجه داشته باشید درصورت تعیین مرکز و زمان مراجعه پرونده به مرکز ارجاع داده می شود.</span>
            @endif
        </form>
    </div>
</div>
@endif
