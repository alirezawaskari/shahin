{{-- request contract settlement --}}
<form class="form-horizontal" role="form" method="post"
    action="{{ route('totalFactorItem.requestContractSettlement') }}">
    @csrf
    <div class="row modal-body">
        <input type="hidden" name="total_factor_item_id" value="{{ $item['id'] }}">
        <div class="col-12 col-md-8 card-body">
            <textarea required class="form-control" name="description_request_contract_settlement" id="" cols="30"
                rows="10"
                placeholder="توضیح خود را بنویسید...">{{ $totalFactorItem->totalFactorItemContractSettlement ? $totalFactorItem->totalFactorItemContractSettlement->description_request_contract_settlement : '' }}</textarea>
        </div>
        <div class="col-12 col-md-4 card-body">
            {{-- truthiness, not isset(): img_request_contract_settlement is an accessor that
                 returns '' when there is no image, and isset('') is true - which would render
                 <img src=""> and hide the upload widget below. --}}
            @if ($totalFactorItem->totalFactorItemContractSettlement?->img_request_contract_settlement)
            <a href="{{ asset($totalFactorItem->totalFactorItemContractSettlement->img_request_contract_settlement) }}"
                target="_blank">
                <img src="{{ $totalFactorItem->totalFactorItemContractSettlement->img_request_contract_settlement }}"
                    class="card-img-top" height="150px">
            </a>
            @else
            <div id="request_contract_settlement_src" class="show">
                <img class="card-img-top" height="150px">
            </div>
            <div class="card-body">
                <label>تصویر درخواست تسویه حساب کاربر</label>
                <br>
                <label for="request_contract_settlement" id="up-label" class="btn badge-secondary"><i
                        class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                <input name="request_contract_settlement" type="file" id="request_contract_settlement"
                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                    accept=".jpg,.jpeg,.png">
                <input type="hidden" id="request_contract_settlement_path" class="path"
                    name="urlrequest_contract_settlement">
            </div>
            @endif
        </div>
    </div>
    @if(!in_array($totalFactorItem->status_mali , [6,7,8,9]))
    <button type="submit" class="btn btn-success px-4 mb-3 submit_btn">ثبت درخواست</button>
    @else
    <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> قبلا برای این پرونده درخواست تسویه حساب ثبت شده است. </p>
    @endif
</form>
{{-- confirm contract settlement --}}
<form class="form-horizontal" role="form" method="post"
    action="{{ route('totalFactorItemContractSettlement.confirm') }}">
    @csrf
    <input type="hidden" name="total_factor_item_id" value="{{ $item['id'] }}">
    @if($totalFactorItem->status_mali == 7)
    @permission('mali_confirmation_settlement')
    <button type="submit" class="btn btn-success px-4 mb-3 submit_btn">تایید و
        ارجاع
        به
        مدیرعامل</button>
    @endpermission
    @elseif($totalFactorItem->status_mali == 8)
    @permission('superadmin_confirmation_settlement')
    <button type="submit" class="btn btn-success px-4 mb-3 submit_btn">تایید
        نهایی
        مدیرعامل</button>
    @endpermission
    @endif
</form>
@if($totalFactorItem->totalFactorItemContractSettlement)
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="col-12 mb-5">
                <h5> <i class="fa fa-info-circle" aria-hidden="true"></i> جزئیات درخواست تسویه حساب </h5>
                <table class="vuetable table table-bordered table-striped table-responsive-md mt-5">
                    <thead>
                        <tr>
                            <th>ثبت کننده درخواست</th>
                            <th>تائیدکننده مالی</th>
                            <th>تائیدکننده مدیرعامل</th>
                            <th>تحویل دهنده ضمانت</th>
                            <th>تاریخ ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $totalFactorItem->totalFactorItemContractSettlement->applicant ? $totalFactorItem->totalFactorItemContractSettlement->applicant->family : 'ثبت نشده' }}</td>
                            <td>{{ $totalFactorItem->totalFactorItemContractSettlement->mali ? $totalFactorItem->totalFactorItemContractSettlement->mali->family : 'ثبت نشده' }}</td>
                            <td>{{ $totalFactorItem->totalFactorItemContractSettlement->superadmin ? $totalFactorItem->totalFactorItemContractSettlement->superadmin->family : 'ثبت نشده' }}</td>
                            <td>{{ $totalFactorItem->totalFactorItemContractSettlement->deliverer ? $totalFactorItem->totalFactorItemContractSettlement->deliverer->family : 'ثبت نشده' }}</td>
                            <td>{{ verta($totalFactorItem->totalFactorItemContractSettlement->created_at)->format('Y/m/d H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
