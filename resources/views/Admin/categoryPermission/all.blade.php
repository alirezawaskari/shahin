@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست دسته بندی دسترسی ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست دسته بندی های دسترسی</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('category-permission.create') }}">دسته بندی جدید</a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>تاریخ ثبت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category_permissions as  $item)
                <tr>
                    <td>
                        {{ $item->name }}
                    </td>

                    <td>
                        {{ verta($item->created_at)->format('Y/m/d H:m:s') }}
                    </td>
                    <td>
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('category-permission.edit' , [ $item->id]) }}" class="dropdown-item pointer"
                                    target="_blank">ویرایش
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
