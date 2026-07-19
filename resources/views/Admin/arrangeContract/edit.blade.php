@extends('Admin.master')

@section('css')

@stop

@section('content')

    {{--
        Mirrors Admin.arrangeContract.create, against ArrangeContract's real schema
        (id, owner_id, contract_id, file, status, user_id, no). The previous version of
        this file was an unadapted copy of Admin.contract.edit: it read $contract (the
        controller passes $arrange_contract) and title/content/type (columns that don't
        exist here), so the page 500'd before rendering anything.

        Field contract expected by ArrangeContractController@update:
          no            required
          file_path     optional — the temp media id written by adminJs.js
          agency_type_id / center_id  -> owner_id = $rec->user_id
          contract_id   only used to build the post-update redirect
    --}}

    <div class="card">
        <div class="card-body">
            <div class="x_title mb-5">
                <h4>ویرایش تنظیم قرارداد</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('arrangeContract.update', $arrange_contract->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}
                @method('PATCH')
                <input type="hidden" name="contract_id" value="{{$arrange_contract->contract_id}}">

                <div class="row">
                    <div class="col-12 col-md-6 mb-5">
                        <label>شماره قرارداد *</label>
                        <input type="text" class="form-control" placeholder="لطفا شماره را وارد کنید"
                               name="no" value="{{old('no', $arrange_contract->no)}}">
                        @if($errors->has('no'))
                            <span class="help-block sub-error"> {{$errors->first('no')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label class="d-block">وضعیت</label>
                        {{-- status is changed through the dedicated arrangeContract.status route
                             from the list, not by update() — shown read-only for context. --}}
                        <span class="badge badge-info p-2">
                            {{ \App\Models\ArrangeContract::STATUS[$arrange_contract->status] ?? '-' }}
                        </span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="d-block">فایل فعلی :</label>

                        {{-- ArrangeContract::getFileAttribute() is Spatie-first with a legacy-column
                             fallback, so ->file resolves for both. It's a document, so it's a link
                             rather than the image-oriented currentMedia partial.

                             Deliberately NO remove (X) here: the file is REQUIRED (store() validates
                             file_path), so deleting it would strand the record in a state the form
                             can't create. The 'file' collection is singleFile(), so uploading a new
                             one replaces it — changing the file never needs a delete. --}}
                        @php $__file = $arrange_contract->getFirstMedia('file'); @endphp
                        @if($__file)
                            <a href="{{ $__file->getFullUrl() }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                        @elseif($arrange_contract->file)
                            <a href="{{ $arrange_contract->file }}" target="_blank" rel="noopener"
                               class="btn btn-primary py-1 px-2">مشاهده</a>
                            <small class="d-block text-warning mt-1">فایل قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                        @else
                            <span class="text-muted">فایلی بارگذاری نشده است</span>
                        @endif
                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label>جایگزینی فایل</label><br/>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax file-up" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>

                    {{-- edit() only fills the list matching the owner: agency_types when the owner
                         has an admin_representation_id, centers when they have a center_id. --}}
                    @if(count($agency_types))
                        <div class="col-12 col-md-4 mb-5">
                            <label>عاملیت *</label>
                            <select name="agency_type_id" class="form-control" title="">
                                <option value="">لطفا انتخاب کنید..</option>
                                @foreach($agency_types as $agency_type)
                                    <option value="{{$agency_type->id}}"
                                        {{ (string) old('agency_type_id', $agency_type->user_id == $arrange_contract->owner_id ? $agency_type->id : '') === (string) $agency_type->id ? 'selected' : '' }}>
                                        {{$agency_type->title}}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('agency_type_id'))
                                <span class="help-block sub-error"> {{$errors->first('agency_type_id')}} </span>
                            @endif
                        </div>
                    @endif

                    @if(count($centers))
                        <div class="col-12 col-md-4 mb-5">
                            <label>شعبه *</label>
                            <select name="center_id" class="form-control" title="">
                                <option value="">لطفا انتخاب کنید..</option>
                                @foreach($centers as $center)
                                    <option value="{{$center->id}}"
                                        {{ (string) old('center_id', $center->user_id == $arrange_contract->owner_id ? $center->id : '') === (string) $center->id ? 'selected' : '' }}>
                                        {{$center->title}}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('center_id'))
                                <span class="help-block sub-error"> {{$errors->first('center_id')}} </span>
                            @endif
                        </div>
                    @endif

                    <div class="col-12 col-md-12 mb-5">
                        <a href="/dashboard/arrangeContractList/{{$arrange_contract->contract_id}}"
                           class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
