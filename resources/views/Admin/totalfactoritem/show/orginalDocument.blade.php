<div id="original_document_div">
    <form action="{{ route('totalFactorItem.uploadDocument') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
        <input type="hidden" name="user_id" value="{{ $user['id'] }}">
        <div class="tab-pane fade show active" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
            @foreach ($errors->all() as $error)
            <div class="alert alert-danger mb-4" role="alert">
                {{ $error }}
            </div>
            @endforeach

            <div class="card col-12 mt-2 pt-4 col-l main border">
                <div class="col-12 mb-2 mt-3">
                    <h5 class="mb-3 card-header"><i class="fa fa-user" aria-hidden="true"></i> اطلاعات
                        وام گیرنده</h5>
                </div>
                @include('layout.uploadSample')
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="card">
                            @if (is_null($factor->user->imgmeli))
                            <div id="imgmeli_src" class="show">
                                <img class="card-img-top" height="150px">
                            </div>
                            @else
                            <div id="imgmeli_src" class="show">
                                <a href="{{ asset($factor->user->imgmeli) }}" target="_blank">
                                    <img src="{{ asset($factor->user->imgmeli) }}" class="card-img-top" height="150px">
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
                                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                                    accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgmeli" class="path" name="urlimgmeli" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
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
                                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                                    accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgshenasname" class="path" name="urlimgshenasname" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card">
                            @if($user->imgturnover)
                            <div id="imgturnover_src" class="show">
                                <a href="{{ asset($user->imgturnover) }}" target="_blank">
                                    <img src="{{ asset($user->imgturnover) }}" class="card-img-top" height="150px">
                                </a>
                            </div>
                            @else
                            <div id="imgturnover_src" class="show">
                                <img class="card-img-top" height="150px">
                            </div>
                            @endif
                            <div class="card-body">
                                <label>تصویر ثنا</label>
                                <br>
                                <label for="imgturnover" id="up-label" class="btn badge-secondary"><i
                                        class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                </label>
                                <input name="imgturnover" type="file" id="imgturnover"
                                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                                    accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="imgturnover" class="path" name="urlimgturnover">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>شماره پرونده مالی</label>
                        <input type="text" class="form-control border-warning" placeholder="شماره پرونده مالی"
                            value="{{ $item['file_number_mali'] }}" name="file_number_mali">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>کدملی</label>
                        <input type="text" class="form-control" placeholder="کدملی" value="{{ $user->melicode }}"
                            name="user[melicode]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>نام پدر</label>
                        <input type="text" class="form-control" placeholder="نام پدر" value="{{ $user->father }}"
                            name="user[father]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>جنسیت</label>
                        <select class="form-control" name="user[sex]">
                            <option value="0" {{ $user->sex == 0 ? 'selected' : '' }}>
                                مرد
                            </option>
                            <option value="1" {{ $user->sex == 1 ? 'selected' : '' }}>
                                زن
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>شغل</label>
                        <input type="text" class="form-control" placeholder="شغل" value="{{ $user->work }}"
                            name="user[work]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>نشانی محل سکونت</label>
                        <input type="text" class="form-control" placeholder="نشانی محل سکونت"
                            value="{{ $user->address }}" name="user[address]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>نشانی محل کار</label>
                        <input type="text" class="form-control" placeholder="نشانی محل کار"
                            value="{{ $user->work_address }}" name="user[work_address]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>تلفن محل سکونت</label>
                        <input type="text" class="form-control" placeholder="تلفن محل سکونت"
                            value="{{ $user->home_phone }}" name="user[home_phone]">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>تلفن محل کار</label>
                        <input type="text" class="form-control" placeholder="تلفن محل کار"
                            value="{{ $user->work_phone }}" name="user[work_phone]">
                    </div>
                    <div class="col-12 col-md-6 mb-3 mt-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="guarantor_checked"
                                class="custom-control-input guarantor_checked" id="customCheck" checked="" name="check">
                            <label class="custom-control-label" for="customCheck">این پرونده دارای ضامن می باشد.</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="guarantor_info d-none">
                <div class="card col-12 mt-2 pt-4 col-l main border">
                    <div class="col-12 mb-2 mt-3">
                        <h5 class="mb-3 card-header"><i class="fa fa-user" aria-hidden="true"></i>
                            اطلاعات ضامن</h5>
                    </div>
                    <div class="row mb-3">
                        @foreach ($item['contract_forms']->where('level',
                        'guarantor_original_documents') as $contract_form)
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
                                    <label for="{{ $contract_form->name }}" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i>
                                        انتخاب تصویر
                                    </label>
                                    <input name="item[{{ $contract_form->name }}]" id="{{ $contract_form->name }}"
                                        type="file" class="filestyle file-upload file_upload_ajax file-up"
                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                    <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                        name="item[{{ 'url' . $contract_form->name }}]" value="">
                                </div>
                            </div>
                        </div>
                        @break
                        @case('text')
                        <div class="col-12 col-md-3 mb-2">
                            <label for="item[{{ $contract_form->name }}]">{{
                                $contract_form->help }}</label>
                            <input type="text" class="form-control {{ $contract_form->class }}" {{
                                $contract_form->required
                            ==
                            true ? 'required' : '' }}
                            placeholder="{{ $contract_form->placeholder }}"
                            value="{{ isset($item['contract_values'][$contract_form->name])
                            ? $item['contract_values'][$contract_form->name] : '' }}"
                            name="item[{{ $contract_form->name }}]">
                        </div>
                        @break
                        @case('select')
                        <div class="col-12 col-md-3 mb-2">
                            <label for="item[{{ $contract_form->name }}]">{{
                                $contract_form->help }}</label>
                            <select class="form-control {{ $contract_form->class }}" {{ $contract_form->required == true
                                ?
                                'required' : '' }}
                                name="item[{{ $contract_form->name }}]">
                                <option value="">انتخاب کنید
                                </option>
                                @foreach ($contract_form->beforeformitems as $select_item)
                                <option value="{{ $select_item->value }}" {{
                                    isset($item['contract_values'][$contract_form->
                                    name]) &&
                                    $select_item->value ==
                                    $item['contract_values'][$contract_form->name] ?
                                    'selected' : '' }}>
                                    {{ $select_item->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @break
                        @case('other_document_image')
                        @if (isset($item['contract_values'][$contract_form->name]))
                        @foreach ($item['contract_values'][$contract_form->name] as $image)
                        <div class="col-12 col-md-3 mb-2 mt-3">
                            <div class="card">
                                <a href="{{ asset($image) }}" target="_blank">
                                    <img src="{{ $image }}" class="card-img-top" height="150px">
                                </a>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        <div class="col-12 mb-3">
                            <div id="addnew" class='file file--upload'>
                                <label for="{{ $contract_form->name }}" id="up-label">
                                    <i class="fa fa-cloud-upload ml-2"></i>
                                    افزودن
                                    سایر مدارک
                                </label>
                                <input name="item[{{ $contract_form->name }}][]" type="file"
                                    id="{{ $contract_form->name }}"
                                    class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                            </div>
                            <div id="{{ $contract_form->name }}_newlink" class="col-12 row">
                                <div>
                                </div>
                            </div>
                            <div id="{{ $contract_form->name }}_newlinktpl">
                                <div id="{{ $contract_form->name }}_src" class="show"></div>
                                <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                    name="item[{{ 'url'.$contract_form->name }}][]">
                            </div>
                        </div>
                        @break
                        @endswitch
                        @endif
                        @endforeach
                    </div>
                </div>
                <div class="card col-12 mt-2 pt-4 col-l main border">
                    <div class="col-12 mb-2">
                        <h5 class="mb-3 card-header"><i class="fa fa-industry" aria-hidden="true"></i>
                            اطلاعات شغل ضامن </h5>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-3 mb-2 mt-3">
                            <label>نوع شغل</label>
                            <select name="item[job_type]" id="jobType" class="form-control job">
                                <option value="employee" {{ isset($item['contract_values']['job_type']) &&
                                    $item['contract_values']['job_type']=='employee' ? 'selected' : '' }}>
                                    کارمند
                                </option>
                                <option value="freelance" {{ isset($item['contract_values']['job_type']) &&
                                    $item['contract_values']['job_type']=='freelance' ? 'selected' : '' }}>
                                    آزاد
                                </option>
                                <option value="pensionary" {{ isset($item['contract_values']['job_type']) &&
                                    $item['contract_values']['job_type']=='pensionary' ? 'selected' : '' }}>
                                    بازنشسته
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divTypeWorkplace">
                            <label>نوع محل کار</label>
                            <select name="item[type_workplace]" id="typeWorkplace" class="form-control job">
                                <option value="governmental" {{ isset($item['contract_values']['type_workplace']) &&
                                    $item['contract_values']['type_workplace']=='governmental' ? 'selected' : '' }}>
                                    دولتی
                                </option>
                                <option value="private" {{ isset($item['contract_values']['type_workplace']) &&
                                    $item['contract_values']['type_workplace']=='private' ? 'selected' : '' }}>
                                    خصوصی
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divPensionaryType">
                            <label>نوع بازنشستگی</label>
                            <select name="item[type_pensionary]" class="form-control job">
                                <option value="civil" {{ isset($item['contract_values']['type_pensionary']) &&
                                    $item['contract_values']['type_pensionary']=='civil' ? 'selected' : '' }}>
                                    کشوری</option>
                                <option value="lashgary" {{ isset($item['contract_values']['type_pensionary']) &&
                                    $item['contract_values']['type_pensionary']=='lashgary' ? 'selected' : '' }}>
                                    لشگری</option>
                                <option value="social-security" {{ isset($item['contract_values']['type_pensionary']) &&
                                    $item['contract_values']['type_pensionary']=='social-security' ? 'selected' : '' }}>
                                    تامین اجتماعی</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divOrganName">
                            <label>نام سازمان | وزارت | اداره</label>
                            <input type="text" name="item[organ_name]" class="form-control"
                                value="{{ isset($item['contract_values']['organ_name']) ? $item['contract_values']['organ_name'] : '' }}">
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divProvince">
                            <label>استان</label>
                            <select class="form-control" name="item[province]">
                                <option value="all">استان</option>
                                @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ isset($item['contract_values']['province']) &&
                                    $province->id
                                    ==
                                    $item['contract_values']['province'] ?
                                    'selected' : '' }}>
                                    {{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divCompanyName">
                            <label>نام شرکت</label>
                            <input type="text" name="item[company_name]" class="form-control"
                                value="{{ isset($item['contract_values']['company_name']) ? $item['contract_values']['company_name'] : '' }}">
                        </div>
                        <div class="col-12 col-md-3 mb-2 mt-3" id="divJobName">
                            <label>عنوان شغلی</label>
                            <input type="text" name="item[job_name]" class="form-control"
                                value="{{ isset($item['contract_values']['job_name']) ? $item['contract_values']['job_name'] : '' }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-12 mt-2 pt-4 col-l main border">
                <div class="col-12 mb-2 mt-3">
                    <h5 class="mb-3 card-header"><i class="fa fa-user" aria-hidden="true"></i> اطلاعات
                        سند ضمانت</h5>
                </div>
                <div class="row mb-3">
                    @foreach ($item['contract_forms']->where('level',
                    'warranty_document_original_documents') as $contract_form)
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
                                <a href="{{ asset($item['contract_values'][$contract_form->name]) }}" target="_blank">
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
                                <label for="{{ $contract_form->name }}" id="up-label" class="btn badge-secondary"><i
                                        class="fa fa-upload ml-2" aria-hidden="true"></i>
                                    انتخاب تصویر
                                </label>
                                <input name="item[{{ $contract_form->name }}]" id="{{ $contract_form->name }}"
                                    type="file" class="filestyle file-upload file_upload_ajax file-up"
                                    data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                    name="item[{{ 'url' . $contract_form->name }}]" value="">
                            </div>
                        </div>
                    </div>
                    @break
                    @case('text')
                    <div class="col-12 col-md-3 mb-2">
                        <label for="item[{{ $contract_form->name }}]">{{
                            $contract_form->help }}</label>
                        <input type="text" class="form-control {{ $contract_form->class }}" {{ $contract_form->required
                        ==
                        true ? 'required' : '' }}
                        placeholder="{{ $contract_form->placeholder }}"
                        value="{{ isset($item['contract_values'][$contract_form->name])
                        ? $item['contract_values'][$contract_form->name] : '' }}"
                        name="item[{{ $contract_form->name }}]">
                    </div>
                    @break
                    @case('select')
                    <div class="col-12 col-md-3 mb-2">
                        <label for="item[{{ $contract_form->name }}]">{{
                            $contract_form->help }}</label>
                        <select class="form-control {{ $contract_form->class }}" {{ $contract_form->required == true ?
                            'required' : '' }}
                            name="item[{{ $contract_form->name }}]">
                            <option value="">انتخاب کنید
                            </option>
                            @foreach ($contract_form->beforeformitems as $select_item)
                            <option value="{{ $select_item->value }}" {{ isset($item['contract_values'][$contract_form->
                                name]) &&
                                $select_item->value ==
                                $item['contract_values'][$contract_form->name] ?
                                'selected' : '' }}>
                                {{ $select_item->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @break
                    @case('other_document_image')
                    @if (isset($item['contract_values'][$contract_form->name]))
                    @foreach ($item['contract_values'][$contract_form->name] as $image)
                    <div class="col-12 col-md-3 mb-2 mt-3">
                        <div class="card">
                            <a href="{{ asset($image) }}" target="_blank">
                                <img src="{{ $image }}" class="card-img-top" height="150px">
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                    <div class="col-12 mb-3">
                        <div id="addnew" class='file file--upload'>
                            <label for="{{ $contract_form->name }}" id="up-label">
                                <i class="fa fa-cloud-upload ml-2"></i>
                                افزودن
                                سایر مدارک
                            </label>
                            <input name="item[{{ $contract_form->name }}][]" type="file" id="{{ $contract_form->name }}"
                                class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                        </div>
                        <div id="{{ $contract_form->name }}_newlink" class="col-12 row">
                            <div>
                            </div>
                        </div>
                        <div id="{{ $contract_form->name }}_newlinktpl">
                            <div id="{{ $contract_form->name }}_src" class="show"></div>
                            <input type="hidden" id="{{ $contract_form->name }}" class="path"
                                name="item[{{ 'url'.$contract_form->name }}][]">
                        </div>
                    </div>
                    @break
                    @endswitch
                    @endif
                    @endforeach
                </div>
            </div>

            <div class="col-12 mb-3 p-4 alert-warning">
                <p class="bold font-16 mt-3">راهنمای
                    بارگزاری
                    فایل در سامانه</p>
                <ul class="px-0 mr-3">
                    <li class="my-1">پسوند های مجاز: jpg,jpeg,png
                    </li>
                    <li class="my-1">حجم فایل نباید بیشتر از 4
                        مگابایت
                        باشد
                    </li>
                </ul>
            </div>

            <div class="col-12 mb-2 mt-auto">
                <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm'] ? 'disabled' : ''
                    }}>
            </div>
        </div>
    </form>
</div>
