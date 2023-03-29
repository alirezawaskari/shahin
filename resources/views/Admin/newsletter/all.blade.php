@extends('Admin.master')

@section('content')

<div class="page-header">
    <div class="heading-elements">
        <h3>فهرست ایمیل ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست ایمیل ها</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('newsletter.exel') }}" class="btn btn-primary">دریافت اکسل</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>ایمیل</th>
                        <th>تاریخ ثبت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($newsletters as $key => $newsletter)
                    <tr>
                        <td>
                            {{ $newsletter->email }}
                        </td>
                        <td>
                            {{ verta($newsletter->created_at)->format('%d %B ، %Y') }}
                        </td>
                        <td>
                            <form class="delete_form" method="post" action="{{ route('newsletter.destroy',[$newsletter->id]) }}">
                                @method('DELETE')
                                @csrf

                                <input type="button" class="btn btn-danger delete_submit" id="{{ $newsletter->id }}" value="حذف" />
                            </form>
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
