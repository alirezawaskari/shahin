@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h3>{{ $title }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card card-bordered card-stretch">
        <div class="row justify-content-between col-12 p-3">
            <form method="GET" action="{{ route('percentageAllocationEmployee.index') }}" class="px-2 m-0">
                <div class="row">

                    <div class="col-md-2">
                        <input type="text" name="name" class="form-control" placeholder="نام سهامدار"
                               value="{{ request('name') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-control">
                            <option value="">نوع تسهیم</option>
                            @foreach(\App\Models\PercentageAllocationEmployee::TYPE as $key => $value)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="startDate" class="form-control" placeholder="تاریخ شروع"
                               value="{{ request('startDate') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="endDate" class="form-control" placeholder="تاریخ پایان"
                               value="{{ request('endDate') }}">
                    </div>
                    <button type="submit" class="btn btn-success">جستجو</button>
                </div>
            </form>
            <a href="{{ route('percentageAllocationEmployee.create') }}" class="btn btn-primary">افزودن سهامدار</a>
        </div>
    </div>
    <div class="nk-content">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    {{-- جدول اصلی --}}
                    <div class="card card-bordered card-stretch">
                        <div class="card-inner p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>نام</th>
                                        <th>نام خانوادگی</th>
                                        <th>موبایل</th>
                                        <th>نوع تسهیم</th>
                                        <th>خدمت | مرکز</th>
                                        <th>درصد</th>
                                        <th>ارسال کننده فاکتور</th>
                                        <th>تاریخ شروع</th>
                                        <th>تاریخ پایان</th>
                                        <th>ثبت کننده</th>
                                        <th>تاریخ ثبت</th>
                                        <th>زیرمجموعه‌ها</th>
                                        <th>حذف</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($employees as $index => $employee)
                                        @include('Admin.partials.employee.row', [
                                            'employee' => $employee,
                                            'percentageAllocations' => $percentageAllocations,
                                            'level' => 0,
                                            'parentId' => null
                                        ])
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('assets/dashboard/js/percentageAllocationEmployee.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

@stop

