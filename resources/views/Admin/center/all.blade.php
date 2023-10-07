@extends('Admin.master')
@section('title')فهرست مراکز@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست مراکز</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست مراکز</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('center.create') }}">مرکز جدید</a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body">
        <table class="table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>خدمت</th>نمایندگی ایجادکننده
                    <th>نمایندگی ایجادکننده</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($centers as $key => $item)
                <tr>
                    <td>
                        {{ $item->title }}
                    </td>
                    <td>
{{--                        {{ $item->centerable ? $item->centerable->title : ''}}--}}
                        {{implode(' | ',$item->services->pluck('title')->toArray())}}
                    </td>
                    <td>
                        {{ $item->creatorRpresentation ? $item->creatorRpresentation->fa_name : 'نمایندگی کل'                    }}
                    </td>
                    <td>
                        {{ verta($item->created_at)->format('Y/m/d H:m:s') }}
                    </td>
                    <td>
                        {!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!}
                    </td>
                    <td>
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('center.serviceTariff' , [$item->id]) }}"
                                    class="dropdown-item pointer" target="_blank">فهرست تعرفه ها
                                </a>
                                @permission('edit-center')
                                @if(!auth()->user()->admin_representation_id)
                                <a href="{{ route('center.edit' , [ $item->id]) }}" class="dropdown-item pointer"
                                    target="_blank">ویرایش
                                </a>
                                @endif
                                @endpermission
                                <form class="delete_form" method="post"
                                    action="{{ route('center.destroy',[$item->id]) }}">
                                    @method('DELETE')
                                    @csrf
                                    <input type="button" class="dropdown-item pointer delete_submit"
                                        id="{{ $item->id }}" value="حذف">
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
@stop

@section('js')

<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/center.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
