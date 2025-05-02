@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست درخواست های پاک شده</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست درخواست های پاک شده</li>
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
                        <th scope="col">کالسنتر</th>
                        <th scope="col">اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    @foreach($callcenters as $key => $callcenter)
                        <tr>
                            <td>{{ $callcenter->register ? ($callcenter->register['name'] . ' ' . $callcenter->register['family']) : 'کاربر حذف شده' }}</td>
                            <td>{{  $callcenter->register ? $callcenter->register['phone'] : 'کاربر حذف شده' }}</td>
                            <td>{{ $callcenter->callcenter ? $callcenter->callcenter['family'] : 'ندارد' }}</td>
                            <td>
                                <a href="{{ route('callcenter.restore' , ['id'=>$callcenter->id]) }}"
                                   class="btn btn-primary">بازگردانی
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $callcenters->render() }}
            </div>
        </div>
    </div>
@stop
