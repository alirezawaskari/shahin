@extends('Admin.master')
@section('title')فهرست طرح های مالی@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست طرح های مالی</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست طرح های مالی</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('financialPlansType.create') }}">طرح جدید</a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان طرح</th>
                    <th>هزینه ثبت نام</th>
                    <th>نوع پرداخت</th>
                    <th>درصد پیش پرداخت</th>
                    <th>درصد تخفیف</th>
                    <th>ظرفیت</th>
                    <th>ظرفیت مصرف شده</th>
                    <th>تاریخ آغاز</th>
                    <th>تاریخ پایان</th>
                    <th>وضعیت</th>
                    <th>نمایش در سایت</th>
                    <th>ثبت کننده</th>
                    <th>تاریخ ثبت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financial_plans_types as $key => $financial_plans_type)
                <tr>
                    <td>{{ $financial_plans_type->title }}</td>
                    <td>{{ number_format($financial_plans_type->price) }}</td>
                    <td>{{ $financial_plans_type::TYPE[$financial_plans_type->payment_type] }}</td>
                    <td>{{ $financial_plans_type->prepayment_percent.' %' }}</td>
                    <td>{{ $financial_plans_type->discount_percent.' %' }}</td>
                    <td>{{ $financial_plans_type->capacity }}</td>
                    <td>{{ $financial_plans_type->reserved }}</td>
                    <td>{{ verta($financial_plans_type->start_date)->format('Y/m/d') }}</td>
                    <td>{{ verta($financial_plans_type->end_date)->format('Y/m/d') }}</td>
                    <td>{!! \App\Helpers\Helper::iconIsItOrNot($financial_plans_type->status) !!} </td>
                    <td>{!! \App\Helpers\Helper::iconIsItOrNot($financial_plans_type->is_show) !!} </td>
                    <td>{{ $financial_plans_type->applicant ? $financial_plans_type->applicant->family : 'ثبت نشده' }}</td>
                    <td>{{ verta($financial_plans_type->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('financialPlansType.edit' , [$financial_plans_type->id]) }}" class="dropdown-item pointer"
                                    target="_blank">ویرایش
                                </a>
                            </div>
                        </div>
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
