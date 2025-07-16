@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                class="modal-content p-5"
                enctype="multipart/form-data"
                method="POST"
                action="{{ route('gateway.store') }}"
            >
                @csrf
                <h3 class="text-center">ایجاد درگاه</h3>
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">نام درگاه</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">نام فارسی درگاه</label>
                        <input type="text" name="fa_name" class="form-control" required>
                    </div>
                    <div class="col-12  card-body">
                        <div id="logo_src" class="show">
                            <img class="card-img-top" width="120" height="80">
                        </div>
                        <label>لوگو</label>
                        <br>
                        <label for="logo" id="up-label" class="btn badge-secondary">
                            <i class="fa fa-upload ml-2"></i>
                            تصویر
                        </label>
                        <input name="logo" type="file" id="logo"
                               class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                               accept=".jpg,.jpeg,.png">
                        <input type="hidden" id="logo" class="path" name="urllogo">
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-control">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success text-center">ثبت</button>
                </div>
            </form>
        </div>
    </div>

@endsection
