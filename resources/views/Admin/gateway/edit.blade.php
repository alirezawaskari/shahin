@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                class="modal-content p-5"
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('gateway.update',['gateway'=>$gateway]) }}"
            >
                @method('PUT')
                @csrf
                <h3 class="text-center">ویرایش درگاه</h3>
                @include('Admin.layouts.errors')

                <div class="card">
                    <div class="mb-3">
                        <label class="form-label">نام درگاه</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $gateway->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نام فارسی درگاه</label>
                        <input type="text" name="fa_name" class="form-control" value="{{ old('fa_name', $gateway->fa_name) }}">
                    </div>
                    <div class="card-body">
                        @include('Admin.partials.currentMedia', ['owner' => $gateway, 'field' => 'logo', 'boxH' => 150, 'required' => true])
                        <label>لوگو</label>
                        <br>
                        <label for="logo" id="up-label" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-upload ml-2"></i>
                            تصویر
                        </label>
                        <input name="logo" type="file" id="logo"
                               class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                               accept=".jpg,.jpeg,.png">
                        <div id="logo_src" class="show"></div>
                        <input type="hidden" id="logo_path" class="path" name="urllogo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-control">
                            <option {{ (int) old('status', $gateway->status) === 1 ? 'selected' : '' }} value="1">فعال</option>
                            <option {{ (int) old('status', $gateway->status) === 0 ? 'selected' : '' }} value="0">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success text-center">ویرایش</button>
                </div>
            </form>
        </div>
    </div>
@endsection
