@if (!is_null($factorItem['centers']))
<div class="col-12 card border shadow-lg">
    <div class="card-body">
        <form action="{{ route('factorItem.turnRatings' , [$factorItem['resnumber']]) }}" method="POST">
            @csrf
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
            <div class="divDate">
                <input type="hidden" name="type" value="supporter">
                <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                <div class="col-12 mb-2">
                    <label for="item[date]">زمان پیشنهادی جهت حضور در مرکز:</label>
                    <input type="text" name="date" class="form-control date" value="{{ $factorItem['date'] }}">
                </div>
                <div class="col-12 mb-3">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="turnRatingsCheck" checked="" name="check">
                        <label class="custom-control-label" for="turnRatingsCheck">زمان مراجعه برای کاربر
                            پیامک شود.</label>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-4">
                <label for="description">توضیحات</label>
                <textarea id="description" name="description" class="form-control">
                            </textarea>
                <p class="text-warning col-12"> <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> همکار
                    لطفا توضیحات مرتبط با نوبت داده شده را وارد کنید.</p>
            </div>

            <div class="col-12 col-md-12 mb-2 mt-auto">
                <button type="submit" class="btn btn-success">ثبت</button>
            </div>
        </form>
    </div>
</div>
@endif
