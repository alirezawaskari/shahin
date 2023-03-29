@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست کاربران پاک شده</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست کاربران پاک شده</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">نام</th>
                        <th scope="col">شماره موبایل</th>
                        <th scope="col">کد ملی</th>
                        <th scope="col">اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    @foreach($users as $key => $user)
                        <tr>
                            <td>{{ $user->name . ' ' .$user->family }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->melicode == '' ? 'ثبت نشده' : $user->melicode }}</td>

                            <td>
                                <a href="{{ route('user.restore' , [$user->id]) }}"
                                   class="btn btn-primary">بازگردانی
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $users->render() }}
            </div>
        </div>
    </div>
@stop
