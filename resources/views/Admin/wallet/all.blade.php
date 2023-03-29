@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست پرداخت های کیف پول</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست پرداخت های کیف پول</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th scope="col">نام</th>
                        <th scope="col">شماره موبایل</th>
                        <th scope="col">کد ملی</th>
                        <th scope="col">مبلغ</th>
                        <th scope="col">شناسه</th>
                        <th scope="col">تاریخ</th>
                        <th scope="col">وضعیت</th>
                        <th scope="col">پیگیری بانک</th>
                        <th scope="col">اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transactions as $key => $walletTransaction)
                        <tr>
                            <td>{{ $walletTransaction->user->name }} {{ $walletTransaction->user->family }}</td>
                            <td>{{ $walletTransaction->user->phone }}</td>
                            <td>{{ $walletTransaction->user->melicode == null ? 'ثبت نشده' : $walletTransaction->user->melicode }}</td>
                            <td>{{ number_format($walletTransaction->price) }}ریال
                            </td>
                            <td>{{ $walletTransaction->resnumber !='' ? $walletTransaction->resnumber : 'ندارد'}}</td>
                            <td>
                                {{ verta($walletTransaction->created_at)->format('%d %B ، %Y') }}
                            </td>
                            <td>{{ $walletTransaction->status == 1 ? 'موفق' : 'معلق' }}</td>

                            <td>
                                {{ $walletTransaction->refnumber ? $walletTransaction->refnumber : 'ندارد' }}
                            </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <form class="delete_form" method="post" action="{{ route('WalletTransaction.destroy',[ $walletTransaction->id]) }}">
                                        @method('DELETE')
                                        @csrf
                                        <input type="button" class="dropdown-item pointer delete_submit" id="{{ $walletTransaction->id }}" value="حذف"/>
                                    </form>
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
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
    <script src="/assets/dashboard/js/layouts.js"></script>
@endsection
