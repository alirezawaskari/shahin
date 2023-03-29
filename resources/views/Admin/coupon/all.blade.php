@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست تخفیف ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست تخفیف ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('coupon.create') }}">تخفیف جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>کد تخفیف</th>
                        <th>درصد تخفیف</th>
                        <th>ظرفیت</th>
                        <th>ظرفیت مصرف شده</th>
                        <th>تاریخ آغاز</th>
                        <th>تاریخ پایان</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($coupons as $key => $coupon)
                        <tr>
                            <td>
                                {{ $coupon->title }}
                            </td>

                            <td>
                                {{ $coupon->code }}
                            </td>

                            <td>
                                {{ $coupon->discount }}%
                            </td>

                            <td>
                                {{ $coupon->capacity }}
                            </td>

                            <td>
                                {{ $coupon->reserved }}
                            </td>

                            <td>
                                {{ verta($coupon->start_at)->format('%d %B ، %Y') }}
                            </td>

                            <td>
                                {{ verta($coupon->end_at)->format('%d %B ، %Y') }}
                            </td>


                            <td>
                                @if($coupon->status == 0)
                                    <span class="label label-danger">غبر فعال</span>
                                @else
                                    <span class="label label-success">فعال</span>
                                @endif
                            </td>

                            <td class="text-right">
                                <a href="{{ route('coupon.edit' , [$coupon->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>
                            <td class="text-left">
                                <form class="delete_form" method="post"
                                      action="{{ route('coupon.destroy',[ $coupon->id]) }}">
                                    @method('DELETE')
                                    @csrf

                                    <input type="button" class="btn btn-danger delete_submit"
                                           id="{{ $coupon->id }}"
                                           value="حذف"/>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
@endsection
