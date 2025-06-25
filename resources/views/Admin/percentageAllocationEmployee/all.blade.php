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
</div>

<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form" action="/dashboard/percentageAllocationEmployee/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <select class="custom-select select2" name="user">
                        <option value="all">همکاران</option>
                        @foreach($employees as $employee)
                        <option value="{{$employee->user->id}}">{{ $employee->user->name }} - {{ $employee->user->family
                            }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="type" class="custom-select select2" id="type">
                        <option value="all">انتخاب نوع تسهیم</option>
                        @foreach(\App\Models\PercentageAllocationEmployee::TYPE as $key => $type)
                            <option value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                        <label class="custom-control-label" for="excel">خروجی اکسل</label>
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

