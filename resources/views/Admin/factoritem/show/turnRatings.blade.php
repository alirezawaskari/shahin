@if (!is_null($factorItem['centers']))
    <div class="card border shadow-lg">
        <div class="card-header">تعیین نوبت مشاوره : </div>
        <div class="card-body">
            <form action="{{ route('factorItem.turnRatings') }}" method="POST">
                @csrf
                <div class="row">
                    <input type="hidden" name="type" value="supporter">
                    <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                    <div class="col-md-3">
                        <label for="item[date]">زمان پیشنهادی جهت حضور در مرکز:</label>
                        <input type="text" name="date" class="form-control date" value="{{ $factorItem['date'] }}">
                    </div>
                    <div class="col-12 mb-4">
                        <label for="description">توضیحات</label>
                        <textarea id="description" name="description" class="form-control">
                            </textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="turnRatingsCheck" checked="" name="check">
                            <label class="custom-control-label" for="turnRatingsCheck">زمان مراجعه برای کاربر
                                پیامک شود.</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-12  mt-auto">
                        <button type="submit" class="btn btn-success" {{ is_null($factorItem['center'] )? 'disabled': ''}}>ثبت</button>
                        @if(is_null($factorItem['center'] ))
                            <span class="text-warning"><i class="fa fa-warning"></i>جهت امکان ثبت نوبت لطفا ابتدا تعیین مرکز نمایید !</span>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
