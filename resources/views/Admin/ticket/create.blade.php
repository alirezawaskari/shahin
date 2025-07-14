@extends('Admin.master')
@section('title')
    ایجاد مکاتبه جدید
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('ticket.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row align-items-center">
                    @include('Admin.layouts.errors')
                    <div class="col-12 mb-2">
                        <label>عنوان مکاتبه</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-12">

                        <div class="row" id="receiver">
                            <div class="col-12 col-md-6 mb-2">
                                <label>نقش مربوطه</label>
                                <select class="form-control select2" name="role[]" id="role_select1" required>
                                    <option value="all">انتخاب کنید</option>
                                    @foreach(\App\Models\Role::all() as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <label>کاربر مربوطه</label>
                                <select class="form-control" name="receivers[]" id="receiver_select1" required>
                                    <option value="all">نقش را انتخاب کنید</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="button" class="btn btn-primary rounded float-right" id="addReceiver">
                            افزودن +
                        </button>
                    </div>
                    <div class="col-12 mb-3">
                        <label>متن درخواست</label>
                        <textarea type="text" class="form-control" name="content"></textarea>
                    </div>

                    <div class="col-12">
                        <div id="addnew" class='file file--upload'>
                            <label for='file'>
                                <i class="fa fa-cloud-upload"></i>بارگذاری فایل
                            </label>
                            <input name="file[]" type="file" id="file"
                                   class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white">
                        </div>
                        <div id="file_newlink" class="col-12 row">
                            <div>
                            </div>
                        </div>
                        <div id="file_newlinktpl">
                            <div id="file_src" class="show"></div>
                            <input type="hidden" id="file" class="path" name="urlfile[]">
                        </div>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/tickets.js?v=1.1') }}"></script>
@endsection
