@if (!is_null($factorItem['centers']))
<div class="col-12 card border shadow-lg">
    <div class="card-body">
        <form action="{{ route('factorItem.setCenter') }}" method="POST">
            @csrf

            @if(!auth()->user()->center_id)
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
            @else
            <div class="col-12 mb-3">
                <label>انتخاب نوع دریافت مشاوره :</label>
                <select name="item[typeConsultation]" id="select_type_consultation" class="selectTypeConsultation form-control" required>
                    <option value="inPerson" {{isset($factorItem['factor_values']['typeConsultation']) &&
                        $factorItem['factor_values']['typeConsultation']=='inPerson' ?'selected' : '' }}>حضوری
                    </option>
                    <option id="type_consultation_online" value="online" class="type_consultation_online"
                        {{isset($factorItem['factor_values']['typeConsultation']) &&
                        $factorItem['factor_values']['typeConsultation']=='online' ?'selected' : '' }}>آنلاین
                    </option>
                </select>
            </div>
            <hr>
            <input type="hidden" name="center_id" value="{{auth()->user()->center_id}}">
            <div class="divDate">
                <input type="hidden" name="type" value="supporter">
                <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                <div class="col-12 mb-2">
                    <label for="item[date]">زمان پیشنهادی جهت حضور در مرکز:</label>
                    <date-picker value="{{ $factorItem['date'] }}" format="YYYY/M/D HH:mm:ss"
                        name="date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="date">
                    </date-picker>
                </div>
                <div class="col-12 mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="turnRatingsCheck" checked="" name="check">
                        <label class="custom-control-label" for="turnRatingsCheck">زمان مراجعه برای کاربر
                            پیامک شود.</label>
                    </div>
                </div>
            </div>
            @endif

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
