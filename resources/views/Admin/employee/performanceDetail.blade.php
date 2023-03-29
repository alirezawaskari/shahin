@extends('Admin.master')
@section('title')کلیات گزارش مالی {{ $user->family }}@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>کلیات گزارش مالی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">کلیات گزارش مالی</li>
                </ol>
            </nav>
        </div>
        <div class="heading-elements">
            <div class="heading-btn-group ltr mb-3">
                <a href="{{ route('employee.wallet', [ $user->id , 'key' => 'all']) }}"
                    class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>جزئیات گزارش مالی</span>
                </a>
                <a href="{{ route('employee.financial.report', [ $user->employee->id]) }}"
                    class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>عملکرد ماهانه</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                        <tr>
                            <th>نام و نام خانوادگی</th>
                            <th>موبایل</th>
                            <th>مبلغ (ریال)</th>
                            <th>نوع پرداخت</th>
                            <th>تعداد جلسات</th>
                            <th>تعداد جلسات انجام شده</th>
                            <th>اقدام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user_performances as $key => $user_performance)
                        <tr>
                            <td>
                                {{ isset($user_performance->factorItem) ? $user_performance->factorItem->factor->user->name. ' ' . $user_performance->factorItem->factor->user->family : 'ندارد' }}
                            </td>
                            <td>
                                {{ isset($user_performance->factorItem) ? $user_performance->factorItem->factor->user->phone : 'ندارد' }}
                            </td>
                            <td>
                                {{ isset($user_performance->factorItem) ? number_format($user_performance->factorItem->total_price) : 'ندارد' }}
                            </td>
                            <td>
                                @if (isset($user_performance->factorItem) && $user_performance->factorItem->type)
                                    اقساط
                                @else
                                    نقد
                                @endif
                            </td>
                            <td>
                                {{ isset($user_performance->factorItem) ?  $user_performance->factorItem->serviceTariffs->sum('pivot.session_number') : '0' }}
                            </td>
                            <td>
                                {{ isset($user_performance->factorItem) ? $user_performance->factorItem->sessions->where('status' , true)->count() : '0' }}
                            </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a target="_blank" href="/dashboard/factorItem/{{ isset($user_performance->factorItem) ? $user_performance->factorItem->resnumber : 'ندارد' }}/edit"><button type="button" class="dropdown-item pointer">مشاهده</button></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @include('Admin.layouts.pagination')
			<p class="mt-3">جمع کل : {{ number_format($user_performances->sum('factorItem.total_price')) }} ریال</p>
        </div>
    </div>
@stop
@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
@endsection
