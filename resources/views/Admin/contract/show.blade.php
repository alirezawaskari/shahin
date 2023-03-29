@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>تاریخچه قرارداد</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> تاریخچه قرارداد  </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>کاربر</th>
                        <th>تاریخ</th>
                        <th>فایل</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contract_histories as $item)
                        <tr>
                            <td>{{\App\Helpers\Helper::user($item->user_id,'name')}}   {{\App\Helpers\Helper::user($item->user_id,'family')}} </td>
                            <td>{{ verta($item->created_at)->format('Y-m-d H:i:s')}}</td>
                            <td><a href="{{$item->file}}">مشاهده</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$contract_histories->links()}}
            </div>
        </div>
    </div>

@stop
@section('js')
@stop