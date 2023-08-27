@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست تک صفحه ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست تک صفحه ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('singleWebPage.create') }}">صفحه جدید</a>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>عنوان  سایت</th>
                        <th>ثبت کننده</th>
                        <th>تاریخ ثبت</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($single_web_pages as $key => $single_web_page)
                    <tr class="active" id="item_{{$single_web_page->id}}" style="cursor: move;">
                        <td>{{ $single_web_page->fa_name }}</td>
                        <td>{{ $single_web_page->agent->name.' '.$single_web_page->agent->family }}</td>
                        <td>{{ verta($single_web_page->created_at)->format('Y/m/d') }}</td>
                        <td>
                            @if ($single_web_page->status)
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
                                    <a href="{{ route('singleWebPage.edit' , [$single_web_page->slug]) }}"><button
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
