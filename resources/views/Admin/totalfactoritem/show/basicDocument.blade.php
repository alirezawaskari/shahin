<div id="accordion" class="panel-group">
    <div class="panel">
        <div class="panel-heading">
            <h5 class="panel-title">
                <a href="#basic_document_executive" class="accordion-toggle collapsed" data-toggle="collapse"
                    data-parent="#accordion">مدارک اولیه امور اجرایی</a>
            </h5>
        </div>
        <div id="basic_document_executive" class="panel-collapse collapse">
            <div class="panel-body">
                @foreach ($items as $key => $factor_item)
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    @foreach ($factor_item['factorItems'] as $count => $factorItem)
                    <li class="nav-item">
                        <a class="nav-link show {{$count==0 ? 'active' :''}}" onclick="basicDocumentExecutiveAppend('basic_document_executive_collection_{{$factorItem->id}}')" id="basic_document_executive_collection_{{$factorItem->id}}-tab" data-toggle="tab" href="#basic_document_executive_collection_{{$factorItem->id}}" role="tab"
                            aria-controls="factor" aria-selected="false">پرونده {{$factorItem->factor->user->name . ' '.$factorItem->factor->user->family}}</a>
                    </li>
                    @endforeach
                </ul>
                <div class="tab-content" id="myTabContent">
                    @foreach ($factor_item['factorItems'] as $count => $factorItem)
                    <div class="tab-pane fade p-t-20 {{$count==0 ? 'active show' :''}}" id="basic_document_executive_collection_{{$factorItem->id}}" role="tabpanel" aria-labelledby="basic_document_executive_collection_{{$factorItem->id}}-tab">
                        <h6> <i class="fa fa-list"></i> {{ $factorItem->factor->user->name . ' '
                            .$factorItem->factor->user->family.' : '.$factor_item['factoritemable']->title .' - '.
                            $factor_item['financial_plans_type']}}</h6>
                        <div class="card border shadow-lg">
                            <div class="card-header"> تکمیل اطلاعات اجرایی پرونده: </div>
                            <div class="card-body">
                                <form action="{{ route('factorItem.information')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="item[id]" value="{{ $factorItem['id'] }}">
                                    <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                                    <input type="hidden" name="item[center]" value="{{ auth()->user()->center_id }}">
                                    @if(!is_null($factor_item['centers']) || !is_null($factor_item['forms']))
                                    <div class="row p-3 position-relative">
                                        @if(!is_null($factor_item['forms']))
                                        @foreach($factorItem->factoritemable->beforeforms as $form)
                                        @if (in_array('panel_basic_document' , $form->data_display_level))
                                        @include('Admin.factoritem.layouts.formItem')
                                        @endif
                                        @endforeach
                                        @endif
                                        <div class="success-overly position-absolute mx-3"></div>
                                    </div>
                                    <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت">
                                    @endif
                                </form>
                                <hr>
                                @if ($factor_item['status_factor'] == 14)
                                <div class="row p-3 position-relative">
                                    @if(!is_null($factor_item['forms']))
                                    @foreach($factorItem->factoritemable->beforeforms as $form)
                                    @if (in_array('panel_determine_center' , $form->data_display_level))
                                    @include('Admin.factoritem.layouts.formItem')
                                    @endif
                                    @endforeach
                                    @endif
                                    <div class="success-overly position-absolute mx-3"></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- @if (in_array(trim($factor_item['status_factor']) , [0,4,14,16]) )
                        @include('Admin.factoritem.show.turnRatings')
                        @endif --}}
                        @if (in_array(trim($factorItem['status_factor']) , [0,4,14]) )
                        <a href="{{route('factorItem.referral' , [ $factorItem['resnumber']])}}"><span
                                class="btn btn-warning py-1 px-2 text-white mt-2 mb-2">ارجاع به
                                مشاوره</span></a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @if(isset($totalFactorItem))
    <div class="panel">
        <div class="panel-heading">
            <h5 class="panel-title">
                <a href="#basic_document_official" class="accordion-toggle collapsed" data-toggle="collapse"
                    data-parent="#accordion">مدارک اولیه امور اداری</a>
            </h5>
        </div>
        <div id="basic_document_official" class="panel-collapse collapse">
            <div class="panel-body">
                <div class="card-group" id="basic_documnet_div">
                    <form action="{{ route('totalFactorItem.uploadDocument') }}" method="POST"
                        enctype="multipart/form-data">
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
                                                        <img src="{{ asset($factor->user->imgmeli) }}"
                                                            class="card-img-top" height="150px">
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
                                                    <input type="hidden" id="imgmeli" class="path" name="urlimgmeli"
                                                        value="">
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
                                                        <img id="imgshenasname_src"
                                                            src="{{ asset($factor->user->imgshenasname) }}"
                                                            class="card-img-top" height="150px">
                                                    </a>
                                                </div>
                                                @endif
                                                <div class="card-body">
                                                    <h5 class="card-title">شناسنامه
                                                    </h5>
                                                    <label for="imgshenasname" id="up-label"
                                                        class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                            aria-hidden="true"></i>انتخاب
                                                        تصویر </label>
                                                    <input name="imgshenasname" id="imgshenasname" type="file"
                                                        class="filestyle file-upload file_upload_ajax file-up"
                                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="imgshenasname" class="path"
                                                        name="urlimgshenasname" value="">
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
                                                        class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                            aria-hidden="true"></i>
                                                        انتخاب تصویر
                                                    </label>
                                                    <input name="item[{{ $contract_form->name }}]"
                                                        id="{{ $contract_form->name }}" type="file"
                                                        class="filestyle file-upload file_upload_ajax file-up"
                                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                                        name="item[{{ 'url' . $contract_form->name }}]" value="">
                                                </div>
                                            </div>
                                        </div>
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
                                            <select class="form-control {{ $contract_form->class }}" {{
                                                $contract_form->required == true
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
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<span class="btn btn-primary py-1 px-2 text-white mt-4" data-toggle="modal" data-target="#linkModal">ارسال لینک
    پرداخت</span>
<form class="form-horizontal" role="form" method="post"
    action="{{ route('totalFactorItem.update', [$totalFactorItem->id]) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="type" value="confirm">
    @permission('supporter_confirmation')
    @if (in_array(trim($item['status']) , [0,4,14]) )
    <p class="text-warning col-12"> همکار محترم تایید مدارک به منظور تایید
        پرونده از جانب شما و ارسال پرونده برای تایید امورتسهیلات می‌باشد.</p>
    <button class="btn btn-success">تایید و ارجاع به امور تسهیلات</button>
    @endif
    @endpermission
</form>
