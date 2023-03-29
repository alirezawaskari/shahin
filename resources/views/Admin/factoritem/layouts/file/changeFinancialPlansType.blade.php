<div class="modal fade" id="changeFinancialPlansTypeModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">تغییر طرح پرونده</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('factorItem.changeFinancialPlansType' , [ $factorItem['resnumber']]) }}"
                method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="form-row">
                        <div class="col-12 mb-5">
                            <select name="financial_plans_type" class="form-control" id="financial_plans_type">
                                @foreach ($factorItem['financial_plans_types_list'] as $financial_plans_type)
                                @if ($financial_plans_type::getPaymentType($financial_plans_type->payment_type) ==
                                $factorItem['type'])
                                <option value="{{ $financial_plans_type->id }}">{{ $financial_plans_type->title.' -
                                    '.number_format($financial_plans_type->price).' ریال '}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-12 mb-3">
                            <label> نوع پرداخت</label>
                            <br>
                            نقد <input type="radio" name="type" value="0" {{ $factorItem['type']=='0' ? 'checked' : ''
                                }}>
                            اقساط<input type="radio" name="type" value="1" {{ $factorItem['type']=='1' ? 'checked' : ''
                                }}>
                        </div>
                        <div class="col-12 p-4 alert-warning">
                            <p>ملاحظات : </p>
                            <p>1 - درصورت تغییر نوع پرداخت پرونده باید طرح مربوطه را هم انتخاب کنید.</p>
                            <p>2 - با تغییر طرح پرونده سرپرست طرح پرونده زیر مجموعه ها هم تغییر میکند.</p>
                            <p>3 - درصورتی که هزینه ثبت نام طرح قبلی با طرح جدید مغایرت داشته باشد کاربر موظف به پرداخت اختلاف هزینه می باشد.</p>
                        </div>
                    </div>
                    <input id="factor_item" type="hidden" value="{{ $factorItem['id'] }}" name="factor_item">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>
