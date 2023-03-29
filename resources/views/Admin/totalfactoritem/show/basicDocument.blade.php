<div class="card-group" id="basic_documnet_div">
    <form action="{{ route('totalFactorItem.uploadDocument') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="user_id" value="{{ $factor->user->id }}">
        <input type="hidden" name="total_factor_item_id" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="14">
        <div class="tab-pane fade show active" id="list-basic-document" role="tabpanel"
             aria-labelledby="list-basic-document-list">
            @include('layout.uploadSample')
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger mb-4" role="alert">
                    {{ $error }}
                </div>
            @endforeach
            <div class="col-12 profile-box bg-gray py-3 my-4 position-relative">
                <div class="col-12">
                    <div class="card-group">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                @if (is_null($factor->user->imgmeli))
                                    <div id="imgmeli_src" class="show">
                                        <img class="card-img-top" height="150px">
                                    </div>
                                @else
                                    <div id="imgmeli_src" class="show">
                                        <a href="{{ asset($factor->user->imgmeli) }}" target="_blank">
                                            <img src="{{ asset($factor->user->imgmeli) }}" class="card-img-top"
                                                 height="150px">
                                        </a>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">کارت ملی
                                    </h5>
                                    <label for="imgmeli" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i>انتخاب
                                        تصویر </label>
                                    <input name="imgmeli" id="imgmeli" type="file"
                                           class="filestyle file-upload file_upload_ajax file-up"
                                           data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="imgmeli" class="path" name="urlimgmeli" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                @if (is_null($factor->user->imgshenasname))
                                    <div id="imgshenasname_src" class="show">
                                        <img class="card-img-top" height="150px">
                                    </div>
                                @else
                                    <div id="imgshenasname_src" class="show">
                                        <a href="{{ asset($factor->user->imgshenasname) }}" target="_blank">
                                            <img id="imgshenasname_src" src="{{ asset($factor->user->imgshenasname) }}"
                                                 class="card-img-top" height="150px">
                                        </a>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title">شناسنامه
                                    </h5>
                                    <label for="imgshenasname" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i>انتخاب
                                        تصویر </label>
                                    <input name="imgshenasname" id="imgshenasname" type="file"
                                           class="filestyle file-upload file_upload_ajax file-up"
                                           data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="imgshenasname" class="path" name="urlimgshenasname"
                                           value="">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <h4>اطلاعات ضامن</h4>
                            <hr>
                        </div>
                        @foreach ($item['contract_forms']->where('level', 'basic_documents') as $contract_form)
                            @if ($contract_form->value == $item['guarantee_type'])
                                @switch($contract_form->type)
                                    @case('file')
                                    @case('file')
                                    <div class="col-12 col-md-3 mb-2 mt-3">
                                        <div class="card">
                                            @if (!isset($item['contract_values'][$contract_form->name]))
                                                <div id="{{ $contract_form->name }}_src" class="show">
                                                    <img class="card-img-top" height="150px">
                                                </div>
                                            @else
                                                <div id="{{ $contract_form->name }}_src" class="show">
                                                    <a href="{{ asset($item['contract_values'][$contract_form->name]) }}"
                                                       target="_blank">
                                                        <img id="{{ $contract_form->name }}_src"
                                                             src="{{ asset($item['contract_values'][$contract_form->name]) }}"
                                                             class="card-img-top" height="150px">
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    {{ $contract_form->help }}
                                                </h5>
                                                <label for="{{ $contract_form->name }}" id="up-label"
                                                       class="btn badge-secondary"><i
                                                        class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                    انتخاب تصویر
                                                </label>
                                                <input name="item[{{ $contract_form->name }}]"
                                                       id="{{ $contract_form->name }}"
                                                       type="file"
                                                       class="filestyle file-upload file_upload_ajax file-up"
                                                       data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                                       name="item[{{ 'url' . $contract_form->name }}]" value="">
                                            </div>
                                        </div>
                                    </div>
                                    @break
                                    @break
                                    @case('text')
                                    <div class="col-12 col-md-3 mb-2">
                                        <label for="item[{{ $contract_form->name }}]">{{ $contract_form->help
                                }}</label>
                                        <input type="text" class="form-control {{ $contract_form->class }}" {{
                                $contract_form->required == true ? 'required' : '' }}
                                        placeholder="{{ $contract_form->placeholder }}"
                                               value="{{ isset($item['contract_values'][$contract_form->name]) ?
                            $item['contract_values'][$contract_form->name] : '' }}"
                                               name="item[{{ $contract_form->name }}]">
                                    </div>
                                    @break
                                    @case('select')
                                    <div class="col-12 col-md-6 mb-2">
                                        <label for="item[{{ $contract_form->name }}]">{{ $contract_form->help
                                }}</label>
                                        <select class="form-control {{ $contract_form->class }}" {{ $contract_form->required == true
                                ? 'required' : '' }}
                                        name="item[{{ $contract_form->name }}]">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($contract_form->beforeformitems as $select_item)
                                                <option value="{{ $select_item->value }}" {{
                                    isset($item['contract_values'][$contract_form->name]) &&
                                    $select_item->value ==
                                    $item['contract_values'][$contract_form->name] ? 'selected' : '' }}>
                                                    {{ $select_item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @break
                                @endswitch
                            @endif
                        @endforeach
                    </div>
                </div>
                <p class="bold font-16 mt-3 text-black-50">راهنمای بارگزاری
                    فایل در سامانه</p>
                <ul class="px-0 mr-3">
                    <li class="my-1">پسوند های مجاز: jpg,jpeg,png</li>
                    <li class="my-1">حجم فایل نباید بیشتر از 6 مگابایت
                        باشد
                    </li>
                </ul>
                <div class="col-12 mb-2 mt-auto">
                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
                </div>
            </div>
        </div>
    </form>
    <div class="col-md-12 row mt-2">
        <span class="btn btn-primary py-1 px-2 text-white" data-toggle="modal" data-target="#linkModal">ارسال لینک
            پرداخت</span>
    </div>
</div>
<form class="form-horizontal" role="form" method="post"
      action="{{ route('totalFactorItem.update', [$totalFactorItem->id]) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="type" value="confirm">
    @role('filing|admin-site')
    @if ($item['status'] == 14)
        <p class="text-warning col-12"> پشتیبان محترم تایید مدارک به منظور تایید
            پرونده از جانب شما و ارسال پرونده برای تایید امورتسهیلات می‌باشد.</p>
        <button class="btn btn-success">تایید و ارجاع به امور تسهیلات</button>
    @endif
    @endrole
</form>
