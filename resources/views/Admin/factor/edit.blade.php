@extends('Admin.master')

@section('content')
<div class="page-header">
    <a href="{{ url()->previous() }}" class="btn btn-primary">بازگشت</a>
</div>
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
                    <input type="hidden" value="{{ $factor->id }}" name="factor_id">
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
            <li class="nav-item">
                <a class="nav-link show active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                    aria-controls="home" aria-selected="false">اطلاعات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                    aria-controls="profile" aria-selected="true">وضعیت ها</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                    aria-controls="contact" aria-selected="false">توضیحات</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">عنوان</th>
                                <th scope="col">مقدار</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">نام</th>
                                <td>{{$user->name}} {{ $user->family }}</td>
                            </tr>
                            <tr>
                                <th scope="row">موبایل</th>
                                <td>{{$user->phone }}</td>
                            </tr>
                            <tr>
                                <th scope="row">کد ملی</th>
                                <td>{{$user->melicode }}</td>
                            </tr>
                            <tr>
                                <th scope="row">شماره شناسنامه</th>
                                <td>{{$user->shenasnamecode }}</td>
                            </tr>
                            <tr>
                                <th scope="row">کد کاربری</th>
                                <td>{{$user->token }}</td>
                            </tr>
                            <tr>
                                <th scope="row">استان و شهر</th>
                                <td>{{ $user->province_id != null ? \App\Models\Province::find($user->province_id)->name
                                    : '' }} - {{ $user->city_id != null ? \App\Models\City::find($user->city_id)->name :
                                    '' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">شناسه فاکتور</th>
                                <td>{{ !is_null($factor->resnumber) ? $factor->resnumber : 'ندارد' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">کالسنتر</th>
                                <td>{{ !is_null($factor->callcenter_id) ? \App\Models\User::where('id' ,
                                    $factor->callcenter_id)->withTrashed()->first()->family : 'ندارد' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">نوع پرداخت</th>
                                <td> {{ $factor::TYPE[$factor->type] }} (زمان پرداخت فاکتور : {{
                                    verta($factor->updated_at)->format('%d %B ، %Y - H:i') }})
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">کد پیگیری بانک</th>
                                <td>{{ !is_null($factor->refnumber) ? $factor->refnumber : 'ندارد' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">شماره کارت</th>
                                <td>{{ !is_null($factor->number_card) ? $factor->number_card : 'ندارد' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">شماره پیگیری مرجع</th>
                                <td>{{ !is_null($factor->payment_code) ? $factor->payment_code : 'ندارد' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">رسید دریافت وجه</th>
                                <td>
                                    @if($factor->bill_image)
                                    <a href="{{ $factor->bill_image }}" class="btn btn-primary"
                                        target="_blank">مشاهده</a>
                                    @if($factor->mediaIsLegacy('bill_image'))
                                    <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                    @endif
                                    @else
                                    ندارد
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">مبلغ فاکتور</th>
                                <td>{{ number_format($factor->price) }} ریال</td>
                            </tr>
                            <tr>
                                <th scope="row">کد تخفیف اعمال شده</th>
                                <td>{{ !is_null($factor->coupon_id) ? $factor->coupon->title .' - ' .$factor->coupon->discount .
                                    ' % ' : 'ثبت نشده'}}</td>
                            </tr>
                            <tr>
                                <th scope="row">مالیات بر ارزش افزوده</th>
                                <td>{{ number_format($factor->tax) }} ریال</td>
                            </tr>
                            <tr>
                                <th scope="row">زمان ثبت</th>
                                <td>زمان ثبت: {{ verta($factor->created_at)->format('%d %B ، %Y - H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">تایید مالی</th>
                                <td>{{ $factor->confirm == false ? 'تایید نشده' : 'تایید شده' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">تایید نهایی</th>
                                <td>{{ $factor->verify == false ? 'تایید نشده' : 'تایید شده' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <form class="form-horizontal" role="form" method="post"
                    action="{{route('factor.update' , [ $factor->resnumber])}}">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        @include('Admin.layouts.errors')
                        @role('superadmin|mali')
                        @permission('factor-verify')
                        <div class="col-12 col-md-4 mb-3">
                            <label>تاییدیه مالی</label>
                            <select class="form-control" name="confirm">
                                <option value="0" {{ $factor->confirm == '0' ? 'selected' : '' }}>
                                    تایید نشده
                                </option>
                                <option value="1" {{ $factor->confirm == '1' ? 'selected' : '' }}>
                                    تایید شده
                                </option>
                            </select>
                        </div>
                        @endpermission
                        <div class="col-12 col-md-4 mb-3">
                            <label>مبلغ کل (ریال)</label>
                            <input type="text" value="{{ number_format($factor->price) }}" class="form-control"
                                name="price" required oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>وضعیت پرداخت</label>
                            <select class="form-control" name="status">
                                <option value="0" {{ $factor->status == '0' ? 'selected' : '' }}>
                                    در انتظار پرداخت
                                </option>
                                <option value="1" {{ $factor->status == '1' ? 'selected' : '' }}>
                                    پرداخت شده
                                </option>
                                <option value="2" {{ $factor->status == '2' ? 'selected' : '' }}>
                                    عودت پرداخت
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نحوه پرداخت</label>
                            <select class="form-control" name="type" id="payment_type1" onchange="paymentType(1)">
                                <option value="online" {{ $factor->type == 'online' ? 'selected' : '' }}>آنلاین</option>
                                <option value="card_reader" {{ $factor->type == 'card_reader' ? 'selected' : ''
                                    }}>کارتخوان</option>
                                <option value="card" {{ $factor->type == 'card' ? 'selected' : '' }}>کارت به کارت
                                </option>
                                <option value="wallet" {{ $factor->type == 'wallet' ? 'selected' : '' }}>کیف پول
                                </option>
                                <option value="cash" {{ $factor->type == 'cash' ? 'selected' : '' }}>پول نقد</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 d-none" id="number_card1">
                            <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                            <input class="form-control" name="number_card" type="number" id="number_card_input1" value="{{ $factor->number_card }}">
                        </div>
                        <div class="col-12 col-md-3 mb-2 d-none" id="payment_code1">
                            <label>شماره پیگیری (4 رقم آخر)</label>
                            <input class="form-control" name="payment_code" type="number" id="payment_code_input1" value="{{ $factor->payment_code }}">
                        </div>
                        <div class="col-12 col-md-3 mb-2 d-none" id="bill_image1">
                            <label>رسید دریافت وجه</label>
                            <br>
                            <label for="bill_image_input1" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                    class="fa fa-cloud-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                            {{-- Ajax upload widget: the file posts to /dashboard/uploadFilesTemporarily and its
                                 temp media id lands in #bill_image_input1_path (name=url_bill_image), which the
                                 controller attaches via MediaLibraryHelper::attachTo. The receipt is required for
                                 card/cash, but that is enforced by the submit handler below + server-side, NOT HTML5
                                 `required` (this input is display:none - HTML5 required aborts submit as "not
                                 focusable"), so data-keep-optional silences paymentType()'s required flag.
                                 data-has-existing lets the submit check skip when a receipt is already on file. --}}
                            <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file"
                                id="bill_image_input1" accept=".jpg,.jpeg,.png" data-buttonname="btn-white"
                                data-keep-optional="1" data-has-existing="{{ $factor->bill_image ? '1' : '' }}">
                            <div id="bill_image_input1_src" class="show"></div>
                            <input type="hidden" id="bill_image_input1_path" class="path" name="url_bill_image">
                        </div>
                        @if(is_null(auth()->user()->id == $factor->support_id) || auth()->user()->hasRole('superadmin'))
                        <div class="col-12 col-md-4 mb-3">
                            <label>انتقال به همکار</label>
                            <select class="form-control" name="support_id">
                                <option value="">انتخاب...</option>
                                @foreach(\App\Models\User::whereLevel('admin')->where('id' , '!=' ,
                                auth()->user()->id)->get() as $item)
                                <option value="{{ $item->id }}" {{ $item->id == $factor->support_id ? 'selected' : ''
                                    }}>
                                    {{ $item->name . ' ' . $item->family }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @if($factor->verify)
                        <div class="col-12 col-md-4 mb-3">
                            <label>تایید نهایی</label>
                            <span class="text-success">این فاکتور تایید نهایی شده است</span>
                        </div>
                        @else
                        <div class="col-12 col-md-4 mb-3">
                            <label>تایید نهایی</label>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheck">
                                    <label class="custom-control-label" for="customCheck">این فاکتور را تایید نهایی
                                        میکنم</label>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @include('Admin.businessTransfer.inlineField', [
                            'currentBusinessIds' => $factor->ghorfeOnlineLists->modelKeys(),
                        ])
                        <div class="col-12 col-md-4 mb-3">
                            <input type="submit" class="btn btn-success border-0" value="ذخیره">
                        </div>
                        @endrole

                        @role('superadmin-representation')
                        <div class="col-12 col-md-4 mb-3">
                            <label>مبلغ کل (ریال)</label>
                            <input type="text" value="{{ number_format($factor->price) }}" class="form-control"
                                name="price" required oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>وضعیت پرداخت</label>
                            <select class="form-control" name="status">
                                <option value="0" {{ $factor->status == '0' ? 'selected' : '' }}>
                                    در انتظار پرداخت
                                </option>
                                <option value="1" {{ $factor->status == '1' ? 'selected' : '' }}>
                                    پرداخت شده
                                </option>
                            </select>
                            <p>توجه:درصورت تغییر وضعیت فاکتوربه پرداخت شده ،مبلغ فاکتور از اعتبار شما کسر خواهد شد.</p>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <input type="submit" class="btn btn-success border-0" value="ذخیره">
                        </div>
                        @endrole
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-t-20" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                <span>توضیحات همکاران</span>
                <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModal">ثبت توضیح
                    جدید</span>
                <div class="mt-4">
                    @if(count($comments) > 0)
                    @foreach($comments as $comment)
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="panel-heading">
                            <span class="d-inline-block">{{ $comment['user'] }}</span>
                            <span class="text-black-50">{{ $comment['date'] }}</span>
                        </div>
                        <p class="mt-4">
                            {!! $comment['description'] !!}
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
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>

<script>
    // .date fields are built by jalaliDatePicker.js.
    @if(session()->get('factor_update') == 'success')
    toastr.success('با موفقیت انجام شد!');
    @elseif(session()->get('factor_update') == 'danger')
    toastr.success('اعتبار شما کافی نیست!');
    @endif

    // The receipt is required for card-to-card, POS (card reader) and cash payments. The ajax
    // widget's file input is display:none so HTML5 `required` can't be used (it aborts submit as
    // "not focusable"); this submit check gives immediate feedback and the server re-validates. A
    // receipt counts if one was just uploaded (hidden url_bill_image populated) or is already on file.
    $('#payment_type1').closest('form').on('submit', function (e) {
        var paymentType = $('#payment_type1').val();
        var hasReceipt = $('#bill_image_input1_path').val()
            || $('#bill_image_input1').data('has-existing');
        if ((paymentType === 'card' || paymentType === 'cash' || paymentType === 'card_reader') && !hasReceipt) {
            e.preventDefault();
            toastr.error('برای پرداخت کارت به کارت، کارتخوان و نقدی، بارگذاری رسید دریافت وجه الزامی است.');
        }
    });

</script>
@endsection
