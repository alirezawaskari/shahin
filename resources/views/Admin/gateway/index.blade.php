@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>{{ $title }}</h3>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-end">
            <div class="row justify-content-between align-items-center px-3">
                <form method="GET" action="{{ route('gateway.index') }}" class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                               placeholder="جستجو بر اساس نام درگاه">
                    </div>
                    <div class="col-md-6 mb-2">
                        <button type="submit" class="btn btn-primary">جستجو</button>
                        <a href="{{ route('gateway.index') }}" class="btn btn-danger">حذف فیلتر</a>
                    </div>
                </form>
                <a class="btn btn-success" href="{{route('gateway.create')}}">ایجاد درگاه</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام فارسی درگاه</th>
                    <th>نام درگاه</th>
                    <th class="text-center" width="120px">لوگو درگاه</th>
                    <th>وضعیت</th>
                    <th>ویرایش</th>
                    <th>حذف</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($gateways as $gateway)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $gateway->name }}</td>
                        <td>{{ $gateway->fa_name }}</td>
                        <td><img src="{{ $gateway->logo }}" height="80px" width="120px" alt=""></td>
                        <td>{{ $gateway->status_label }}</td>
                        <td>
                            <a class="btn btn-primary btn-floating" href="{{route('gateway.edit',['gateway'=> $gateway])}}">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('gateway.destroy', $gateway->id) }}"
                                  class="d-inline" onsubmit="return confirm('آیا مطمئنی؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-floating"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">درگاهی یافت نشد</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
