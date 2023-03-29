@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="http://cdn.datatables.net/1.10.18/css/jquery.dataTables.min.css">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3> اطلاعات دعوت از دوستان</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> لیست اطلاعات دعوت از دوستان</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('inviteCategory.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>دسته بندی / خدمت / محصول</th>
                    <th>درصد</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invite_categories as $item)
                    <tr>
                        <td>
                            <img class="border-radius5" src="{{$item->invitecategoryable->image}}" width="60px" />
                            @if(isset($item->invitecategoryable->title))
                                {{$item->invitecategoryable->title}}
                            @elseif(isset($item->invitecategoryable->name))
                                {{$item->invitecategoryable->name}}
                            @endif
                        </td>
                        <td>{{$item->percent}} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('inviteCategory.edit',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$invite_categories->links()}}

        </div>
    </div>


@stop
@section('js')

@stop