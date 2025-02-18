@extends('Admin.master')

@section('css')
@stop
@section('content')

<div class="page-header">
    <div>
        <h3>لیست زیر سازمانها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">لیست زیر سازمانهای
                    <span class="green"> {{$parent->fa_name}}</span>
                </li>
            </ol>
        </nav>
    </div>
    <div><a class="btn btn-primary" href="{{route('organization.index')}}">فهرست سازمان ها </a></div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
            <thead>
                <tr>
                    <th scope="col">عنوان</th>
                    <th scope="col">شماره بایگانی</th>
                    <th scope="col">نوع همکاری</th>
                     <th scope="col">تاریخ ثبت</th>
                    <th scope="col">وضعیت بایگانی</th>
                    <th scope="col">اقدام</th>
                </tr>
            </thead>
            <tbody>
                @foreach($children as $organization)
                <tr>
                    <td>
                        {{$organization->fa_name}}
                    </td>
                    <td>
                        {{$organization->archive_number}}
                    </td>
                    <td>
                        {{ $organization::TYPE[$organization->type]}}
                    </td>
                    <td>
                        {{verta($organization->created_at)->format('Y/m/d')}}
                    </td>
                    <td>
                        {{ $organization::status($organization->archive_status)}}
                    </td>
                    <td>

                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i></a>
                            <div class="dropdown-menu">
                                <a href="../../dashboard/organization/{{$organization->slug}}/edit" target="_blank"><button type="button" class="dropdown-item pointer">مشاهده  </button></a>
                               @permission('delete_organ')
                                <form method="post" action="{{route('organization.destroy',[$organization->slug])}}">
                                    {{ method_field('DELETE') }}  {{csrf_field()}}
                                    <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                    </button>
                                </form>
                                @endpermission
                               </div></div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@stop
@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
<script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>

@stop
