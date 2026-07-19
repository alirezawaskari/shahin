<div class="card border">
    <div class="card-body">
        <div class="card-header row col-12 col-md-12 mb-3">
            <div class="col-md-11">
                <h4>فاکتور </h4>
            </div>
            <div class="col-md-1">
                <span class="badge badge-{{$status_class}}">{{$status}}</span>
            </div>
        </div>
        <div class="row col-12 mt-5">
            <form class="form-horizontal w-100" role="form" method="post"
                  action="{{route('purchaseInvoice.update' , [$purchaseInvoice->id ])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')

                <div class="row mb-3">
                    @if($purchaseInvoice->postage)
                        <div class="col-12 col-md-2 mb-3">
                            <label> هزینه ارسال کالا(ریال)</label>
                            <input class="form-control" type="text"
                                   value="{{ number_format($purchaseInvoice->postage) }}">
                        </div>
                    @endif
                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی درخواست کننده</label>
                        <select class="form-control" name="representation_id">
                            <option value="">نمایندگی کل</option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}" {{ $purchaseInvoice->representation_id ==
                                $representation->id ? 'selected' : '' }}>{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @permission('purchase-invoice')
                    <input name="supplier_id" type="hidden" value="{{ auth()->user()->supplier->id }}">
                    @endpermission
                    @permission('purchase-invoice-detail')
                    <div class="col-12 col-md-3 mb-3">
                        <label>تامین کننده</label>
                        <select class="form-control" name="supplier_id">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $purchaseInvoice->supplier_id == $supplier->id ?
                                'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endpermission
                    <div class="col-12 col-md-3 mb-3">
                        <label>جمع کل فاکتور با اعمال تخفیف(ریال)</label>
                        <input class="form-control" name="total_price" type="text"
                               value="{{ number_format($purchaseInvoice->total_price) }}"
                               oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>مبلغ تخفیف(ریال)</label>
                        <input class="form-control" name="discount" type="text"
                               value="{{ number_format($purchaseInvoice->discount) }}" oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 col-md-3 mb-3" id="installments_num">
                        <label>تعداد مراحل پرداخت هزینه فاکتور</label>
                        <input class="form-control" name="installments_num" type="number" min="1"
                               value="{{ $purchaseInvoice->installments_num }}">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>مالیات(درصد)</label>
                        <input class="form-control" name="taxation" type="number"
                               value="{{ $purchaseInvoice->taxation }}" min="0">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 col-md-3 mb-3">
                        <label>پرداخت هزینه ارسال کالا توسط :</label>
                        <br>
                        تامین کننده <input type="radio" name="pay_postage" value="1" {{ $purchaseInvoice->pay_postage ==
                        '1' ? 'checked' : '' }}>
                        درخواست کننده<input type="radio" name="pay_postage" value="0" {{ $purchaseInvoice->pay_postage
                        == '0' ? 'checked' : '' }}>
                    </div>
                    <div class="col-12 col-md-4 mb-3" id="pay_time">
                        <label>اولین پرداختی از هزینه فاکتور : </label>
                        <br>
                        بعد از دریافت کالا <input type="radio" name="pay_time" value="1" {{ $purchaseInvoice->pay_time
                        == '1' ? 'checked' : '' }}>
                        واریز قبل از دریافت کالا <input type="radio" name="pay_time" value="0" {{
                            $purchaseInvoice->pay_time == '0' ? 'checked' : '' }}>
                    </div>
                </div>
                @include('Admin.purchaseinvoice.installments')

                <div class="row position-relative mb-4">
                    <div class="col-12 row">
                        <div class="col-12 mb-3">
                            <label>متن درخواست</label>
                            <textarea type="text" class="form-control"
                                      name="list">{{ $purchaseInvoice->list }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 row">
                        <div class="col-md-4">
                            <div id="addnew" class="btn btn-success mb-5">
                                <label for="factor_image" id="up-label"><i class="fa fa-upload ml-2"
                                                                           aria-hidden="true"></i> انتخاب تصویر فاکتور
                                </label>
                                <input name="factor_image[]" type="file" id="factor_image"
                                       class="filestyle file-upload file_upload_ajax file-up"
                                       data-buttonname="btn-white"
                                       accept=".jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div id="factor_image_newlink" class="col-12 row">
                            <div>
                            </div>
                        </div>
                        <div id="factor_image_newlinktpl" class="d-none">
                            <div id="factor_image_src" class="show"></div>
                            <input type="hidden" id="factor_image" class="path" name="url_factor_image[]">
                        </div>
                    </div>

                    @if(isset($purchaseInvoice->factor_image))
                        @foreach($purchaseInvoice->factor_image as $image)
                            <div class="col-md-3 mb-2">
                                <a href="{{ asset($image) }}" target="_blank">
                                    <img src="{{ $image }}" class="card-img-top" height="150px">
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
                @if($purchaseInvoice->status == 0 || $purchaseInvoice->status == 1 || $purchaseInvoice->status == 2)
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        <a href="{{route('purchaseInvoice.reject' , [$purchaseInvoice->id ])}}"
                           class="btn btn-danger">رد درخواست</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
