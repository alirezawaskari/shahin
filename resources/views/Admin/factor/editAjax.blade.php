<div class="">
    <div class="modal fade" id="commentModalFactor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت توضیح جدید</h5>
                </div>
                <form id="factor-comment-form-modal">
                    <div class="modal-body">
                        <textarea required class="form-control" name="comment" id="comment-body" cols="30" rows="10"
                            placeholder="توضیح خود را بنویسید..."></textarea>
                        <input type="hidden" value="{{ $factor->id }}" name="factor_id">
                    </div>

                    <div class="modal-footer">
                        <span class="btn btn-primary" id="submit-factor-comment-modal">ثبت</span>
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
                        aria-controls="home" aria-selected="false">جزئیات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                        aria-controls="profile" aria-selected="true">ویرایش اطلاعات مالی</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="comments-tab" data-toggle="tab" href="#comments" role="tab"
                        aria-controls="contact" aria-selected="false">توضیحات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link show" id="changes-tab" data-toggle="tab" href="#changes" role="tab"
                        aria-controls="changes" aria-selected="false">تغییرات</a>
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
                                    <td>{{ $user->province_id != null ?
                                        \App\Models\Province::find($user->province_id)->name : '' }} - {{ $user->city_id
                                        != null ? \App\Models\City::find($user->city_id)->name : '' }}</td>
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
                                    <td> {{ $factor::TYPE[$factor->type] }} (زمان پرداخت فاکتور : {{$factor->status == 1
                                        ? verta($factor->updated_at)->format('%d %B ، %Y - H:i') : 'پرداخت نشده' }})
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
                                    <td id="bill_image_td">
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
                                    <td>{{ !is_null($factor->coupon_id) ? $factor->coupon->title .' - '
                                        .$factor->coupon->discount . ' % ' : 'ثبت نشده'}}</td>
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
                    <form class="form-horizontal" id="form-status-mali" enctype="multipart/form-data">
                        {{-- @csrf--}}
                        {{-- @method('PATCH')--}}
                        <div class="row">
                            @include('Admin.layouts.errors')
                            @permission('factor-mali-information-edit')
                            <div class="col-12 col-md-4 mb-3">
                                <label>مبلغ کل (ریال)</label>
                                <input type="text" value="{{ number_format($factor->price) }}" class="form-control"
                                    name="price" required oninput="separatePrices(this)">
                            </div>
                            <div class="col-12 col-md-4 mb-3">
                                <input type="hidden" name="resnumber" value="{{$factor->resnumber}}">
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
                                <select class="form-control" name="type" id="payment_type_edit_factor"
                                    onchange="paymentType('_edit_factor')">
                                    <option value="online" {{ $factor->type == 'online' ? 'selected' : '' }}>آنلاین
                                    </option>
                                    <option value="card_reader" {{ $factor->type == 'card_reader' ? 'selected' : ''
                                        }}>کارتخوان</option>
                                    <option value="card" {{ $factor->type == 'card' ? 'selected' : '' }}>کارت به کارت
                                    </option>
                                    <option value="cash" {{ $factor->type == 'cash' ? 'selected' : '' }}>پول نقد
                                    </option>
                                    <option value="account">واریز به حساب</option>
                                    <option value="wallet">کسر از کیف پول</option>
                                    <option value="credit">کسر از حواله</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2 d-none" id="number_card_edit_factor">
                                <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                                <input class="form-control" name="number_card" type="number"
                                    id="number_card_input_edit_factor">
                            </div>
                            <div class="col-12 col-md-2 mb-2 d-none" id="payment_code_edit_factor">
                                <label>شماره پیگیری (4 رقم آخر)</label>
                                <input class="form-control" name="payment_code" type="number"
                                    id="payment_code_input_edit_factor">
                            </div>
                            <div class="col-12 col-md-3 mb-2 d-none" id="sheba_number_edit_factor">
                                <label>شماره شبا</label>
                                <input class="form-control" name="sheba_number" type="text" id="sheba_number_input_edit_factor">
                            </div>
                            <div class="col-12 col-md-2 mb-2 d-none" id="bill_image_edit_factor">
                                <label>رسید دریافت وجه</label>
                                <br>
                                <label for="bill_image_input_edit_factor" id="up-label" class="btn badge-secondary"><i
                                        class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                <input class="form-control file-up" name="bill_image" type="file"
                                    id="bill_image_input_edit_factor" accept=".jpg,.jpeg,.png">
                            </div>
                            <div class="col-12 col-md-2 mb-2 d-none" id="password_edit_factor">
                                <label>رمز عبور کاربر</label>
                                <input class="form-control" name="password" type="number"
                                    id="password_input_edit_factor">
                                <p>لطفا رمز عبور 6 رقمی که برای کاربر ارسال شده است را وارد کنید.</p>
                            </div>
                            <div class="col-12 col-md-2 mb-2 d-none" id="send_password_edit_factor">
                                <input type="hidden" value="{{ $user->phone }}" name="phone" class="phone">
                                <span class="send_user_password btn btn-success-default text-white px-3"> ارسال رمز <i
                                        class="fa fa-paper-plane mr-1" aria-hidden="true"></i></span>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <label>انتقال به همکار</label>
                                <select class="form-control select2" id="support_id" name="support_id">
                                    <option value="">انتخاب...</option>
                                    @foreach(\App\Models\User::where('level','admin')->where('id' , '!=' ,
                                    auth()->user()->id)->get() as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $factor->support_id ? 'selected' :
                                        ''
                                        }}>
                                        {{ $item->name . ' ' . $item->family }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endpermission
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
                            @endpermission
                            <div class="col-12 col-md-4 mb-3">
                                <input class="btn btn-success border-0 factor-update-submit" value="ذخیره">
                            </div>

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

                            </div>
                            <div class="col-12 mb-3">
                                <p class="alert alert-warning">توجه:درصورت تغییر وضعیت فاکتوربه پرداخت شده ،مبلغ فاکتور
                                    از اعتبار شما کسر خواهد شد.
                                </p>
                            </div>
                            <div class="col-12 mb-3">
                                <span class="btn btn-success border-0 factor-update-submit">ذخیره </span>
                            </div>
                            @endrole
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade p-t-20" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                    <span>توضیحات همکاران</span>
                    <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModalFactor">ثبت
                        توضیح جدید</span>
                    <div class="mt-4 comment-item">
                        @if(count($comments) > 0)
                        <div class="modal-body table-responsive card-body width-auto">
                            <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                                id="datatable-history-blade">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">آپدیت کننده</th>
                                        <th scope="col">پیگیری کننده</th>
                                        <th scope="col">تاریخ ثبت</th>
                                        <th scope="col">گزارش ها</th>
                                    </tr>
                                </thead>
                                <tbody class="histories-table-body" id="tbody-histories">
                                    @php($i=1)
                                    @foreach($comments as $comment)
                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td>{{$comment['user']}}</td>
                                        <td>{{$comment['callcenter']}}</td>
                                        <td>{{$comment['date']}}</td>
                                        <td>{{$comment['description']}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p>هیچ توضیحی ثبت نشده</p>
                        @endif
                    </div>
                </div>
                <div class="tab-pane fade p-t-20" id="changes" role="tabpanel" aria-labelledby="changes-tab">
                    <div class="mt-4 comment-item">
                        @if($changes)
                        <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                            id="datatable-history-blade">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">عامل تغییر</th>
                                    <th scope="col">نوع تغییر</th>
                                    <th scope="col">فیلد تغییرداده شده</th>
                                    <th scope="col">تاریخ ثبت</th>
                                </tr>
                            </thead>
                            <tbody class="histories-table-body" id="tbody-histories">
                                @php($i=1)
                                @foreach($changes as $change)
                                <tr>
                                    <td>{{$i++}}</td>
                                    <td>{{$change->user ? $change->user->name.' '.$change->user->family : 'ثبت نشده'}}
                                    </td>
                                    <td>{{$change->event}}</td>
                                    <td>
                                        @foreach($change->getModified() as $field => $value)
                                        {{ ucfirst($field).'' }}
                                        <table class="diff">
                                            {{-- <tr>
                                                <td style="background: #ffe9e9" class="deleted-line">{{ $value["old"] }}
                                                </td>
                                                <td style="background: #e9ffe9" class="added-line">{{ $value["new"] }}
                                                </td>
                                            </tr> --}}
                                        </table>
                                        @endforeach
                                    </td>
                                    <td>{{verta($change->created_at)->format('Y/m/d H:i:s')}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p>تغییری برای این تراکنش ثبت نشده است.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready( function () {
        $('#datatable-history-blade').DataTable({searching: false});
        $('#support_id').select2();
    } );
</script>
