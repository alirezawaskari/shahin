@extends('Admin.master')
@section('title')فهرست نمونه‌کارها@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست نمونه‌کارها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست نمونه‌کارها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('workSample.create') }}">نمونه‌کار جدید</a>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                <tr>
                    <th>تصویر</th>
                    <th>عنوان</th>
                    <th>شعبه</th>
                    <th>مربوط به</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($workSamples as $item)
                    <tr>
                        <td>
                            @if($item->image)
                                <img src="{{ asset($item->image) }}" alt="نمونه‌کار" class="img-thumbnail" style="width: 80px; height: auto;">
                            @else
                                <span class="text-muted">ندارد</span>
                            @endif
                        </td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->center->title ?? 'بدون شعبه' }}</td>
                        <td>
                            {{ optional($item->worksampleable)->title ?? '---' }}
                        </td>
                        <td>{{ verta($item->created_at)->format('Y/m/d H:i:s') }}</td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->status) !!}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('workSample.edit', $item->id) }}" class="dropdown-item">ویرایش</a>
                                    <form class="delete_form" method="POST" action="{{ route('workSample.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item delete_submit">حذف</button>
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

@endsection

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
@endsection
