@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست نمایندگی ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست نمایندگی ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('representation.create') }}">نمایندگی جدید</a>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>آدرس سایت</th>
                        <th>عنوان  نمایندگی</th>
                        <th>نام نماینده</th>
                        <th>موبایل</th>
                        <th>تاریخ ثبت</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($representations as $key => $representation)
                    <tr class="active" id="item_{{$representation->id}}" style="cursor: move;">
                        <td><a href="{{ URL::to('/').'/'.$representation->slug }}" target="_blank">{{ URL::to('/').'/'.$representation->slug }}</a></td>
                        <td>{{ $representation->fa_name }}</td>
                        <td>{{ $representation->agency_name }}</td>
                        <td>{{ $representation->call }}</td>
                        <td>{{ verta($representation->created_at)->format('Y:m:d') }}</td>
                        <td>
                            @if ($representation->status)
                            فعال
                            @else
                            غیرفعال
                            @endif
                        </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown dropup">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('representation.edit' , [$representation->slug]) }}"><button
                                            type="button" class="dropdown-item pointer"> ویرایش </button></a>
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
