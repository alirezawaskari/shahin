@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست لاله کارت ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست لاله کارت ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('lalecard.create') }}">جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>شهر</th>
                        <th>زمان ارسال پست</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($lalecards as $key => $lalecard)
                        <tr>
                            <td>{{ $lalecard->name }}</td>
                            <td>{{ $lalecard->family }}</td>
                            <td>{{ $lalecard->city }}</td>
                            <td>{{ verta($lalecard->post_date)->format('%d %B ، %Y - H:i:s') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('Admin.layouts.pagination')
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
@endsection
