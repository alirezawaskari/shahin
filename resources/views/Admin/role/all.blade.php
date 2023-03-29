@extends('Admin.master')
@section('title')فهرست نقش ها@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست نقش ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست نقش ها</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('role.create') }}">نقش جدید</a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان نقش</th>
                    <th>کلمه کلیدی نقش</th>
                    <th>تاریخ ثبت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $key => $role)
                <tr class="active" id="item_{{$role->id}}" style="cursor: move;">
                    <td>{{ $role->display_name }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ verta($role->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('role.edit' , [$role->id]) }}"><button type="button"
                                class="btn btn-primary border-0">ویرایش دسترسی ها</button></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @include('Admin.layouts.pagination')
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
