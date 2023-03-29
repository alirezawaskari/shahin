@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>  نظرات کالا | خدمت</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست نظرات کالا | خدمت </li>
                </ol>
            </nav>
        </div>
    </div>

    @if($filter == true)
     <div class="card">
        <div class="card-body">
            <form method="get" action="{{route('comment.index')}}">
                <div class="col-12 col-md-3 mb-3 disInline">
                        <select title="type" name="type" class="custom-select">
                            <option value="all">نوع </option>
                                <option value="1">کالا</option>
                                <option value="2">خدمت</option>
                        </select>
                    </div>
                <div class="col-12 col-md-2 mb-3 disInline">
                    <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    <div class="card">
        <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>کاربر</th><th>نظر </th><th>کالا / خدمت</th><th>امتیاز</th><th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($comments as $item)
                        <tr>
                            <td>{{\App\Helpers\Helper::user($item->user_id,'name')}} {{\App\Helpers\Helper::user($item->user_id,'family')}} </td>
                            <td>{{$item->message}} </td>
                            <td>
                                @if(isset($item->commentable->title) ){{$item->commentable->title}}
                                @elseif(isset($item->commentable->name)){{$item->commentable->name}}
                                @endif
                                 </td>
                            <td>{{$item->score}} </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true"  aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{route('comment.show',$item->id)}}" title="فعال / غیرفعال" >
                                            <button type="button" class="dropdown-item pointer"> @if($item->active == 0) فعال  @else  غیرفعال @endif</button>
                                        </a>
                                    </div>
                                </div>

                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>

               @if($comments != []) {{$comments->links()}} @endif

        </div>
    </div>

@stop
@section('js')
@stop