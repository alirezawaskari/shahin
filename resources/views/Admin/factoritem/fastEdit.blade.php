@extends('Admin.master')

@section('content')
    @include('Admin.factoritem.layouts.info')
    <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت توضیح جدید</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('factor.comment') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <textarea required class="form-control" name="comment" id="" cols="30" rows="10"
                                  placeholder="توضیح خود را بنویسید..."></textarea>
                        <input type="hidden" value="{{ $factorItem['factor']->id }}" name="factor_id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">ثبت</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                @permission('file-fastEdit')
                <li class="nav-item">
                    <a class="nav-link show active"
                       id="contact-tab" data-toggle="tab" href="#factoritem" role="tab" aria-controls="factoritem"
                       aria-selected="false">ثبت سریع</a>
                </li>
                @endpermission
            </ul>
            <div class="tab-content" id="myTabContent">
                <div
                    class="tab-pane fade p-t-20 active show"
                    id="factoritem" role="tabpanel" aria-labelledby="contact-tab">
                    <div class="col-12 mt-4">
                        <div class="card col-12 mt-5 pt-4 col-l main">
                            <h4>اطلاعات اولیه پرونده</h4>
                            <hr>
                            <form action="{{ route('factorItem.information')}}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="item[id]" value="{{ $item['id'] }}" id="factorItem">
                                <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                                <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                                <input type="hidden" name="mobile_supervisor"
                                       value="{{ $factorItem->supervisor ? $factorItem->supervisor->phone : '' }}">
                                <br/>
                                <input type="hidden" value="{{ $item['id'] }}" name="id">
                                <input type="hidden" value="{{ $factorItem['status_factor'] }}" name="status_factor">
                                <div class="row">
                                    <div class="col-12 col-md-4 mb-2">
                                        <label>تاریخ تشکیل پرونده</label>
                                        <input type="text" id="created_at" name="created_at"
                                               class="form-control date mb-2">
                                        <input type="hidden" id="approval_date" value="{{$factorItem['approval_date']}}">
                                    </div>
                                </div>
                                <input type="submit" class="btn btn-success px-4 mb-3 submit_btn" value="ثبت"
                                       id="btnSave">
                            </form>
                        </div>
                        <div class="card col-12 mt-5 pt-4 col-l main">
                            <h4>فاکتور</h4>
                            <hr>
                            <form action="{{ route('factorItem.createFactor')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="item[id]" value="{{ $item['id'] }}" id="factorItem">
                                <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                                <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                                <br/>
                                <input type="hidden" value="{{ $item['id'] }}" name="id">
                                <input type="hidden" value="{{ $factorItem['status_factor'] }}" name="status_factor">
                                <div class="row">
                                    <div class="col-12 col-md-2 mb-2">
                                        <label>جمع کل(ریال)</label>
                                        <input class="form-control" value="{{ number_format($item['total_price'])}}" name="totalPrice" type="text" oninput="separatePrices(this)">
                                    </div>
                                    <div class="col-12 col-md-4 mb-3">
                                        نوع پرداخت
                                        <br>
                                        <br>
                                        نقد<input type="radio" name="type"
                                                value="0" {{ $factorItem['type'] == 0 ? 'checked' : '' }} {{ $item['confirm'] ? 'onclick="changePaymentType()"' : '' }}>
                                        اقساط<input type="radio" name="type"
                                                value="1" {{ $factorItem['type']  == 1 ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <input type="submit" class="btn btn-success px-4 mb-3 submit_btn" value="ثبت"
                                       id="btnSave" {{ $item['confirm'] ? 'disabled' : '' }}>
                            </form>
                        </div>
                        <div class="card col-12 mt-5 pt-4 col-l main">
                            <h4>مبالغ و محاسبه اقساط</h4>
                            <hr>
                            <form action="{{ route('factorItem.calculate')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" value="{{ $item['id'] }}" name="id">
                                @if($item['total_price'] != 0)
                                    <div class="row">
                                        <div class="col-12 col-md-2 mb-2">
                                            <label>جمع کل(ریال)</label>
                                            <p class="form-control">{{number_format($item['total_price'])}} </p>
                                        </div>
                                        <div class="col-12 col-md-2 mb-2">
                                            <label>مبلغ تخفیف</label>
                                            <input class="form-control" value="{{number_format($item['discount'])}}" name="discount"
                                                   type="text" oninput="separatePrices(this)">
                                        </div>
                                        @if($factorItem['type'] == 1)
                                            <div class="col-12 col-md-1 mb-2">
                                                <label>تعداد اقساط</label>
                                                <input class="form-control" value="{{$item['installments_num']}}"
                                                       name="installments_num" type="number" min="1">
                                            </div>
                                            <div class="col-12 col-md-2 mb-2">
                                                <label>مبلغ اقساط(ریال)</label>
                                                <p class="form-control">{{number_format($item['installments_price'])}}</p>
                                            </div>
                                            <div class="col-12 col-md-2 mb-2">
                                                <label>پیش پرداخت(ریال)</label>
                                                <input class="form-control" value="{{number_format($item['prepayment']['price'])}}"
                                                       name="prepayment" type="text" oninput="separatePrices(this)">
                                            </div>
                                            <div class="col-12 col-md-3 mb-2">
                                                <label>تعداد مراحل پرداخت پیش پرداخت</label>
                                                <input class="form-control"
                                                       value="{{ isset($item['prepayment']['num']) ? $item['prepayment']['num'] : 1}}"
                                                       name="prepayment_num" type="number"
                                                       max="{{ $factorItem['type'] == 0 ? $item['prepayment']['num'] : '10' }}"
                                                       min="1">
                                            </div>
                                            <div class="col-12 col-md-4 mb-3">
                                                <label>نوع ضمانت</label>
                                                <br>
                                                چک <input type="radio" name="guarantee_type"
                                                          value="check" {{ $item['guarantee_type'] == 'check' ? 'checked' : '' }}>
												سفته <input type="radio" name="guarantee_type" value="promissorynote" {{ $item['guarantee_type']  == 'promissorynote' ? 'checked' : '' }}>

                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <input type="submit" class="btn btn-success px-4 mb-3 submit_btn"
                                       value="ثبت" {{ $item['confirm'] ? 'disabled' : ''}}>
                            </form>
                        </div>

                        @include('Admin.factoritem.layouts.file.prepaymentLink')

                        <div class="card col-12 mt-5 pt-4 col-l main">
                            <h4>اقساط</h4>
                            <hr>
                            <form class="form-horizontal" role="form" method="post"
                                  action="{{url('dashboard/save-installments-paid')}}" enctype="multipart/form-data">
                                @csrf
                                @if($item['total_price'] != 0)
                                    <input type="hidden" value="{{ $item['id'] }}" name="id">
                                    <input type="hidden" value="{{ $user['id'] }}" name="user_id">
                                    <input type="hidden" value="prepayment" name="type">
                                @endif
                                <div class="row" id="newpaymenttpl">
                                    <div class="col-12 col-md-2 mb-2">
                                        <label>مبلغ(ریال)</label>
                                        <input class="form-control" name="price[]
                                        " type="text" oninput="separatePrices(this)" required>
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 payment">
                                        <label>نحوه پرداخت</label>
                                        <select class="form-control" name="payment_type[]" id="payment_type3"
                                                onchange="paymentType(3)">
                                            <option value="online">آنلاین</option>
                                            <option value="card_reader">کارتخوان</option>
                                            <option value="card">کارت به کارت</option>
                                            <option value="cash">پول نقد</option>
                                            <option value="credit">حواله</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2" id="payment_date">
                                        <label>تاریخ پرداخت</label>
                                        <input type="text" name="payment_date[]" class="form-control date mb-2">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2 d-none" id="number_card3">
                                        <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                                        <input class="form-control" name="number_card[]" type="number"
                                               id="number_card_input3">
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 d-none" id="payment_code3">
                                        <label>شماره پیگیری (4 رقم آخر)</label>
                                        <input class="form-control" name="payment_code[]" type="number"
                                               id="payment_code_input3">
                                    </div>
                                    <div class="col-12 col-md-3 mb-2 d-none" id="bill_image3">
                                        <label>رسید دریافت وجه</label>
                                        <br>
                                        <label for="bill_image_input3" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input class="form-control file-up" name="bill_image[]" type="file" id="bill_image_input3" accept=".jpg,.jpeg,.png">
                                    </div>
                                </div>
                                <div id="newpayment">
                                    <div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-1 mb-2">
                                    <div onclick="new_payment()" class="btn btn-success mb-2">+</div>
                                </div>

                                <input type="submit" class="btn btn-success px-4 mb-3 submit_btn"
                                       value="ثبت">
                            </form>
                        </div>
                        <div class="card col-12 mt-5 pt-4 col-l main">
                            <h4>آپلود تصاویر</h4>
                            <hr>
                            <form action="{{ route('factorItem.document')}}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
                                <input type="hidden" name="status" value="4">
                                <input type="hidden" name="status_factor"
                                       value="{{ $factorItem['factor']->representation_id && $factorItem['factor']->representation_id != 3 ? 11 : 9}}">
                                <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                                <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                                @if(!is_null($factorItem['centers']) || !is_null($factorItem['forms']))
                                    <div class="row p-3 position-relative">
                                        @if(!is_null($factorItem['forms']))
                                            @foreach($factorItem['forms'] as $form)
                                                @if($form->level == 'first_factor_image')
                                                    <div class="col-12 row">
                                                        <div class="col-md-2">
                                                            <div id="addnew" class="btn badge-success mb-5">
                                                                <a href="javascript:new_link()" class="text-white"><i
                                                                        class="fa fa-plus" aria-hidden="true"></i>
                                                                    افزودن عکس </a>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div id="newlink">
                                                                <div>
                                                                </div>
                                                            </div>
                                                            <div id="newlinktpl" class="d-none">
                                                                <div class="card">
                                                                    <input name="item[{{ $form->name }}][]" type="file"
                                                                           class="filestyle"
                                                                           data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(isset($item['factor_values'][$form->name]))
                                                        @foreach($item['factor_values'][$form->name] as $image)
                                                            <div class="card col-md-3">
                                                                <a href="{{ asset($image) }}" target="_blank">
                                                                    <img src="{{ $image }}" class="card-img-top"
                                                                         height="150px">
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <div class="col-12">
                                                        {!! $form->help !!}
                                                        <p>تذکر : آپلود تمامی تصاویر درصورت وجود الزامی است.</p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="success-overly position-absolute mx-3"></div>
                                    </div>
                                @endif
                                <input type="submit" class="btn btn-success px-4 mb-3 submit_btn" value="ثبت">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade p-t-20" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                    <span>توضیحات همکاران</span>
                    <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModal">ثبت توضیح جدید</span>
                    <div class="mt-4">
                        @if(count($factorItem['user_histories']) > 0)
                        @foreach($factorItem['user_histories'] as $user_history)
                        <div class="bg-light rounded p-3 mb-3">
                            <div class="panel-heading">
                                <span class="d-inline-block">{{ $user_history['user'] }}</span>
                                <span class="text-black-50">{{ $user_history['date'] }}</span>
                            </div>
                            <p class="mt-4">
                                {!! $user_history['description'] !!}
                            </p>
                        </div>
                        @endforeach
                        @else
                        <p>هیچ توضیحی ثبت نشده</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')

<script src="{{ asset('/assets/dashboard/js/factorItem.js') }}"></script>
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>

<script>
document.getElementById('created_at').value = document.getElementById('approval_date').value;
</script>
@endsection
