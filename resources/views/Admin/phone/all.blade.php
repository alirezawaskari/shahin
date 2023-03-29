@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست شماره تماس ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست شماره تماس ها</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('phone.exel') }}" class="btn btn-primary">
            دریافت اکسل
        </a>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
            <thead>
                <tr>
                    <th>#</th>
                    <th>موبایل</th>
                    <th>تاریخ ثبت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phones as $key => $phone)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        {{ $phone->phone }}
                    </td>
                    <td>
                        {{ verta($phone->created_at)->format('%d %B ، %Y') }}
                    </td>
                    <td>
                        <form class="delete_form" method="post"
                            action="{{ route('phone.destroy',['id'=>$phone->id]) }}">
                            @method('DELETE')
                            @csrf
                            <input type="button" class="btn btn-danger delete_submit" id="{{ $phone->id }}"
                                value="حذف" />
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $phones->render() }}
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
