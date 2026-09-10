{{--
    اتصال قرارداد — the reusable association widget.

    Mounted on service edit, role edit and the location-wide purchase contract, it
    posts to ContractUsageController@store rather than to the owner's own update
    action, so the association never depends on which fields that form happens to
    submit.

    Expects:
      $location    string   a ContractUsage::LOCATIONS key
      $ownerType   ?string  'service' | 'role' | 'product' | null (location-wide)
      $ownerId     ?int
      $usage       ?ContractUsage  what is currently attached here
      $folders     array    ContractFolder::options()
      $widgetId    string   unique per widget on the page — the ajax upload helper
                            keys off DOM ids (#<id>_path / #<id>_src), so two
                            widgets on one page must not share one.
--}}
@php
    $widgetId = $widgetId ?? ('contract_' . $location . '_' . ($ownerId ?? 0));
    $currentMode = $usage ? 'existing' : 'none';
@endphp

<div class="card contract-attach-widget" data-widget="{{ $widgetId }}">
    <div class="card-body">
        <div class="x_title">
            <h4>قرارداد — {{ \App\Models\ContractUsage::LOCATIONS[$location] ?? $location }}</h4>
            <div class="clearfix"></div>
        </div>

        @if($usage)
            <div class="alert alert-info">
                قرارداد فعلی:
                <strong>{{ optional($usage->contract)->title }}</strong>
                @if($usage->contract_number) — شماره {{ $usage->contract_number }} @endif
                @if($usage->contract_date) — تاریخ {{ verta($usage->contract_date)->format('Y/m/d') }} @endif
                <a href="/dashboard/contract/{{ $usage->contract_id }}/export/{{ $usage->id }}" target="_blank"
                   class="btn btn-info btn-sm mr-2">خروجی</a>
                @if(optional($usage->contract)->hasOriginalFile())
                    <a href="/dashboard/contract/{{ $usage->contract_id }}/download"
                       class="btn btn-secondary btn-sm">دانلود فایل اصلی</a>
                @endif
            </div>
        @endif

        <form method="post" action="{{ route('contractUsage.store') }}">
            {{ csrf_field() }}
            <input type="hidden" name="location" value="{{ $location }}">
            <input type="hidden" name="owner_type" value="{{ $ownerType }}">
            <input type="hidden" name="owner_id" value="{{ $ownerId }}">

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="{{ $widgetId }}_mode_none" name="mode" value="none"
                               class="custom-control-input contract-mode" {{ $currentMode === 'none' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="{{ $widgetId }}_mode_none">بدون قرارداد</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="{{ $widgetId }}_mode_existing" name="mode" value="existing"
                               class="custom-control-input contract-mode" {{ $currentMode === 'existing' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="{{ $widgetId }}_mode_existing">استفاده از قرارداد موجود</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="{{ $widgetId }}_mode_new" name="mode" value="new"
                               class="custom-control-input contract-mode">
                        <label class="custom-control-label" for="{{ $widgetId }}_mode_new">ایجاد قرارداد جدید</label>
                    </div>
                </div>
            </div>

            {{-- انتخاب قرارداد موجود --}}
            <div class="row contract-pane" data-pane="existing" style="display:none">
                <div class="col-12 col-md-4 mb-3">
                    <label>پوشه</label>
                    <select class="form-control contract-folder-filter" title="پوشه">
                        <option value="">دسته بندی نشده</option>
                        @foreach($folders as $folderId => $folderTitle)
                            <option value="{{ $folderId }}"
                                    @if(optional(optional($usage)->contract)->contract_folder_id == $folderId) selected @endif>
                                {{ $folderTitle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 mb-3">
                    <label>قرارداد *</label>
                    <select name="contract_id" class="form-control contract-select" title="قرارداد">
                        @if($usage && $usage->contract)
                            <option value="{{ $usage->contract_id }}" selected>{{ $usage->contract->title }}</option>
                        @else
                            <option value="">لطفا انتخاب کنید..</option>
                        @endif
                    </select>
                    @if($errors->has('contract_id'))
                        <span class="help-block sub-error">{{ $errors->first('contract_id') }}</span>
                    @endif
                </div>

                {{-- ویرایش قرارداد انتخاب شده, in place. The bank contract is shared, so the
                     warning below is populated whenever it has more than this one usage. --}}
                <div class="col-12 contract-edit-box" style="display:none">
                    <div class="border rounded p-3 mb-3">
                        <h5 class="mb-3">ویرایش قرارداد انتخاب شده</h5>

                        <div class="alert alert-warning contract-shared-warning" style="display:none"></div>

                        {{-- Prefilled here because the fields above are already rendered with
                             this contract's current text. Javascript rewrites it after fetching
                             a different contract; the server ignores the edits unless it matches
                             the selected contract, so a failed fetch can never blank a body. --}}
                        <input type="hidden" name="edit_contract_id" class="contract-edit-id"
                               value="{{ optional($usage)->contract_id }}">

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label>عنوان قرارداد *</label>
                                <input type="text" name="edit_title" class="form-control contract-edit-title"
                                       value="{{ optional(optional($usage)->contract)->title }}">
                                @if($errors->has('edit_title'))
                                    <span class="help-block sub-error">{{ $errors->first('edit_title') }}</span>
                                @endif
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label>نوع</label>
                                <select name="edit_type" class="form-control contract-edit-type" title="نوع">
                                    @foreach(\App\Models\Contract::TYPE as $typeId => $typeTitle)
                                        <option value="{{ $typeId }}"
                                                @if(optional(optional($usage)->contract)->type == $typeId) selected @endif>
                                            {{ $typeTitle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label>متن قرارداد</label>
                                <textarea name="edit_content" id="{{ $widgetId }}_edit_content"
                                          class="form-control contract-edit-content contract-rich-text"
                                          rows="10">{{ optional(optional($usage)->contract)->content }}</textarea>
                                @if($errors->has('edit_content'))
                                    <span class="help-block sub-error">{{ $errors->first('edit_content') }}</span>
                                @endif
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="d-block">جایگزینی فایل قرارداد (اختیاری)</label>
                                <label for="{{ $widgetId }}_editfile"
                                       class="btn btn-primary custom-file-upload border border-1">
                                    <i class="fa fa-cloud-upload"></i> بارگذاری فایل جدید
                                </label>
                                <input id="{{ $widgetId }}_editfile" class="d-none file-upload file_upload_ajax"
                                       type="file" name="{{ $widgetId }}_editfile">
                                <div id="{{ $widgetId }}_editfile_src" class="show"></div>
                                <input type="hidden" id="{{ $widgetId }}_editfile_path" class="path"
                                       name="edit_file_path" value="">
                            </div>
                            <div class="col-12 col-md-6 mb-3 contract-current-file" style="display:none">
                                <label class="d-block">فایل فعلی</label>
                                <a href="#" target="_blank" rel="noopener"
                                   class="btn btn-secondary py-1 px-2 contract-current-file-link">مشاهده فایل فعلی</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ایجاد قرارداد جدید: creates a real bank contract, so it stays reusable. --}}
            <div class="row contract-pane" data-pane="new" style="display:none">
                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان قرارداد *</label>
                    <input type="text" name="title" class="form-control" placeholder="عنوان قرارداد جدید"
                           value="{{ old('title') }}">
                    @if($errors->has('title'))
                        <span class="help-block sub-error">{{ $errors->first('title') }}</span>
                    @endif
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نوع *</label>
                    <select name="type" class="form-control" title="نوع">
                        <option value="">لطفا انتخاب کنید..</option>
                        @foreach(\App\Models\Contract::TYPE as $typeId => $typeTitle)
                            <option value="{{ $typeId }}" @if(old('type') == $typeId) selected @endif>{{ $typeTitle }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('type'))
                        <span class="help-block sub-error">{{ $errors->first('type') }}</span>
                    @endif
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>پوشه</label>
                    <select name="contract_folder_id" class="form-control" title="پوشه">
                        <option value="">دسته بندی نشده</option>
                        @foreach($folders as $folderId => $folderTitle)
                            <option value="{{ $folderId }}">{{ $folderTitle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 mb-3">
                    <label>متن قرارداد</label>
                    <textarea name="content" id="{{ $widgetId }}_new_content"
                              class="form-control contract-rich-text" rows="8"
                              title="متن قرارداد">{{ old('content') }}</textarea>
                    @if($errors->has('content'))
                        <span class="help-block sub-error">{{ $errors->first('content') }}</span>
                    @endif
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label class="d-block">فایل قرارداد (اختیاری)</label>
                    <label for="{{ $widgetId }}_file" class="btn btn-primary custom-file-upload border border-1">
                        <i class="fa fa-cloud-upload"></i> بارگذاری
                    </label>
                    {{-- id drives the upload helper's #<id>_path / #<id>_src lookups. --}}
                    <input id="{{ $widgetId }}_file" class="d-none file-upload file_upload_ajax" type="file"
                           name="{{ $widgetId }}_file">
                    <div id="{{ $widgetId }}_file_src" class="show"></div>
                    <input type="hidden" id="{{ $widgetId }}_file_path" class="path" name="file_path" value="">
                </div>
            </div>

            {{-- شماره و تاریخ قرارداد belong to the association, not to the bank contract. --}}
            <div class="row contract-pane" data-pane="meta" style="display:none">
                <div class="col-12 col-md-4 mb-3">
                    <label>شماره قرارداد</label>
                    <input type="text" name="contract_number" class="form-control" placeholder="شماره قرارداد"
                           value="{{ optional($usage)->contract_number }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تاریخ قرارداد</label>
                    {{-- format is what the server receives: Gregorian, straight into a date column. --}}
                    <date-picker
                        id="{{ $widgetId }}_contract_date"
                        name="contract_date"
                        placeholder="تاریخ قرارداد"
                        locale="fa,en"
                        format="YYYY-M-D"
                        value="{{ optional($usage)->contract_date ? $usage->contract_date->format('Y-m-d') : '' }}"
                    ></date-picker>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <button type="submit" class="btn btn-success">ذخیره قرارداد</button>
                </div>
            </div>
        </form>
    </div>
</div>
