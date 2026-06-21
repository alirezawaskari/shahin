@extends('Admin.master')
@section('title')ویرایش منو @endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد طرح جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a
                        href="{{ route('menuSetting.index') }}">  زیر منو های مربوط به {{$menuSetting->name}}  </a></li>
            </ol>
        </nav>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
            ویرایش منو و زیرمنو های آن
        </div>
        <form class="form-horizontal" role="form" method="post"
            action="{{route('menuSetting.update' , [$menuSetting->id])}}"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('Admin.layouts.errors')
            <div class="row">

                <div class="col-12 col-md-6 mb-2">
                    <label>عنوان منو </label>
                    <input type="text" class="form-control" name="name" value="{{ $menuSetting->name }}"
                        required>
                </div>

                <div class="col-12 col-md-6 mb-2">
                    <label>وضعیت</label>
                    <select class="form-control" name="is_active">
                        <option value="0" {{ $menuSetting->is_active == 0 ? 'selected' : '' }}>غیرفعال</option>
                        <option value="1" {{ $menuSetting->is_active == 1 ? 'selected' : '' }}>فعال</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </form>

        <div class="card position-relative">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped" id="datatable-json">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>عنوان نمایشی</th>
                        <th>توضیحات</th>
                        <th>وضعیت </th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($menuSetting->permission as $key => $lalecard)
                        <tr>
                            <td><input type="text" value="{{ $lalecard->name }}" class="form-control" readonly></td>
                            <td><input type="text" value="{{ $lalecard->display_name }}" class="form-control"></td>
                            <td><input type="text" value="{{ $lalecard->description }}" class="form-control"></td>
                            <td><input type="text" value="{{ $lalecard->link }}" class="form-control"></td>
                            <td>
                                <select class="form-control">
                                    <option value="1" {{ $lalecard->is_active ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ !$lalecard->is_active ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-success btn-save" data-id="{{ $lalecard->id }}">
                                    ذخیره
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>
@stop
@section('js')
    <script>
        $(document).ready(function () {

            $('.btn-save').click(function () {

                let row = $(this).closest('tr');
                console.log(row.find('select').val());
                $.ajax({
                    url: "{{ route('permissions.updateSubMenu') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: $(this).data('id'),
                        display_name: row.find('input').eq(1).val(),
                        description: row.find('input').eq(2).val(),
                        link: row.find('input').eq(3).val(),
                        is_active: row.find('select').val(),
                    },
                    success: function (res) {
                        alert('ذخیره شد ✅');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });

            });

        });
    </script>
@endsection
