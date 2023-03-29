@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست دعوت از کاربران</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">دعوت از کاربران</li>
                </ol>
            </nav>
        </div>

    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>موبایل</th>
                        <th>نام و نام خانوادگی</th>
                        <th>دسته بندی / خدمت</th>
                        <th>دعوت کننده</th>
                        <th>وضعیت</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($user_invites as $user_invite)
                        <tr>
                            <td>{{$user_invite->mobile}} </td>
                            <td>{{$user_invite->name}}  {{$user_invite->family}} </td>
                            <td>
                                @if($user_invite->invite_category_title)
                                    {{$user_invite->invite_category_title}}
                                @endif
                            </td>
                            <td>{{\App\Helpers\Helper::user($user_invite->user_id,'name')}}  {{\App\Helpers\Helper::user($user_invite->user_id,'family')}} </td>
                            <td>
                                @if($user_invite->status != null)
                                   {{$user_invite::STATUS[$user_invite->status]}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            {{$user_invites->links()}}
        </div>
    </div>


@stop
@section('js')
@stop