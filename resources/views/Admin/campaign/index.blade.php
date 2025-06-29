@extends('Admin.master')
@section('title')
    {{ $title }}
@endsection
@section('content')
    <div class="page-header">
        <div>
            <h3>{{ $title }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                اقدامات
            </button>
            <div class="dropdown-menu">
                <a href="{{ route('campaign.create') }}" class="dropdown-item fast-service">
                    ایجاد
                </a>
            </div>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                   id="data-table-factors">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>موضوع</th>
                    <th>ظرفیت</th>
                    <th>تصویر</th>
                    <th>تصویر</th>
                    <th>تاریخ شروع</th>
                    <th>تاریخ پایان</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($campaigns as $campaign)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$campaign->name}}</td>
                        <td>{{$campaign->subject}}</td>
                        <td>{{$campaign->capacity}}</td>
                        <td>
                            <a href="{{ asset($campaign->image) }}" target="_blank">
                                <img src="{{ asset($campaign->image) }}" width="70px" alt="{{$campaign->subject}}">
                            </a>
                        </td>
                        <td>
                            <a href="{{ asset($campaign->image_header) }}" target="_blank">
                                <img src="{{ asset($campaign->image_header) }}" width="70px" alt="{{$campaign->subject}}">
                            </a>
                        </td>
                        <td>{{verta($campaign->start_date)->format('Y/m/d')}}</td>
                        <td>{{verta($campaign->end_date)->format('Y/m/d')}}</td>
                        <td>
                            <div class="dropdown" id="taskDetailModal">
                                <a
                                    href="#"
                                    data-toggle="dropdown"
                                    class="btn btn-dark btn-floating btn-icon btn-sm"
                                >
                                    <i class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('campaign.edit',['campaign' => $campaign]) }}">
                                        <span class="dropdown-item pointer task-status">
                                                ویرایش
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/dashboard/js/layouts.js?v=1.0')}}"></script>
    <script src="{{asset('assets/dashboard/js/layout/productServiceFilterInputs.js')}}"></script>
@endsection
