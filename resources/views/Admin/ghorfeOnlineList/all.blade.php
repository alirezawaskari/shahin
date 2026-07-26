    @extends('Admin.master')

    @section('content')

        <div class="page-header">
            <div>
                <h3>فهرست مغازه های دیجیتال</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                        <li class="breadcrumb-item active" aria-current="page">فهرست مغازه های دیجیتال</li>
                    </ol>
                </nav>
            </div>
            <div>
                @permission('ghorfe_online_list_create')
                <a class="btn btn-primary" href="{{ route('ghorfeOnlineList.create') }}">ایجاد مغازه جدید</a>
                @endpermission
            </div>
        </div>
        <div class="card position-relative">
            <div class="card-body table-responsive">
                <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                        <tr>
                            <th>لوگو</th>
                            <th>عنوان</th>
                            <th>نام مالک</th>
                            <th>شماره تماس</th>
                            <th>تاریخ شروع اجاره نامه</th>
                            <th>تاریخ پایان اجاره نامه</th>
                            <th>نام دامنه</th>
                            <th>وضعیت</th>
                            <th>اقدام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ghorfeOnlineLists as $key => $ghorfeOnline)
                        <tr class="active" id="item_{{$ghorfeOnline->id}}" style="cursor: move;">
                            <td>@if($ghorfeOnline->logo)<img src="{{ $ghorfeOnline->logo }}" width="100px">@endif</td>
                            <td>{{ $ghorfeOnline->title }}</td>
                            <td>{{ $ghorfeOnline->user ? $ghorfeOnline->user->name.' '.$ghorfeOnline->user->family : 'ثبت نشده' }}</td>
                            <td>{{ $ghorfeOnline->call }}</td>
                            <td>{{ verta($ghorfeOnline->contract_start_date)->format('Y/m/d') }}</td>
                            <td>{{ verta($ghorfeOnline->contract_expiration_date)->format('Y/m/d') }}</td>
                            <td><a href="//{{ $ghorfeOnline->domain_active }}" target="_blank">{{ $ghorfeOnline->domain_active }}</a></td>
                            <td>{{ $ghorfeOnline->status ? 'فعال' : 'غیرفعال'}}</td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown dropup">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('ghorfeOnlineList.edit' , [$ghorfeOnline->slug]) }}"><button
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
