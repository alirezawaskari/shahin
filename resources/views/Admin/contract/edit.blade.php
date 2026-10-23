@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('contract.update',$contract->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>ارجاع به </label>
                        <select name="status" class="form-control" title="status">
                            <option value="">لطفا انتخاب کنید..</option>
                            @foreach($contract::STATUS as $ind=>$status)
                                @if($ind != $contract->status)
                                <option value="{{$ind}}" @if($contract->type == $ind) selected @endif>{{$status}}</option>
                                @endif
                            @endforeach
                        </select>
                        @if($errors->has('status'))
                            <span class="help-block sub-error "> {{$errors->first('status')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$contract->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif

                    </div>
                    <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع *</label>
                        <select name="type" class="form-control" title="type">
                            <option value="">لطفا انتخاب کنید..</option>
                            <option value="1" @if($contract->type == 1) selected @endif>خرید</option>
                            <option value="2" @if($contract->type == 2) selected @endif>فروش</option>
                            <option value="3" @if($contract->type == 3) selected @endif>واگذاری</option>
                        </select>
                        @if($errors->has('type'))
                            <span class="help-block sub-error"> {{$errors->first('type')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>پوشه</label>
                        <select name="contract_folder_id" class="form-control" title="پوشه">
                            <option value="">دسته بندی نشده</option>
                            @foreach($folders as $folderId => $folderTitle)
                                <option value="{{ $folderId }}"
                                        @if($contract->contract_folder_id == $folderId) selected @endif>{{ $folderTitle }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('contract_folder_id'))
                            <span class="help-block sub-error">{{ $errors->first('contract_folder_id') }}</span>
                        @endif
                    </div>
                    <br/> <br/> <br/> <br/>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="d-block">فایل آپلود شده :</label>

                        {{-- Contract::getFileAttribute() is Spatie-first with a legacy-column
                             fallback, so $contract->file resolves for both. It's a document
                             (pdf/doc), so it's shown as a link rather than the image-oriented
                             currentMedia partial.

                             The file is optional — a contract may be text-only, and the original
                             file is only offered for download when one exists. The 'file'
                             collection is singleFile(), so uploading a new one replaces it. --}}
                        @php $__file = $contract->getFirstMedia('file'); @endphp
                        @if($__file)
                            <a href="{{ $__file->getFullUrl() }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                        @elseif($contract->file)
                            <a href="{{ $contract->file }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                            <small class="d-block text-warning mt-1">فایل قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                        @else
                            <span class="text-muted">فایلی بارگذاری نشده است</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری فایل
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax file-up" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="col-12 col-md-10 mb-3">
                        <label>محتوا </label>
                        <textarea class="form-control" name="content" rows="5"
                                  cols="5" title="محتوا">{{$contract->content}}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif
                    </div>
                    <br/>

                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="/dashboard/contract" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                        <a href="/dashboard/contract/{{ $contract->id }}/export" target="_blank"
                           class="btn btn-info">خروجی قرارداد</a>
                        @if($contract->hasOriginalFile())
                            <a href="/dashboard/contract/{{ $contract->id }}/download"
                               class="btn btn-secondary">دانلود فایل اصلی</a>
                        @endif
                    </div>
                </div>
            </form>

        </div>
    </div>

    {{-- محل های استفاده: the ContractUsage rows. One contract can be active for
         several services, roles and locations at the same time, so this lists them
         all rather than pretending a contract belongs to one place. --}}
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>محل های استفاده این قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>محل استفاده</th>
                    <th>مورد</th>
                    <th>شماره قرارداد</th>
                    <th>تاریخ قرارداد</th>
                    <th>وضعیت</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($contract->usages as $usage)
                    <tr>
                        <td>{{ $usage->location_label }}</td>
                        <td>{{ $usage->owner_label }}</td>
                        <td>{{ $usage->contract_number ?: '—' }}</td>
                        <td>{{ $usage->contract_date ? verta($usage->contract_date)->format('Y/m/d') : '—' }}</td>
                        <td>
                            @if($usage->is_active)
                                <span class="badge badge-success">فعال</span>
                            @else
                                <span class="badge badge-secondary">غیرفعال</span>
                            @endif
                        </td>
                        <td>
                            <a href="/dashboard/contract/{{ $contract->id }}/export/{{ $usage->id }}" target="_blank"
                               class="btn btn-info btn-sm">خروجی</a>
                            <form method="post" action="{{ route('contractUsage.destroy', [$usage->id]) }}"
                                  class="d-inline">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('آیا مطمئن هستید؟ فقط اتصال حذف می شود و خود قرارداد باقی می ماند.')">
                                    حذف اتصال
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            این قرارداد هنوز به خدمت یا نقشی متصل نشده است.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- فایل‌های مدیریت: up to 5, changeable only while the contract is not locked by a signature. --}}
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>فایل‌های مدیریت این قرارداد ({{ $contract->getMedia('staff_attachments')->count() }} از ۵)</h4>
                <div class="clearfix"></div>
            </div>

            @if($staffLocked)
                <div class="alert alert-secondary small">این قرارداد امضا شده است؛ فایل‌های مدیریت دیگر قابل تغییر نیستند.</div>
            @endif

            <ul class="list-unstyled mb-3">
                @forelse($contract->getMedia('staff_attachments') as $staffFile)
                    <li class="d-flex align-items-center justify-content-between border rounded p-2 mb-2 small">
                        <span class="text-truncate me-2">{{ $staffFile->file_name }}</span>
                        <span class="d-flex gap-1">
                            <a href="{{ $staffFile->getUrl() }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm py-0 px-2">مشاهده</a>
                            @unless($staffLocked)
                                <form method="post" action="{{ route('contract.staffFiles.destroy', [$contract->id, $staffFile->id]) }}" class="d-inline">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                    <button type="submit" class="btn btn-danger btn-sm py-0 px-2"
                                            onclick="return confirm('این فایل حذف شود؟');">حذف</button>
                                </form>
                            @endunless
                        </span>
                    </li>
                @empty
                    <li class="text-muted small">فایلی بارگذاری نشده است.</li>
                @endforelse
            </ul>

            @unless($staffLocked)
                <form method="post" action="{{ route('contract.staffFiles.store', $contract->id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="file" name="staff_files[]" class="form-control form-control-sm mb-2" multiple
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted d-block mb-2">فرمت‌های مجاز: pdf، doc، docx، jpg، jpeg، png - حداکثر ۱۰ مگابایت برای هر فایل</small>
                    @if($errors->has('staff_files'))
                        <span class="help-block sub-error d-block mb-2">{{ $errors->first('staff_files') }}</span>
                    @endif
                    <button type="submit" class="btn btn-success btn-sm">بارگذاری فایل‌های مدیریت</button>
                </form>
            @endunless
        </div>
    </div>

@stop
@section('js')

@stop
