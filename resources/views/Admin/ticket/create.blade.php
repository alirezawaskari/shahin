@extends('Admin.master')
@section('title')ایجاد مکاتبه جدید@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('ticket.store')}}"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                @include('Admin.layouts.errors')

                <div class="col-12 col-md-6 mb-2">
                    <label>عنوان مکاتبه</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

{{--                <div class="col-12 col-md-6 mb-2">--}}
{{--                    <label>واحد مربوطه</label>--}}
{{--                    <select class="form-control" name="card_id">--}}
{{--                        <option value="all">انتخاب کنید</option>--}}
{{--                        @foreach($cards as $card)--}}
{{--                        <option value="{{ $card->id }}">{{ $card->title }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

                <div class="col-12 col-md-6 mb-2">
                    <label>نقش مربوطه</label>
                    <select class="form-control select2" name="role" id="role" required>
                        <option value="all">انتخاب کنید</option>
                        @foreach(\App\Models\Role::all() as $role)
                        <option value="{{ $role->id }}" >{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label>کاربر مربوطه</label>
                    <select class="form-control" name="receiver_id" id="users" required>
                        <option value="all">انتخاب کنید</option>
                    </select>
                </div>


              {{--  @permission('representation-ticket')
                <div class="col-12 col-md-4 mb-2">
                    <label>عامل مربوطه</label>
                    <select class="form-control select2" name="representation_id[]" multiple>
                        @foreach($representations as $representation)
                        <option value="{{ $representation->id }}">{{ $representation->fa_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endpermission

                @permission('presentore-ticket')
                <div class="col-12 col-md-4 mb-2">
                    <label>بازاریاب دیجیتال</label>
                    <select class="form-control select2" name="presentore_id[]" multiple>
                        @foreach($presentores as $presentore)
                        <option value="{{ $presentore->id }}">{{ $presentore->name.' '.$presentore->family }}</option>
                        @endforeach
                    </select>
                </div>
                @endpermission

                @permission('center-ticket')
                <div class="col-12 col-md-4 mb-2">
                    <label>مرکز مربوطه</label>
                    <select class="form-control select2" name="center_id[]" multiple>
                        @foreach($centers as $center)
                        <option value="{{ $center->id }}">{{ $center->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endpermission--}}

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
<script>
    $(document).ready(function () {

        $('#role').on('change', function () {
            var id = $(this).val();
            $.ajax({
                url: '/dashboard/role/get-user-by-role',
                type: 'get',
                data: {
                    "id": id,
                },
                success: function (data) {
                    $('#users')
                        .empty()
                        .append('<option value="all">انتخاب دریافت کننده</option>')
                    ;
                    $.each(data.data.users, function (i, d) {
                        $('#users').append('<option value="' + d.id + '">' + d.name + " "+  d.family + '</option>');
                    });
                }
            });
        });


    })
</script>
@endsection
