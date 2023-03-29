@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست پزشکان</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">پزشکان</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>نام</th>
                    <th>فامیلی</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $item)
                    <tr>
                        <td>{{$item->name}} </td>
                        <td>{{$item->family}} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" 
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('fullCalendarDoctor',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">تقویم کاری</button>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$users->links()}}
        </div>
    </div>


@stop
@section('js')
@stop
